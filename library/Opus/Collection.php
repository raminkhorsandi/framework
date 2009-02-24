<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Framework
 * @package     Opus
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Tobias Tappe <tobias.tappe@uni-bielefeld.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Bridges Opus_Collection_Information to Opus_Model_Abstract.
 *
 * @category    Framework
 * @package     Opus
 */
class Opus_Collection extends Opus_Model_AbstractDb
{
    /**
     * Specify then table gateway.
     *
     * @var string Classname of Zend_DB_Table to use if not set in constructor.
     */
    protected static $_tableGatewayClass = 'Opus_Db_CollectionsContents';

    /**
     * Holds the role of the collection.
     *
     * @var int
     */
    private $__role_id = null;

    /**
     * The collections external fields, i.e. those not mapped directly to the
     * Opus_Db_CollectionsContents table gateway.
     *
     * @var array
     * @see Opus_Model_Abstract::$_externalFields
     */
    protected $_externalFields = array(
        'SubCollection' => array(
            'fetch' => 'lazy',
            'model' => 'Opus_Collection'),
        'ParentCollection' => array(
            'fetch' => 'lazy',
            'model' => 'Opus_Collection'),
        );

    /**
     * Fields that should not be displayed on a form.
     *
     * @var array  Defaults to array('SubCollection', 'ParentCollection').
     */
    protected $_hiddenFields = array(
            'SubCollection',
            'ParentCollection',
        );

    /**
     * Fetches existing or creates new collection.
     *
     * @param  int|string  $role           The role that this collection is in.
     * @param  int         $collection_id  (Optional) Id of an existing collection.
     * @param  int         $parent         (Optional) parent Id of a new collection.
     * @param  int         $left_sibling   (Optional) left sibling Id of a new collection.
     */
    public function __construct($role_id, $collection_id = null, $parent = null, $left_sibling = null) {
        if (is_null($collection_id) === true) {
            if (is_null($parent) === true or is_null($left_sibling) === true) {
                throw new Opus_Model_Exception('New collection requires parent and left sibling id to be passed.');
            } else {
                $collection_id = Opus_Collection_Information::newCollection($role_id, $parent, $left_sibling, null);
            }
        }
        $this->__role_id = $role_id;
        parent::__construct($collection_id, new Opus_Db_CollectionsContents($role_id));
    }

    /**
     * Sets up field by analyzing collection content table metadata.
     *
     * @return void
     */
    protected function _init() {

        // Add all database column names except primary keys as Opus_Model_Fields
        $table = new Opus_Db_CollectionsContents($this->__role_id);
        $info = $table->info();
        $dbFields = $info['metadata'];
        foreach (array_keys($dbFields) as $dbField) {
            if (in_array($dbField, $info['primary'])) {
                continue;
            }
            // Convert snake_case to CamelCase for fieldnames
            $fieldname = '';
            foreach(explode('_', $dbField) as $part) {
                $fieldname .= ucfirst($part);
            }
            $field = new Opus_Model_Field($fieldname);
            $this->addField($field);
        }

        // Add a field to hold subcollections
        $subCollectionField = new Opus_Model_Field('SubCollection');
        $subCollectionField->setMultiplicity('*');
        $this->addField($subCollectionField);

        // Add a field to hold parentcollections
        $parentCollectionField = new Opus_Model_Field('ParentCollection');
        $parentCollectionField->setMultiplicity('*');
        $this->addField($parentCollectionField);
    }

    /**
     * Fetches the entries in this collection.
     *
     * @return array $documents The documents in the collection.
     */
    public function getEntries() {
        $docIds = Opus_Collection_Information::getAllCollectionDocuments((int) $this->__role_id, (int) $this->getId());
        $documents = array();
        foreach ($docIds as $docId) {
            $documents[] = new Opus_Document($docId);
        }
        return $documents;
    }

    /**
     * Adds a document to this collection.
     *
     * @param  Opus_Document  $document The document to add.
     * @return void
     */
    public function addEntry(Opus_Model_AbstractDb $model) {
        $linkTable = new Opus_Db_LinkDocumentsCollections((int) $this->__role_id);
        $link = $linkTable->createRow();
        $link->documents_id = $model->getId();
        $link->collections_id = $this->getId();
        $link->save();
    }

    /**
     * Returns subcollections.
     *
     * @return Opus_Collection|array Subcollection(s).
     */
    protected function _fetchSubCollection() {
        $collections = Opus_Collection_Information::getSubCollections((int) $this->__role_id, (int) $this->getId());
        $collectionIds = array();
        foreach ($collections as $collection) {
            $collectionIds[] = $collection['structure']['collections_id'];
        }
        $result = array();
        if (empty($collectionIds) === false) {
            $result = array();
            $table = new Opus_Db_CollectionsContents($this->__role_id);
            $rows = $table->find($collectionIds);

            foreach ($collectionIds as $key => $collectionId) {
                foreach ($rows as $row) {
                    $rowArray = $row->toArray();
                    if ($collectionId === $rowArray['id']) {
                        $result[$key] = new Opus_Collection((int) $this->__role_id, $row);
                    }
                }

            }
        }
        return $result;
    }

    /**
     * Overwrites store procedure.
     * TODO: Implement storing collection structures.
     *
     * @return void
     */
    protected function _storeSubCollection() {

    }

    /**
     * Returns parentcollections.
     *
     * @param  int  $index (Optional) Index of the parentcollection to fetchl.
     * @return Opus_Collection|array Parentcollection(s).
     */
    protected function _fetchParentCollection() {
        $result = array();
        $collectionIds = Opus_Collection_Information::getAllParents($this->__role_id, (int) $this->getId());
        $result = array();
        if (empty($collectionIds) === false) {
            $result = array();
            $table = new Opus_Db_CollectionsContents($this->__role_id);
            $rows = $table->find($collectionIds);
            foreach ($rows as $row) {
                $result[] = new Opus_Collection((int) $this->__role_id, $row);
            }
        }
        return $result;
    }

    /**
     * Overwrites store procedure.
     * TODO: Implement storing collection structures.
     *
     * @return void
     */
    protected function _storeParentCollection() {

    }

    /**
     * Overwrites standard toArray() to prevent infinite recursion due to parent collections.
     *
     * @return array A (nested) array representation of the model.
     */
    public function toArray() {
        $result = array();
        foreach ($this->getSubCollection() as $subCollection) {
            $result[] = array(
                    'Id' => $subCollection->getId(),
                    'Name' => $subCollection->getName(),
                    'SubCollection' => $subCollection->toArray(),
                );
        }
        return $result;
    }

    /**
     * Overwrite to reconnect to correct primary table row in database after unserializing.
     *
     * @return void
     */
    public function __wakeup() {
        $table = new Opus_Db_CollectionsContents($this->__role_id);
        $this->_primaryTableRow->setTable($table);
    }

    /**
     * Overwrite standard deletion in favour of collections history tracking.
     *
     * @return void
     */
    public function delete() {
        Opus_Collection_Information::deleteCollection($this->__role_id, (int) $this->getId());
    }
}
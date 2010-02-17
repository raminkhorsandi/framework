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
 * @package     Opus_Model
 * @author      Felix Ostrowski (ostrowski@hbz-nrw.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Abstract class to linkt model account with model role in the Opus framework.
 *
 * @category    Framework
 * @package     Opus_Model
 */
class Opus_Model_Dependent_Link_FileRole extends Opus_Model_Dependent_Link_Abstract
{

    /**
     * Specify then table gateway.
     *
     * @var string Classname of Zend_DB_Table to use if not set in constructor.
     */
    protected static $_tableGatewayClass = 'Opus_Db_Privileges';

    /**
     * Primary key of the parent model.
     *
     * @var mixed $_parentId.
     */
    protected $_parentColumn = 'file_id';

    /**
     * The class of the model that is linked to.
     *
     * @var string
     */
    protected $_modelClass = 'Opus_Role';

    /**
     * The name of the field containing an identifying string.
     *
     * @var string
     */
    protected $_displayAttributeName = 'Privilege';

    /**
     * Initialize model with the following values:
     * - Role
     *
     * @return void
     */
    protected function _init() {
        if (is_null($this->getId()) === false) {
            $this->setModel(new Opus_Role($this->_primaryTableRow->role_id));
        } else {
            $this->setModel(new Opus_Role);
        }
    }

    /**
     * Persist foreign model & link.
     *
     * @return void
     */
    public function store() {
        $this->_primaryTableRow->role_id = $this->_model->store();
        // only store if something has changed
        // this avoids duplicate entries
        if ($this->getId() !== $this->_primaryTableRow->role_id) {
            parent::store();
        }
    }

}
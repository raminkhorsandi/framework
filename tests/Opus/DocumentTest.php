<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
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
 * @category    Tests
 * @package     Opus
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @author      Ralf Claußnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Felix Ostrowski (ostrowski@hbz-nrw.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


/**
 * Test cases for class Opus_Document.
 *
 * @package Opus
 * @category Tests
 *
 * @group DocumentTest
 *
 */
class Opus_DocumentTest extends PHPUnit_Framework_TestCase {


    /**
     * Test document type.
     *
     * @var string
     */
    protected $_xmlDoctype =
        '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

            <field name="Language" mandatory="yes" />
            <field name="Licence"/>
            <field name="ContributingCorporation"/>
            <field name="CreatingCorporation"/>
            <field name="ContributingCorporation"/>

            <field name="DateAccepted"/>
            <field name="Edition"/>
            <field name="Issue"/>
            <field name="NonInstituteAffiliation"/>
            <field name="PageFirst"/>
            <field name="PageLast"/>
            <field name="PageNumber"/>

            <mandatory type="one-at-least">
                <field name="CompletedYear"/>
                <field name="CompletedDate"/>
            </mandatory>

            <field name="Reviewed"/>
            <field name="ServerDateUnlocking"/>
            <field name="ServerDateValid"/>
            <field name="Source"/>
            <field name="IdentifierOpac"/>
            <field name="Volume"/>

        </documenttype>';


    /**
     * Test fixture document type.
     *
     * @var Opus_Document_Type
     */
    protected $_type = null;


    /**
     * Set up test fixture.
     *
     * @return void
     */
    public function setUp() {

        TestHelper::clearTable('document_identifiers');
        TestHelper::clearTable('link_persons_documents');
//        TestHelper::clearTable('link_institutes_documents');
        TestHelper::clearTable('link_documents_licences');
        TestHelper::clearTable('document_title_abstracts');
        TestHelper::clearTable('documents');
        TestHelper::clearTable('document_patents');
        TestHelper::clearTable('document_notes');
        TestHelper::clearTable('document_enrichments');
        TestHelper::clearTable('document_licences');
//        TestHelper::clearTable('institutes_contents');
        TestHelper::clearTable('persons');

        $this->_type = new Opus_Document_Type($this->_xmlDoctype);
        $adapter = Zend_Db_Table::getDefaultAdapter();

        // Set up a mock language list.
        $list = array('de' => 'Test_Deutsch', 'en' => 'Test_Englisch', 'fr' => 'Test_Französisch');
        Zend_Registry::set('Available_Languages', $list);
    }

    /**
     * Tear down test fixture.
     *
     * @return void
     */
    public function tearDown() {
        TestHelper::clearTable('document_identifiers');
        TestHelper::clearTable('link_persons_documents');
//        TestHelper::clearTable('link_institutes_documents');
        TestHelper::clearTable('link_documents_licences');
        TestHelper::clearTable('document_title_abstracts');
        TestHelper::clearTable('documents');
        TestHelper::clearTable('document_patents');
        TestHelper::clearTable('document_notes');
        TestHelper::clearTable('document_enrichments');
        TestHelper::clearTable('document_licences');
//        TestHelper::clearTable('institutes_contents');
        TestHelper::clearTable('persons');
    }


    /**
     * Test if a Document instance can be serialized.
     *
     * @return void
     */
    public function testSerializing() {
        $doc = new Opus_Document(null, $this->_type);
        $ser = serialize($doc);
    }

    /**
     * Test if a serialized Document instance can be deserialized.
     *
     * @return void
     */
    public function testDeserializing() {
        $doc1 = new Opus_Document(null, $this->_type);
        $ser = serialize($doc1);
        $doc2 = unserialize($ser);
        $this->assertEquals($doc1, $doc2, 'Deserializing unsuccesful.');
    }

    /**
     * Valid document data.
     *
     * @var array  An array of arrays of arrays. Each 'inner' array must be an
     * associative array that represents valid document data.
     */
    protected static $_validDocumentData = array(
            array(
                array(
                    'Language' => 'de',
                    'ContributingCorporation' => 'Contributing, Inc.',
                    'CreatingCorporation' => 'Creating, Inc.',
                    'DateAccepted' => '1901-01-01',
                    'Edition' => 2,
                    'Issue' => 3,
                    'Volume' => 1,
                    'NonInstituteAffiliation' => 'Wie bitte?',
                    'PageFirst' => 1,
                    'PageLast' => 297,
                    'PageNumber' => 297,
                    'CompletedYear' => 1960,
                    'CompletedDate' => '1901-01-01',
                    'Reviewed' => 'peer',
                    'ServerDateUnlocking' => '2008-12-01',
                    'ServerDateValid' => '2008-12-01',
                    'Source' => 'BlaBla',
                )
            )
        );

    /**
     * Valid document data provider
     *
     * @return array
     */
    public static function validDocumentDataProvider() {
        return self::$_validDocumentData;
    }



    /**
     * Test if tunneling setter calls through a n:m link model reaches
     * the target model instance.
     *
     * @return void
     */
    public function testTunnelingSetterCallsInManyToManyLinks() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Licence" multiplicity="3"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $licence = new Opus_Licence();

        $doc->addLicence($licence);
        $doc->getLicence(0)->setSortOrder(47);
        $value = $doc->getLicence(0)->getSortOrder();

        $this->assertEquals(47, $value, 'Wrong value returned from linked model.');
    }


    /**
     * Test if adding an many-to-many models works.
     *
     * @return void
     */
    public function testAddingModelInManyToManyLink() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Licence" multiplicity="3"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);

        $value = $doc->getLicence();
        $this->assertTrue(is_array($value), 'Expected array type.');
        $this->assertEquals(0, count($value), 'Expected zero objects to be returned initially.');

        $doc->addLicence(new Opus_Licence());
        $value = $doc->getField('Licence')->getValue();
        $this->assertTrue(is_array($value), 'Expected array type.');
        $this->assertEquals(1, count($value), 'Expected only one object to be returned after adding.');
        $this->assertType('Opus_Model_Dependent_Link_DocumentLicence', $value[0], 'Returned object is of wrong type.');
    }


    /**
     * Test if adding an one-to-many model works.
     *
     * @return void
     */
    public function testAddingModelInOneToManyLink() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Note" multiplicity="*"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);

        $value = $doc->getNote();
        $this->assertTrue(is_array($value), 'Expected array type.');
        $this->assertEquals(0, count($value), 'Expected zero objects to be returned initially.');

        $doc->addNote();
        $value = $doc->getNote();
        $this->assertTrue(is_array($value), 'Expected array type.');
        $this->assertEquals(1, count($value), 'Expected only one object to be returned after adding.');
        $this->assertType('Opus_Note', $value[0], 'Returned object is of wrong type.');
    }


    /**
     * Test if storing a document wich has a linked model doesnt throw
     * an Opus_Model_Exception.
     *
     * @return void
     *
     */
    public function testStoreWithLinkToIndependentModel() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="PersonAuthor" multiplicity="*"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);

        $author = new Opus_Person();
        $author->setFirstName('Ludwig');
        $author->setLastName('Wittgenstein');
        $doc->addPersonAuthor($author);

        $doc->store();
    }

    /**
     * Test if adding more values to a multi-value field than it may hold throws
     * an InvalidArgumentException.
     *
     * @return void
     */
    public function testAddingMoreValuesThanMultiplicityAllowsThrowsException() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="PersonAuthor" multiplicity="2"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $document = new Opus_Document(null, $type);

        $author = new Opus_Person();
        $author->setFirstName('Ludwig');
        $author->setLastName('Wittgenstein');

        $document->addPersonAuthor($author);
        $document->addPersonAuthor($author);
        $this->setExpectedException('InvalidArgumentException');
        $document->addPersonAuthor($author);

    }

    /**
     * Test if adding a value to a single-value field that is already populated
     * throws an InvaludArgumentException.
     *die;
     * @return void
     */
    public function testAddingValuesToPopulatedSingleValueFieldThrowsException() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Enrichment"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $document = new Opus_Document(null, $type);

        $enrichment = new Opus_Enrichment;
        $enrichment->setValue('Poor enrichment.');

        $document->addEnrichment($enrichment);
        $this->setExpectedException('InvalidArgumentException');
        $document->addEnrichment($enrichment);
    }

    /**
     * Test if an exception is thrown when using a model in a field that does
     * not extend Opus_Model_Abstract and for which no custom _fetch method
     * is defined.
     *
     * @return void
     */
    public function testUndefinedFetchMethodForFieldValueClassNotExtendingAbstractModelThrowsException() {
        $this->setExpectedException('Opus_Model_Exception');
        $document = new Opus_Model_ModelWithNonAbstractExtendingClassField;
    }

    /**
     * FIXME: Handling of Files and Enrichments are not tested!
     *
     * Test if a document's fields come out of the database as they went in.
     *
     * @param array $documentDataset Array with valid data of documents.
     * @return void
     *
     * @dataProvider validDocumentDataProvider
     */
    public function testDocumentFieldsPersistDatabaseStorage(array $documentDataset) {
        // FIXME Fix date field problem
        $this->markTestSkipped('Invalid scheme for storing date fields breaks test.');

        Opus_Document_Type::setXmlDoctypePath(dirname(__FILE__));
        $document = new Opus_Document(null, 'article');
        foreach ($documentDataset as $fieldname => $value) {
            $callname = 'set' . $fieldname;
            $document->$callname($value);
        }

        $title = $document->addTitleMain();
        $title->setValue('Title');
        $title->setLanguage('de');

        $abstract = $document->addTitleAbstract();
        $abstract->setValue('Abstract');
        $abstract->setLanguage('fr');

        $parentTitle = $document->addTitleParent();
        $parentTitle->setValue('Parent');
        $parentTitle->setLanguage('en');

        $isbn = $document->addIdentifierIsbn();
        $isbn->setValue('123-123-123');

        $note = $document->addNote();
        $note->setMessage('Ich bin eine öffentliche Notiz.');
        $note->setCreator('Jim Knopf');
        $note->setScope('public');

        $patent = $document->addPatent();
        $patent->setCountries('Lummerland');
        $patent->setDateGranted('2008-12-05');
        $patent->setNumber('123456789');
        $patent->setYearApplied('2008');
        $patent->setApplication('Absolutely none.');

//      $enrichment = $document->addEnrichment();
//      $enrichment->setValue('Poor enrichment.');

        $author = new Opus_Person();
        $author->setFirstName('Ludwig');
        $author->setLastName('Wittgenstein');
        $author->setDateOfBirth('1889-04-26');
        $author->setPlaceOfBirth('Wien');
        $document->addPersonAuthor($author);

        $author = new Opus_Person();
        $author->setFirstName('Ferdinand');
        $author->setLastName('de Saussure');
        $author->setDateOfBirth('1857-11-26');
        $author->setPlaceOfBirth('Genf');
        $document->addPersonAuthor($author);

        $licence = new Opus_Licence;
        $licence->setActive(1);
        $licence->setLanguage('de');
        $licence->setLinkLicence('http://creativecommons.org/');
        $licence->setMimeType('text/pdf');
        $licence->setNameLong('Creative Commons');
        $licence->setPodAllowed(1);
        $licence->setSortOrder(0);
        $document->addLicence($licence);

        // Save document, modify, and save again.
        $id = $document->store();
        $document = new Opus_Document($id);
        $title = $document->addTitleMain();
        $title->setValue('Title Two');
        $title->setLanguage('en');
        $id = $document->store();
        $document = new Opus_Document($id);

        foreach ($documentDataset as $fieldname => $value) {
            $this->assertEquals($value, $document->{'get' . $fieldname}(), "Field $fieldname was changed by database.");
        }
        $this->assertEquals($document->getTitleMain(0)->getValue(), 'Title');
        $this->assertEquals($document->getTitleMain(0)->getLanguage(), 'de');
        $this->assertEquals($document->getTitleMain(1)->getValue(), 'Title Two');
        $this->assertEquals($document->getTitleMain(1)->getLanguage(), 'en');
        $this->assertEquals($document->getTitleAbstract()->getValue(), 'Abstract');
        $this->assertEquals($document->getTitleAbstract()->getLanguage(), 'fr');
        $this->assertEquals($document->getTitleParent()->getValue(), 'Parent');
        $this->assertEquals($document->getTitleParent()->getLanguage(), 'en');
        $this->assertEquals($document->getIdentifierIsbn()->getValue(), '123-123-123');
        $this->assertEquals($document->getNote()->getMessage(), 'Ich bin eine öffentliche Notiz.');
        $this->assertEquals($document->getNote()->getCreator(), 'Jim Knopf');
        $this->assertEquals($document->getNote()->getScope(), 'public');
        $this->assertEquals($document->getPatent()->getCountries(), 'Lummerland');
        $this->assertEquals($document->getPatent()->getDateGranted(), '2008-12-05');
        $this->assertEquals($document->getPatent()->getNumber(), '123456789');
        $this->assertEquals($document->getPatent()->getYearApplied(), '2008');
        $this->assertEquals($document->getPatent()->getApplication(), 'Absolutely none.');
//      $this->assertEquals($document->getEnrichment()->getValue(), 'Poor enrichment.');
//      $this->assertEquals($document->getEnrichment()->getType(), 'nonesense');
        $this->assertEquals($document->getPersonAuthor(0)->getFirstName(), 'Ludwig');
        $this->assertEquals($document->getPersonAuthor(0)->getLastName(), 'Wittgenstein');
        $this->assertEquals($document->getPersonAuthor(0)->getDateOfBirth(), '1889-04-26 00:00:00');
        $this->assertEquals($document->getPersonAuthor(0)->getPlaceOfBirth(), 'Wien');
        $this->assertEquals($document->getPersonAuthor(1)->getFirstName(), 'Ferdinand');
        $this->assertEquals($document->getPersonAuthor(1)->getLastName(), 'de Saussure');
        $this->assertEquals($document->getPersonAuthor(1)->getDateOfBirth(), '1857-11-26 00:00:00');
        $this->assertEquals($document->getPersonAuthor(1)->getPlaceOfBirth(), 'Genf');
        $this->assertEquals($document->getLicence()->getActive(), 1);
        $this->assertEquals($document->getLicence()->getLanguage(), 'de');
        $this->assertEquals($document->getLicence()->getLinkLicence(), 'http://creativecommons.org/');
        $this->assertEquals($document->getLicence()->getMimeType(), 'text/pdf');
        $this->assertEquals($document->getLicence()->getNameLong(), 'Creative Commons');
        $this->assertEquals($document->getLicence()->getPodAllowed(), 1);
        $this->assertEquals($document->getLicence()->getSortOrder(), 0);
    }

    /**
     * Test if corresponding links to persons are removed when deleting a document.
     *
     * @return void
     */
    public function testDeleteDocumentCascadesPersonLinks() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="PersonAuthor" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $author = new Opus_Person();
        $author->setLastName('Gandi');

        $doc->addPersonAuthor($author);
        $doc->store();

        $linkId = $doc->getField('PersonAuthor')->getValue()->getId();

        $doc->delete();

        $this->setExpectedException('Opus_Model_Exception');
        $link = new Opus_Model_Dependent_Link_DocumentPerson($linkId);
    }

    /**
     * Test if corresponding links to licences are removed when deleting a document.
     *
     * @return void
     */
    public function testDeleteDocumentCascadesLicenceLinks() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Licence" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $licence = new Opus_Licence();
        $licence->setNameLong('LongName');
        $licence->setLinkLicence('http://long.org/licence');

        $doc->addLicence($licence);
        $doc->store();
        $linkid = $doc->getField('Licence')->getValue()->getId();
        $doc->delete();

        $this->setExpectedException('Opus_Model_Exception');
        $link = new Opus_Model_Dependent_Link_DocumentLicence($linkid);
    }

    /**
     * Test if corresponding enrichments are removed when deleting a document.
     *
     * @return void
     */
    public function testDeleteDocumentCascadesEnrichments() {
        $this->markTestSkipped('Enrichments currently under development.');
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Enrichment" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $enrichment = new Opus_Enrichment();

        $doc->addEnrichment($enrichment);
        $doc->store();
        $id = $doc->getEnrichment()->getId();
        $doc->delete();
        $this->setExpectedException('Opus_Model_Exception');
        $enrichment = new Opus_Enrichment($id);
    }

    /**
     * Test if corresponding identifiers are removed when deleting a document.
     *
     * @return void
     */
    public function testDeleteDocumentCascadesIdentifiers() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="IdentifierIsbn" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $isbn = new Opus_Identifier();
        $isbn->setValue('ISBN');

        $doc->addIdentifierIsbn($isbn);
        $doc->store();
        $id = $doc->getIdentifierIsbn()->getId();
        $doc->delete();
        $this->setExpectedException('Opus_Model_Exception');
        $isbn = new Opus_Identifier($id);
    }

    /**
     * Test if corresponding patents are removed when deleting a document.
     *
     * @return void
     */
    public function testDeleteDocumentCascadesPatents() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Patent" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $patent = new Opus_Patent();
        $patent->setNumber('X0815');
        $patent->setDateGranted('01-01-2001');

        $doc->addPatent($patent);
        $doc->store();
        $id = $doc->getPatent()->getId();
        $doc->delete();
        $this->setExpectedException('Opus_Model_Exception');
        $patent = new Opus_Patent($id);
    }

    /**
     * Test if corresponding notes are removed when deleting a document.
     *
     * @return void
     */
    public function testDeleteDocumentCascadesNotes() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Note" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $note = new Opus_Note();
        $note->setMessage('A note!')
            ->setCreator('Me');

        $doc->addNote($note);
        $doc->store();
        $id = $doc->getNote()->getId();
        $doc->delete();
        $this->setExpectedException('Opus_Model_Exception');
        $note = new Opus_Note($id);
    }

    /**
     * Test if corresponding subjects are removed when deleting a document.
     *
     * @return void
     */
    public function testDeleteDocumentCascadesSubjects() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="SubjectSwd" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $subject = new Opus_Subject();
        $subject->setValue('Schlagwort');

        $doc->addSubjectSwd($subject);
        $doc->store();
        $id = $doc->getSubjectSwd()->getId();
        $doc->delete();
        $this->setExpectedException('Opus_Model_Exception');
        $subject = new Opus_Subject($id);
    }

    /**
     * Test if corresponding titles are removed when deleting a document.
     *
     * @return void
     */
    public function testDeleteDocumentCascadesTitles() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="TitleMain" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $title = new Opus_Title();
        $title->setValue('Title of a document');

        $doc->addTitleMain($title);
        $doc->store();
        $id = $doc->getTitleMain()->getId();
        $doc->delete();
        $this->setExpectedException('Opus_Model_Exception');
        $title = new Opus_Title($id);
    }

    /**
     * Test if corresponding abstracts are removed when deleting a document.
     *
     * @return void
     */
    public function testDeleteDocumentCascadesAbstracts() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="TitleAbstract" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $abstract = new Opus_Abstract();
        $abstract->setValue('It is necessary to give an abstract.');

        $doc->addTitleAbstract($abstract);
        $doc->store();
        $id = $doc->getTitleAbstract()->getId();
        $doc->delete();
        $this->setExpectedException('Opus_Model_Exception');
        $abstract = new Opus_Title($id);
    }

    /**
     * Test if a set of documents can be retrieved by getAll().
     *
     * @return void
     */
    public function testRetrieveAllDocuments() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="test_type"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="TitleAbstract" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $docs[] = new Opus_Document(null, $type);
        $docs[] = new Opus_Document(null, $type);
        $docs[] = new Opus_Document(null, $type);
        foreach ($docs as $doc) {
            $doc->store();
        }

        $result = Opus_Document::getAll();
        $this->assertEquals(count($docs), count($result), 'Wrong number of objects retrieved.');
    }

    /**
     * Test if an empty title list can be retrieved from an empty database.
     *
     * @return void
     */
    public function testRetrieveEmptyTitleListFromEmptyDatabase() {
        $this->assertTrue(false, 'Cannot check title list - Opus_Document::getAllDocumentTitles does not exist.');
        return;

        $result = Opus_Document::getAllDocumentTitles();
        $this->assertTrue(empty($result), 'Title list contains phantom results.');
    }

    /**
     * Test if a correct title list can be retrieved.
     *
     * @return void
     */
    public function testRetrieveAllTitles() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="test_type"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="TitleMain" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);

        $doc1 = new Opus_Document(null, 'test_type');
        $title1 = $doc1->addTitleMain();
        $title1->setLanguage('de');
        $title1->setValue('Ein deutscher Titel');
        $id1 = $doc1->store();

        $doc2 = new Opus_Document(null, 'test_type');
        $title2 = $doc2->addTitleMain();
        $title2->setLanguage('en');
        $title2->setValue('An english titel');
        $id2 = $doc2->store();

        $result = Opus_Document::getAllDocumentTitles();
        $this->assertEquals(2, count($result), 'Wrong number of title entries.');
        $this->assertEquals($title1->getValue(), $result[$id1][0], 'Expected title is not in the list.');
        $this->assertEquals($title2->getValue(), $result[$id2][0], 'Expected title is not in the list.');
    }

    /**
     * Test if the corresponding document id is set for each titile in the tile list.
     *
     * @return void
     */
    public function testRetrieveDocumentIdPerTitle() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="test_type"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="TitleMain" multiplicity="*" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);

        $doc1 = new Opus_Document(null, 'test_type');
        $title1 = $doc1->addTitleMain();
        $title1->setLanguage('de');
        $title1->setValue('Ein deutscher Titel');
        $title2 = $doc1->addTitleMain();
        $title2->setLanguage('en');
        $title2->setValue('Ein englischer Titel');
        $id1 = $doc1->store();

        $doc2 = new Opus_Document(null, 'test_type');
        $title3 = $doc2->addTitleMain();
        $title3->setLanguage('en');
        $title3->setValue('An english titel');
        $id2 = $doc2->store();

        $result = Opus_Document::getAllDocumentTitles();

        $this->assertEquals($title1->getValue(), $result[$id1][0], 'Wrong document id for title.');
        $this->assertEquals($title2->getValue(), $result[$id1][1], 'Wrong document id for title.');
        $this->assertEquals($title3->getValue(), $result[$id2][0], 'Wrong document id for title.');
    }

    /**
     * Test if adding a model to a field that is defined as a link sets the
     * field value to the corresponding dependent link model.
     *
     * TODO: This test should be moved to AbstractTest.
     *
     * @return void
     */
    public function testAddLinkModel() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Licence" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $document = new Opus_Document(null, $type);
        $licence = new Opus_Licence;
        $document->addLicence($licence);

        $this->assertTrue($document->getField('Licence')->getValue() instanceof Opus_Model_Dependent_Link_Abstract,
                'Adding to a field containing a link model failed.');
    }

    /**
     * Test if setting a model's field that is defined as a link sets the
     * field value to the corresponding dependent link model.
     *
     * TODO: This test should be moved to AbstractTest.
     *
     * @return void
     */
    public function testSetLinkModel() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Licence" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $document = new Opus_Document(null, $type);
        $licence = new Opus_Licence;
        $document->setLicence($licence);

        $this->assertTrue($document->getField('Licence')->getValue() instanceof Opus_Model_Dependent_Link_Abstract,
                'Setting a field containing a link model failed.');
    }

    /**
     * Test if getting a model's field value  that is defined as a link sets the
     * field value to the corresponding dependent link model.
     *
     * TODO: This test should be moved to AbstractTest.
     *
     * @return void
     */
    public function testGetLinkModel() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="doctoral_thesis"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Licence" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);
        $document = new Opus_Document(null, $type);
        $licence = new Opus_Licence;
        $document->setLicence($licence);
        $licence = $document->getField('Licence')->getValue();

        $this->assertTrue($licence instanceof Opus_Model_Dependent_Link_Abstract,
                'Getting a field value containing a link model failed.');
    }

    /**
     * Test if title informations delivered back properly with toArray().
     *
     * @return void
     */
    public function testToArrayReturnsCorrectValuesForTitleMain(){
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="test_type"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="TitleMain" multiplicity="*" />
        </documenttype>';
        $type = new Opus_Document_Type($xml);

        $doc = new Opus_Document(null, 'test_type');
        $title = $doc->addTitleMain();
        $title->setLanguage('de');
        $title->setValue('Ein deutscher Titel');
        $id = $doc->store();

        $loaded_document = new Opus_Document($id);
        $iterim_result = $loaded_document->toArray();
        $result = $iterim_result['TitleMain'][0];
        $expected = array(
            'Language' => 'de',
            'Value' => 'Ein deutscher Titel',
            'SortOrder' => null
            );
        $this->assertEquals($expected, $result, 'toArray() deliver not expected title data.');
    }

    /**
     * Test if multiple languages are (re)stored properly.
     *
     * @return void
     */
    public function testMultipleLanguageStorage() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="meintest"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="Language" multiplicity="3"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);

        $doc->addLanguage('de');
        $doc->addLanguage('en');
        $doc->addLanguage('fr');
        $languages = $doc->getLanguage();
        $id = $doc->store();
        $doc = new Opus_Document($id);

        $this->assertEquals($languages, $doc->getLanguage(), 'Document language list corrupted by storage.');
    }

    /**
     * Test storing of a urn.
     *
     * @return void
     */
    public function testStoringOfOneIdentifierUrn() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="meintest"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="IdentifierUrn" multiplicity="1"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);

        $id = $doc->store();
        $doc2 = new Opus_Document($id);
        $urn_value = $doc2->getIdentifierUrn(0)->getValue();

        $urn = new Opus_Identifier_Urn('swb', '14', 'opus');
        $this->assertEquals($urn->getUrn($id), $urn_value, 'Stored and expected URN value did not match.');
    }

    /**
     * Test saving of empty multiple urn fields.
     *
     * @return void
     */
    public function testStoringOfMultipleIdentifierUrnField() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="meintest"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="IdentifierUrn" multiplicity="2"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);

        $id = $doc->store();
        $doc2 = new Opus_Document($id);
        $urn_value = $doc2->getIdentifierUrn(0)->getValue();

        $urn = new Opus_Identifier_Urn('swb', '14', 'opus');
        $this->assertEquals($urn->getUrn($id), $urn_value, 'Stored and expected URN value did not match.');
        $this->assertEquals(1, count($doc2->getIdentifierUrn()), 'On an empty multiple field only 2 URN value should be stored.');
    }

    /**
     * Ensure that existing urn values not overriden.
     *
     * @return void
     */
    public function testNotOverrideExistingUrn() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="meintest"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="IdentifierUrn" multiplicity="1"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);

        $urn_value = 'urn:nbn:de:swb:14-opus-5548';
        $urn_model = $doc->addIdentifierUrn();
        $urn_model->setValue($urn_value);

        $id = $doc->store();
        $doc2 = new Opus_Document($id);

        $this->assertEquals($urn_value, $doc2->getIdentifierUrn(0)->getValue(), 'Stored and expected URN value did not match.');
    }

    /**
     * Test storing document with empty identifier urn model create a urn.
     *
     * @return void
     */
    public function testStoreUrnWithEmptyModel() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="meintest"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="IdentifierUrn" multiplicity="1"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $urn_model = new Opus_Identifier();
        $doc->setIdentifierUrn($urn_model);
        $id = $doc->store();
        $doc2 = new Opus_Document($id);
        $this->assertNotNull($doc2->getIdentifierUrn(0)->getValue(), 'URN value should not be empty.');
        $urn = new Opus_Identifier_Urn('swb', '14', 'opus');
        $this->assertEquals($urn->getUrn($id), $doc2->getIdentifierUrn(0)->getValue(), 'Stored and expected URN value did not match.');
    }

    /**
     * Test if multiple existing URN values does not overriden.
     *
     * @return void
     */
    public function testNotOverrideExistingMultipleUrn() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="meintest"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="IdentifierUrn" multiplicity="2"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);

        $urn_value_1 = 'urn:nbn:de:swb:14-opus-5548';
        $urn_model = $doc->addIdentifierUrn();
        $urn_model->setValue($urn_value_1);

        $urn_value_2 = 'urn:nbn:de:swb:14-opus-5598';
        $urn_model = $doc->addIdentifierUrn();
        $urn_model->setValue($urn_value_2);
        $id = $doc->store();
        $doc2 = new Opus_Document($id);

        $this->assertEquals($urn_value_1, $doc2->getIdentifierUrn(0)->getValue(), 'Stored and expected URN value did not match.');
        $this->assertEquals($urn_value_2, $doc2->getIdentifierUrn(1)->getValue(), 'Stored and expected URN value did not match.');
    }

    /**
     * Test if at least one value inside a multiple urn values does not create a new urn.
     *
     * @return void
     */
    public function testNotOverridePartialExistingMultipleUrn() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="meintest"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="IdentifierUrn" multiplicity="2"/>
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);

        $urn_value_1 = 'urn:nbn:de:swb:14-opus-5548';
        $urn_model = $doc->addIdentifierUrn();
        $urn_model->setValue($urn_value_1);

        $urn_value_2 = 'urn:nbn:de:swb:14-opus-2345';
        $urn_model = $doc->addIdentifierUrn();
        $urn_model->setValue($urn_value_2);
        $id = $doc->store();
        $doc2 = new Opus_Document($id);

        $this->assertEquals($urn_value_1, $doc2->getIdentifierUrn(0)->getValue(), 'Stored and expected URN value did not match.');
        $this->assertEquals($urn_value_2, $doc2->getIdentifierUrn(1)->getValue(), 'Stored and expected URN value did not match.');
    }

    /**
     * Test if after creation of a document leaves the fields marked unmodified.
     *
     * @return void
     */
    public function testNewlyCreatedDocumentsHaveNoModifiedFields() {
        Opus_Document_Type::setXmlDoctypePath(dirname(__FILE__));
        $newdoc = new Opus_Document(null, 'article');
        $fieldnames = $newdoc->describe();
        foreach ($fieldnames as $fieldname) {
            $field = $newdoc->getField($fieldname);
            $this->assertFalse($field->isModified(), 'Field ' . $fieldname . ' marked as modified after creation.');
        }
    }

    /**
     * Test retrieving a document list based on server (publication) states.
     *
     * @return void
     */
    public function testGetByServerStateReturnsCorrectDocuments() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="meintest"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="TitleMain" />
        </documenttype>';

        $type = new Opus_Document_Type($xml);

        $publishedDoc1 = new Opus_Document(null, $type);
        $publishedDoc1->setServerState('published');
        $publishedDoc1->store();

        $publishedDoc2 = new Opus_Document(null, $type);
        $publishedDoc2->setServerState('published');
        $publishedDoc2->store();

        $unpublishedDoc1 = new Opus_Document(null, $type);
        $unpublishedDoc1->setServerState('unpublished');
        $unpublishedDoc1->store();

        $unpublishedDoc2 = new Opus_Document(null, $type);
        $unpublishedDoc2->setServerState('unpublished');
        $unpublishedDoc2->store();

        $deletedDoc1 = new Opus_Document(null, $type);
        $deletedDoc1->setServerState('deleted');
        $deletedDoc1->store();

        $deletedDoc2 = new Opus_Document(null, $type);
        $deletedDoc2->setServerState('deleted');
        $deletedDoc2->store();

        $publishedDocs = Opus_Document::getAllByState('published');
        $unpublishedDocs = Opus_Document::getAllByState('unpublished');
        $deletedDocs = Opus_Document::getAllByState('deleted');

        $this->assertEquals(2, count($publishedDocs));
        $this->assertEquals(2, count($unpublishedDocs));
        $this->assertEquals(2, count($deletedDocs));

    }

    /**
     * Test setting and getting date values on different ways and fields.
     *
     * @return void
     */
    public function testSettingAndGettingDateValues() {
        // FIXME Fix date field problem
        $this->markTestSkipped('Non-object return values of date fields break test.');

        $locale = new Zend_Locale('de_DE');
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="meintest"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="PublishedDate" />
            <field name="ServerDateUnlocking" />
            <field name="Patent" />
            <field name="PersonAuthor" />
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);

        $doc->setPublishedDate('10.05.2008');
        $doc->setServerDateUnlocking('05.04.2009');

        $personAuthor = new Opus_Person();
        $personAuthor->setLastName('Tester');
        $personAuthor->setDateOfBirth('26.03.1965');
        $doc->addPersonAuthor($personAuthor);

        $patent = new Opus_Patent();
        $patent->setNumber('08 15');
        $patent->setDateGranted('07.07.2008');
        $doc->addPatent($patent);

        $docId = $doc->store();

        $doc = new Opus_Document($docId);
        $publishedDate = $doc->getPublishedDate();
        $serverDateUnlocking = $doc->getServerDateUnlocking();
        $personAuthor = $doc->getPersonAuthor(0);
        $patent = $doc->getPatent(0);
        
        $localeFormatDate = Zend_Locale_Format::getDateFormat($locale);
        $this->assertEquals('05.10.2008', $publishedDate->getZendDate()->toString($localeFormatDate), 'Setting a date through string does not work.');
        $this->assertEquals('05.04.2009', $serverDateUnlocking->getZendDate()->toString($localeFormatDate), 'Setting a date through Zend_Date does not work.');
        $this->assertEquals('23.06.1965', $personAuthor->getDateOfBirth()->getZendDate()->toString($localeFormatDate), 'Setting a date on a model doesn not work.');
        $this->assertEquals('11.10.1999', $patent->getDateGranted()->getZendDate()->toString($localeFormatDate), 'Setting a date on a dependent model doesn not work.');
    }

    /**
     * Test if ServerState becomes value unpublished if not set and document is stored.
     *
     * @return void
     */
    public function testCheckIfDefaultServerStateValueIsSetCorrectAfterStoringModel() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
        <documenttype name="meintest"
            xmlns="http://schemas.opus.org/documenttype"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <field name="PublishedDate" />
            <field name="ServerDateUnlocking" />
            <field name="Patent" />
            <field name="PersonAuthor" />
        </documenttype>';

        $type = new Opus_Document_Type($xml);
        $doc = new Opus_Document(null, $type);
        $doc->store();
        
        $this->assertEquals('unpublished', $doc->getServerState(), 'ServerState should be unpublished if not set and document is stored.');    
    }

}

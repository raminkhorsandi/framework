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
 * @package     Opus_Security
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Test case for Opus_Security_Acl.
 *
 * @category    Tests
 * @package     Opus_Security
 *
 * @group       AclTest
 */
class Opus_Security_AclTest extends PHPUnit_Framework_TestCase {

    /**
     * Table adapter to accounts table.
     *
     * @var Zend_Db_Table
     */
    protected $_privileges = null;

    /**
     * Table adapter to resources table.
     *
     * @var Zend_Db_Table
     */
    protected $_resources = null;

    /**
     * Set up table adapter.
     *
     * @return void
     */
    public function setUp() {
        TestHelper::clearTable('privileges');
        $this->_privileges = new Opus_Db_Privileges();
        TestHelper::clearTable('resources');
        $this->_resources = new Opus_Db_Resources();
    }

    /**
     * Test if privileges table is initially empty.
     *
     * @return void
     */
    public function testPrivilegeTableIsInitiallyEmpty() {
        $rowset = $this->_privileges->fetchAll();
        $this->assertEquals(0, $rowset->count(), 'Privileges table is not empty no test begin.');
    }

    /**
     * Test if resources table is initially empty.
     *
     * @return void
     */
    public function testResourcesTableIsInitiallyEmpty() {
        $rowset = $this->_resources->fetchAll();
        $this->assertEquals(0, $rowset->count(), 'Resoucres table is not empty no test begin.');
    }

    /**
     * Test initallization of Opus_Security_Acl and if Opus_Security_Acl extends Zend_Acl.
     *
     * @return void
     */
    public function testOpusSecurityAclExtendsZendAcl() {
        $acl = new Opus_Security_Role();
        $this->assertTrue($acl instanceof Zend_Acl_Role_Interface, 'Opus_Security_Acl is not an instance of Zend_Acl!');
    }

    /**
     * Test if a resources is stored in the database after adding to the acl.
     *
     * @return void
     */
    public function testResourceExistsAfterAddingToAcl() {
        $acl = new Opus_Security_Acl();
        $resourceMock = new Opus_Model_ModelAbstract();
        $acl->add($resourceMock);
        $rowset = $this->_resources->fetchAll($this->_resources->select()->where('name = ?', $resourceMock->getResourceId()));
        $this->assertEquals(1, $rowset->count(), 'Opus_Security_Acl does not store resources in the DB.');
    }

    /**
     * Test if method has() loads resources from the database.
     *
     * @return void
     */
    public function testHasMethodLoadsResources() {
        TestHelper::clearTable('resources');
        $this->_resources = new Opus_Db_Resources();
        $acl = new Opus_Security_Acl();
        $resourceMock = new Opus_Model_ModelAbstract();
        $acl->add($resourceMock);
        $acl = new Opus_Security_Acl();
        $this->assertTrue($acl->has($resourceMock), 'Acl does not load resources from database.');
    }
}
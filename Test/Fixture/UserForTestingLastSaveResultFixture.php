<?php
/**
 * Bancha test fixture, only used in BanchaRemotableBehaviorTest::testGetLastSaveResult_ValidationFailed
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Fixture
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 2.1.0
 * @author        Andrejs Semovs <andrejs.semovs@gmail.com>
 */

/**
 * Bancha test fixture
 *
 * @package       Bancha.Test.Fixture
 * @author        Andrejs Semovs <andrejs.semovs@gmail.com>
 * @since         Bancha v 2.1.0
 */
class UserForTestingLastSaveResultFixture extends CakeTestFixture {

/**
 * fields property
 *
 * @var array
 * @access public
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'length' => null, 'collate' => null, 'comment' => ''),
		'login' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
	);

/**
 * records property
 *
 * @var array
 * @access public
 */
	public $records = array();
}

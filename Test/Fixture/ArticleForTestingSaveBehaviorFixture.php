<?php
/**
 * Bancha test fixture, only used in BanchaRemotableBehaviorTest::testModelSave
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.Fixture
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * Bancha test fixture
 *
 * @package       Bancha.Test.Fixture
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 0.9.0
 */
class ArticleForTestingSaveBehaviorFixture extends CakeTestFixture {

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => true),
		'date' => array('type' => 'datetime', 'null' => true),
		'body' => array('type' => 'string', 'null' => true),
		'published' => array('type' => 'integer', 'default' => 0),
		'user_id' => array('type' => 'integer', 'null' => false),
	);

/**
 * belongsTo associations
 * this article belongs to an author from the class below
 *
 * @var array
 */
	public $belongsTo = array('User');

/**
 * records property
 *
 * @var array
 */
	public $records = array();
}

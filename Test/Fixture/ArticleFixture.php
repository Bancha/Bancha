<?php
/**
 * Bancha test fixture
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @category      Tests
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * Bancha test fixture
 *
 * @package       Bancha.Test.Fixture
 */
class ArticleFixture extends CakeTestFixture {

/**
 * fields property
 *
 * @var array
 * @access public
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => true),
		'date' => array('type' => 'datetime', 'null' => true),
		'body' => array('type' => 'string', 'null' => true),
		'published' => array('type' => 'boolean', 'default' => false),
		'user_id' => array('type' => 'integer', 'null' => false),
	);

/**
 * records property
 *
 * @var array
 * @access public
 */
	public $records = array(
		array('id' => 988, 'title' => 'Title 1', 'date' => '2011-11-24 03:40:04', 'body' => 'Text 1', 'published' => true, 'user_id' => 2),
		array('id' => 989, 'title' => 'Title 2', 'date' => '2011-12-24 03:40:04', 'body' => 'Text 2', 'published' => false, 'user_id' => 3),
		array('id' => 990, 'title' => 'Title 3', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 3', 'published' => false, 'user_id' => 3),
	);
}

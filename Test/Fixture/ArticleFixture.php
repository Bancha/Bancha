<?php
/**
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/view/1196/Testing CakePHP(tm) Tests
 * @package       cake.tests.fixtures
 * @since         CakePHP(tm) v 1.2.0.4667
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Creates sample articles
 *
 * @package       cake.tests.fixtures
 */
class ArticleFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'User'
 * @access public
 */
	public $name = 'Article';
	
	public $table = true;

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
		'published' => array('type' => 'integer', 'default' => 0),
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
	);
}

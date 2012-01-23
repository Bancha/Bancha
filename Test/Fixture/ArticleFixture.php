<?php
/**
 * Bancha test ficture
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @category      Tests
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * Creates sample articles
 *
 * @package       Bancha.tests.fixtures
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
		array('id' => 990, 'title' => 'Title 3', 'date' => '2010-12-24 03:40:04', 'body' => 'Text 3', 'published' => false, 'user_id' => 3),
	);
}

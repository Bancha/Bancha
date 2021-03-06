<?php
/**
 * Bancha test fixture
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
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
class ArticlesTagFixture extends CakeTestFixture {

/**
 * fields property
 *
 * @var array
 * @access public
 */
	public $fields = array(
		'article_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'collate' => null, 'comment' => ''),
		'tag_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'collate' => null, 'comment' => ''),
	);

/**
 * records property
 *
 * @var array
 * @access public
 */
	public $records = array(
		array('article_id' => 1001, 'tag_id' => 1),
		array('article_id' => 1001, 'tag_id' => 2),
	);
}

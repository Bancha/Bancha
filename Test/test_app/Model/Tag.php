<?php
/**
 * Tag file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.test_app.Model
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 2.1.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * Tag Model
 *
 * @package       Bancha.Test.test_app.Model
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.1.0
 */
class Tag extends AppModel {

/**
 * Behaviors
 */
	public $actsAs = array('Bancha.BanchaRemotable');

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'string' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Article' => array(
			'className' => 'Article',
			'joinTable' => 'articles_tags',
			'foreignKey' => 'tag_id',
			'associationForeignKey' => 'article_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

}

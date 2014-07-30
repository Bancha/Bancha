<?php
/**
 * User file.
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.test_app.Model
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 2.1.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * User Model
 *
 * @package       Bancha.Test.test_app.Model
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.1.0
 */
class User extends AppModel {

/**
 * Bancha behavior
 */
	public $actsAs = array('Bancha.BanchaRemotable');

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array( // TODO example for validation rule "url" missing
		'id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'precision' => 0
			),
		),
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty')
			),
			'minLength' => array(
				'rule' => array('minLength', 3),
			),
			'maxLength' => array(
				'rule' => array('maxLength', 64),
			),
		),
		'login' => array(
			'isUnique' => array( // this has to be checked by the server (so there's nothing onthe frontend for this
				'rule' => array('isUnique'),
				'message' => "Login is already taken."
			),
			'minLength' => array(
				'rule' => array('minLength', 3),
				'required' => true, // this one is slick
			),
			'maxLength' => array(
				'rule' => array('maxLength', 64),
			),
			'alphaNumeric' => array(
				'rule' => array('alphaNumeric')
			),
		),
		'email' => array(
			'email' => array(
				'rule' => array('email'),
				'required' => true,
			),
		),
		'created' => array(
			'created' => array(
				'rule' => array('datetime', 'ymd'),
				'message' => 'Enter a valid date and time in "YYYY-MM-DD HH:MM:SS" format.',
				'allowEmpty' => true,
			),
		),
		'weight' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'precision' => 2
			),
		),
		'height' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'precision' => 0
			),
			'range' => array(
				'rule' => array('range', 49, 301),
				'message' => 'Please enter a value between 50 and 300cm.'
			)
		),
		'avatar' => array(
			'file' => array( // this validation rule forces Bancha.scaffold in the frontend to render a fileuploadfield
				'rule' => array('file')
			),
			'extension' => array(
				'rule' => array('extension', array('gif', 'jpeg', 'png', 'jpg')),
				'allowEmpty' => true,
				'message' => 'Please supply a valid image.'
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Article' => array(
			'className' => 'Article',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}

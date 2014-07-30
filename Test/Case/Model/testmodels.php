<?php
/**
 * BanchaRemotableBehaviorTest test models file.
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.Case.Model
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

/**
 * This model is used inside multiple
 * BanchaRemotableBehaviorTest tests
 *
 * @package       Bancha.Test.Case.Model
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 0.9.0
 */
class TestArticle extends CakeTestModel {

	public $name = 'Article';

	public $displayField = 'title';

	public $useTable = false;

	public $order = array('name.order' => 'ASC');

	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'length' => null, 'collate' => null, 'comment' => ''),
		'title' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
		'date' => array('type' => 'datetime', 'null' => true, 'default' => null, 'length' => null, 'collate' => null, 'comment' => ''),
		'body' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
		'published' => array('type' => 'boolean', 'null' => true, 'default' => false, 'length' => null, 'collate' => null, 'comment' => ''),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'length' => null, 'collate' => null, 'comment' => ''),
	//	'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
	//	'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $virtualFields = array(
		'headline' => 'CONCAT(TestArticle.date, " ", TestArticle.title' // we simply need a virtual field to test as well
	);

	public $validate = array(
		'title' => array(
			'alphaNumeric' => array(
				'rule' => array('alphaNumeric'),
			),
			'notEmpty' => array(
				'rule' => array('notEmpty'),
			),
		),
		'body' => 'alphaNumeric',
	);

	// used in testGetAssociated
	// tese rules are not fully valid

	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
		),
	);

	public $hasMany = array(
		'ArticleTag',
		'HasManyModel' => array(
			'className' => 'HasManyModel',
			'foreignKey' => 'article_id',
		),
	);

	public $hasAndBelongsToMany = array(
		'Tag' => array(
			'className' => 'Tag',
			'foreignKey' => 'article_id',
		),
	);
} //eo TestArticle


/**
 * This model is used inside
 * BanchaRemotableBehaviorTest::testGetValidations_NoValidationRules
 *
 * @package       Bancha.Test.Case.Model
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.0.0
 */
class TestArticleNoValidationRules extends CakeTestModel {

	public $name = 'ArticleNoValidationRules';

	public $useTable = false;

	public $order = array('name.order' => 'ASC');

	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'length' => null, 'collate' => null, 'comment' => ''),
		'title' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
		'date' => array('type' => 'datetime', 'null' => true, 'default' => null, 'length' => null, 'collate' => null, 'comment' => ''),
		'body' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
		'published' => array('type' => 'boolean', 'null' => true, 'default' => false, 'length' => null, 'collate' => null, 'comment' => ''),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'length' => null, 'collate' => null, 'comment' => ''),
	//	'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
	//	'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
} //eo TestArticleNoValidationRules

/**
 * This model is used inside
 * BanchaRemotableBehaviorTest::testGetValidations_BasicStructure
 *
 * @package       Bancha.Test.Case.Model
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.0.0
 */
class TestUser extends CakeTestModel {

	public $name = 'User';

	public $useTable = false;

	public $order = array('TestUser.name' => 'ASC');

	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'length' => null, 'collate' => null, 'comment' => ''),
		'login' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
		'published' => array('type' => 'boolean', 'null' => true, 'default' => false, 'length' => null, 'collate' => null, 'comment' => ''),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null, 'length' => null, 'collate' => null, 'comment' => ''),
		'email' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
		'avatar' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
		'weight' => array('type' => 'float', 'null' => false, 'default' => null, 'length' => null, 'collate' => null, 'comment' => ''),
		'heigth' => array('type' => 'float', 'null' => true, 'default' => null, 'length' => null, 'collate' => null, 'comment' => ''),
		'a_or_ab_only' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => null, 'collate' => null, 'comment' => ''),
	//	'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
	//	'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $validate = array(
		// Simple Rules
		// See http://book.cakephp.org/2.0/en/models/data-validation.html#simple-rules
		'login' => 'alphaNumeric',
		'email' => 'email',

		// One Rule Per Field
		// http://book.cakephp.org/2.0/en/models/data-validation.html#one-rule-per-field
		'id' => array(
			'rule' => 'numeric', // rule as string
			'precision' => 0,
			'required' => true, // create only one present rule
			'allowEmpty' => true,
		),
		'avatar' => array(
			'rule' => array('minLength', 8), // rule as array with argument
			'required' => true, // currently simply mapped to present rule
			'allowEmpty' => false,
		),
	);
}

/**
 * This model is used inside
 * BanchaRemotableBehaviorTest::testGetValidations
 *
 * @package       Bancha.Test.Case.Model
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.0.0
 */
class TestUserCoreValidationRules extends TestUser {

	public $name = 'UserCoreValidationRules';

	public $validate = array(
		// Check all Core Validation rules
		'id' => array(
			'numeric' => array(
				'rule' => 'numeric'
			),
		),
		// Multiple Rules per Field
		'login' => array(
			'loginRule-1' => array( // this has to be checked by the server (so there's nothing onthe frontend for this)
				'rule' => array('isUnique'),
				'message' => "Login is already taken."
			),
			'loginRule-2' => array(
				'rule' => 'alphaNumeric',
				'required' => true,
				'message' => 'Alphabets and numbers only'
			),
			'between' => array(
				'rule' => array('between', 5, 15),
				'message' => 'Between 5 to 15 characters'
			)
		),
		'published' => array(
			'rule' => 'boolean',
		),
		'name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty')
			),
			'minLength' => array(
				'rule' => array('minLength', 3),
			),
			'maxLength' => array(
				'rule' => array('maxLength', 64),
			),
		),
		'email' => array(
			'rule' => array('email', true), // second argument (host resolutin) can only be checked on the server
			'message' => 'Please supply a valid email address.'
		),
		'avatar' => array(
			'file' => array( // this validation rule forces Bancha.scaffold in the frontend to render a fileuploadfield
				'rule' => array('file') // TODO Does this work?
			),
			'extension' => array(
				'rule' => array('extension', array('gif', 'jpeg', 'png', 'jpg')),
				'message' => 'Please supply a valid image.'
			),
		),
		'a_or_ab_only' => array(
			'rule' => array('inList', array('a', 'ab')),
			'message' => 'This file can only be "a" or "ab" or undefined.'
		),
	);
}

/**
 * TestingSaveArticleModel class
 *
 * @package       Bancha.Test.Case.Model
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 0.9.3
 */
class ArticleForTestingSaveBehavior extends CakeTestModel {

/**
 * useTable property
 *
 * @var bool false
 */
	public $useTable = false;

/**
 * name property
 *
 * @var string 'ArticleForTestingSaveBehavior'
 */
	public $name = 'ArticleForTestingSaveBehavior';

/**
 * schema property
 *
 * @var array
 * @access protected
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'collate' => null, 'key' => 'primary', 'collate' => null, 'comment' => '', 'length' => 8),
		'title' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => null, 'length' => 64, 'comment' => ''),
		'date' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
		'body' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => null, 'comment' => ''),
		'published' => array('type' => 'integer', 'null' => false, 'default' => 0, 'comment' => '', 'length' => 1),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => 0, 'comment' => '', 'length' => 8),
	);

/**
 * we are testing the bancha remotable behavior
 */
	public $actsAs = array('Bancha.BanchaRemotable');

/**
 * belongsTo associations
 * this article belongs to an author from the class below
 *
 * @var array
 */
	public $belongsTo = array('User');

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'title' => array(
			// standard validation rule
			'notEmpty' => array(
				'rule' => array('notEmpty')
			),
			// custom validation rule
			// we just need a custom function, so it is not allowed
			// to have more then two entries with the same name
			'limitDuplicates' => array(
				'rule'	=> array('customFunctionLimitDuplicates', 2),
				'message' => 'This name has been used too many times.'
			)
		),
	);

/**
 * Custom function to test bancha remotable behavior.
 *
 * @param array $check  Called by CakePHP, will get value
 *                      array('name' => 'some-value')
 * @param string $limit Called by CakePHP, will get value
 *                      25
 * @return boolean      True if valid
 */
	public function customFunctionLimitDuplicates($check, $limit) {
		$existingNames = $this->find('count', array(
			'conditions' => $check,
			'recursive' => -1
		));
		return $existingNames < $limit;
	}

}

/**
 * This model is used inside
 * BanchaRemotableBehaviorTest::testGetLastSaveResult_ValidationFailed
 *
 * @package       Bancha.Test.Case.Model
 * @author        Andrejs Semovs <andrejs.semovs@gmail.com>
 * @since         Bancha v 2.1.0
 */
class UserForTestingLastSaveResult extends CakeTestModel {

/**
 * name property
 *
 * @var string 'UserForTestingLastSaveResult'
 */
	public $name = 'UserForTestingLastSaveResult';

	public $useTable = false;

	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'length' => null, 'collate' => null, 'comment' => ''),
		'login' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => null, 'comment' => ''),
	);

	public $validate = array(
		'id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'precision' => 0
			),
		),
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => "Name is required."
			),
			'minLength' => array(
				'rule' => array('minLength', 3),
				'message' => "Name min. length is 3"
			),
			'maxLength' => array(
				'rule' => array('maxLength', 64),
			),
		),
		'login' => array(
			'isUnique' => array(
				'rule' => array('isUnique'),
				'message' => "Login is already taken."
			),
			'alphaNumeric' => array(
				'rule' => array('alphaNumeric'),
				'message' => "Login must be alphanumeric."
			),
		),
	);

/**
 * we are testing the bancha remotable behavior
 */
	public $actsAs = array('Bancha.BanchaRemotable');
}

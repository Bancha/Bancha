<?php
/**
 * BanchaRemotableBehaviorTest test models file.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha.Test.Case.Model
 * @category      tests
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */
class TestUser extends CakeTestModel {

/**
 * name property
 *
 * @var string 'User'
 * @access public
 */
	public $name = 'User';
	
	public $useTable = false;
	
	/** order property
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	
	public $order = array('name.order' => 'ASC');
	
	
	/**
 * schema property
 *
 * @var array
 * @access protected
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'length' => NULL, 'collate' => NULL, 'comment' => ''),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 64, 'collate' => NULL, 'comment' => ''),
		'login' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 64, 'collate' => NULL, 'comment' => ''),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL, 'length' => NULL, 'collate' => NULL, 'comment' => ''),
		'email' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 64, 'collate' => NULL, 'comment' => ''),
		'avatar' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 64, 'collate' => NULL, 'comment' => ''),
		'weight' => array('type' => 'float', 'null' => false, 'default' => NULL, 'length' => NULL, 'collate' => NULL, 'comment' => ''),
		'heigth' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => NULL, 'collate' => NULL, 'comment' => ''),
	//	'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
	//	'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	
	/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'alphanumeric' => array(
				'rule' => array('alphanumeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'login' => array(
			'alphanumeric' => array(
				'rule' => array('alphanumeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'email' => array(
			'email' => array(
				'rule' => array('email'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'avatar' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'weight' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'heigth' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
	
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

/**
 *
 * @package       Bancha.Test.Case.Model
 * @category      tests
 *
 */

class TestUserOrder extends CakeTestModel {
	public $name = 'User';
	public $useTable = false; //users
	public $order = array('name.order' => 'ASC');

/**
 * schema property
 *
 * @var array
 * @access protected
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'title' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
		'body' => array('type' => 'string', 'null' => '1', 'default' => '', 'length' => ''),
		'number' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'created' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
		'modified' => array('type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null)
	);
	
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
                'rule' => array('minLength',3),
            ),
            'maxLength' => array(
                'rule' => array('maxLength',64),
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
                'rule' => array('maxLength',64),
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
                'rule' => array('date'),
            ),
        ),
        'weight' => array(
            'numeric' => array(
                'rule' => array('decimal', 2)
            ),
        ),
        'height' => array(
            'numeric' => array(
                'rule' => array('numeric', 0),
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
                 'message' => 'Please supply a valid image.'
             ),
		),
	);
	
	
	/**
	 * Returns an array of table metadata (column names and types) for testing.
	 * $field => keys(type, null, default, key, length, extra)
	 *
	 * @param boolean|string $field Set to true to reload schema, or a string to return a specific field
	 * @return array Array of table metadata
	 */
	function schema($field = false) {
		if (is_string($field)) {
			if (isset($this->_schema[$field])) {
				return $this->_schema[$field];
			} else {
				return null;
			}
		}
		return $this->_schema;
	}
}
	
/**
 *
 * @package       Bancha.Test.Case.Model
 * @category      tests
 *
 */

class TestUserRelationships extends CakeTestModel {

/**
 * name property
 *
 * @var string 'User'
 * @access public
 */
	public $name = 'User';
	
/**
 * useTable property
 *
 * @var bool false
 * @access public
 */
	public $useTable = false;
	
	//public $hasOne = NULL;
	
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
	/**
 * schema property
 *
 * @var array
 * @access protected
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'title' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
		'body' => array('type' => 'string', 'null' => '1', 'default' => '', 'length' => ''),
		'number' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'created' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
		'modified' => array('type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null)
	);
	//public $hasMany = array('Device' => array('order' => array('Device.id' => 'ASC')));
}








/**
 * TestingSaveArticleModel class
 *
 * @package       Bancha.Test.Case.Model.Behavior
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
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'collate' => NULL, 'key' => 'primary', 'collate' => NULL, 'comment' => '', 'length'=>8),
		'title' => array('type' => 'string', 'null' => true, 'default' => NULL,'collate' => NULL,  'length' => 64, 'comment' => ''),
		'date' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
		'body' => array('type' => 'text', 'null' => true, 'default' => NULL,'collate' => NULL,  'comment' => ''),
		'published' => array('type' => 'integer', 'null' => false, 'default' => 0, 'comment' => '', 'length'=>1),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => 0, 'comment' => '', 'length'=>8),
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
			'notempty' => array(
				'rule' => array('notempty')
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
	 * custom function to test bancha remotable behavior
	 */
	function customFunctionLimitDuplicates($check, $limit) {
		// $check will have value: array('name' => 'some-value')
		// $limit will have value: 25
		$existing_names = $this->find('count', array(
			'conditions' => $check,
			'recursive' => -1
		));
		return $existing_names < $limit;
	}
}

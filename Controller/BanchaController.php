<?php

/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */


/**
 * Bancha Controller
 * This class exports the ExtJS API of all other Controllers for use in ExtJS Frontends
 *
 * @author Andreas Kern
 */

class BanchaController extends BanchaAppController {

	var $name = 'Bancha.Bancha'; //turns html on again
	var $autoRender = false; //we don't need a view for this
	var $autoLayout = false;
	//var $viewClass = 'Bancha.BanchaExt';
		

	/**
	 *  CRUD mapping between cakephp and extjs
	 * 	TODO check if the right arguments are passed
	 *
	 * @var array
	 */
	public $map = array(
			'index' => array('getAll', 0),
			'view' => array('read', 1),
			'add' => array('create', 1), 
			'edit' => array('update', 1), 
			'delete' => array('destroy', 1)
	);


	/**
	 * index method, sets $API for use in the view
	 *
	 * @return void
	 */
	public function index() {
		/**
		 * holds the ExtJS API array which is returned
		 *
		 * @var array
		 */

		$API = array();
		//$API['url'] =  'Bancha/router.php';
		$API['url'] =  '/bancha.php';
		$API['namespace'] = 'Bancha.RemoteStubs';
    	$API['type'] = "remoting";

		

		/****** parse Models **********/

		$models = App::objects('Model');
		$banchaModels = array();

		//load all Models and add those with BanchaBehavior to $banchaModels
		foreach ($models as $model) {
			$this->loadModel($model);
			if (is_array($this->{$model}->actsAs )) {
				if( in_array( 'Bancha', $this->{$model}->actsAs )) {
					array_push($banchaModels, $model);
				}
			}
		}
		
		//insert UID
		$API['metaData']['_UID'] = str_replace('.','',uniqid('', true));
		

		if(	in_array("all",$this->params['pass'] )) {
			$metaDataModels = $banchaModels;
		} else {
			$metaDataModels = $this->params['pass'];
		}
		
		//load the MetaData into $API
		foreach ($metaDataModels as $mod) {
			if(! in_array($mod, $banchaModels)) {
				throw new MissingModelException($mod);
			}
			$this->{$mod}->setBehaviorModel($mod);
			$API['metaData'][$mod] = $this->{$mod}->extractBanchaMetaData();
		}
		/**
		 * loop through the Controllers and adds the apropriate methods
		 * 
		 * TODO implement scaffolding;
		 */

		foreach($banchaModels as $cont) {
			$cont = Inflector::pluralize($cont);
			include(APP . DS . 'Controller' . DS . $cont . 'Controller.php');			
			$methods = get_class_methods($cont . 'Controller');;
			$cont = str_replace('Controller','',$cont);
			$cont = Inflector::singularize($cont);
			$API['actions'][$cont] = array();
			foreach( $this->map as $key => $value) {
				if (array_search($key, $methods) !== false) {
					array_push($API['actions'][$cont], array('name' => $value[0],'len' => $value[1]));					
				};
			}
		}

		$this->set('API', $API);
		print("Ext.ns('Bancha'); Bancha.REMOTE_API =" . json_encode($API));
		//$this->render(null, 'ajax', null); //removes the html
	}
	
	/**
	 * 
	 * this function returns the Metadata of the models passed as an argument
	 */
	
	public function loadMetaData($models = array() ) {
		if(isset($this->params['pass'])) {
			$models = $this->params['pass'];
		}
		if ($models == null) {
			return;
		}
		
		if ( is_string($models)) {
			$models = array($models);
		}
		$modelMetaData = array();
		foreach($models as $mod) {
			$mod =  Inflector::Singularize($mod);
			$mod = ucfirst($mod);
			$this->loadModel($mod);
			$this->{$mod}->setBehaviorModel($mod);
			$modelMetaData[$mod] = $this->{$mod}->extractBanchaMetaData();	
			
		}
		return $modelMetaData;
	}
}

?>

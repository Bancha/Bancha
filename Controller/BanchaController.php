<?php

/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Controller
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <florian@theroadtojoy.at>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

// todo this should not not be necessary
App::import('Controller', 'Bancha.BanchaApp');
App::uses('BanchaException', 'Bancha.Bancha/Exception');
App::uses('BanchaApi', 'Bancha.Bancha');

/**
 * Bancha Controller
 * This class exports the ExtJS API for remotable models and controller.
 * This is only internally used by the client side of Bancha.
 *
 * @package    Bancha
 * @subpackage Controller
 * @author     Andreas Kern <andreas.kern@gmail.com>
 * @author     Florian Eckerstorfer <florian@theroadtojoy.at>
 * @author     Roland Schuetz <mail@rolandschuetz.at>
 */
class BanchaController extends BanchaAppController {

	public $name = 'Bancha';
	public $autoRender = false; //we don't need a view for this
	public $autoLayout = false;
	
	// enable auth component if configured
	public function __construct($request = null, $response = null) {
		if(is_array(Configure::read('Bancha.Api.AuthConfig'))) {
			$this->components['Auth'] = Configure::read('Bancha.Api.AuthConfig');
		}
		parent::__construct($request, $response);
	}
	
	/**
	 * the index method is called by default by cakePHP if no action is specified,
	 * it will print the API for the Controllers which have the Bancha-
	 * Behavior set. This will not include any model meta data. to specify which
	 * model meta data should be printed you will have to pass the model name or 'all'
	 * For more see [how to adopt the layout](https://github.com/Bancha/Bancha/wiki/Installation)
	 *
	 * @param string $metadataFilter Models that should be exposed through the Bancha API. Either all or [all] for
	 *                                  all models or a comma separated list of models.
	 * @return void
	 */
	public function index($metadataFilter='') {
		$metadataFilter = urldecode($metadataFilter);
		$banchaApi = new BanchaApi();
		
		// send as javascript
		$this->response->type('js');
		
		$remotableModels = $banchaApi->getRemotableModels();
        $metadataModels = $banchaApi->filterRemotableModels($remotableModels, $metadataFilter);
		
		$api = array(
			'url'		=> $this->request->webroot.'bancha.php',
			'namespace'	=> Configure::read('Bancha.Api.stubsNamespace'),
    		'type'		=> 'remoting',
    		'metadata'	=> $banchaApi->getMetadata($metadataModels),
    		'actions'	=> array_merge_recursive(
				$banchaApi->getRemotableModelActions($remotableModels),
				$banchaApi->getRemotableMethods(),
				array('Bancha' => array(
					array(
						'name'	=> 'loadMetaData',
						'len'	=> 1,
					),
				))
			)
		);
		
		$remoteApiNamespace = Configure::read('Bancha.remote_api');
		if(empty($remoteApiNamespace)) {
			$remoteApiNamespace = 'Bancha.REMOTE_API';
		}

		$this->response->body(sprintf("Ext.ns('Bancha');\n%s=%s", $remoteApiNamespace, json_encode($api)));
	}

	/**
	 * loadMetaData returns the Metadata of the models passed as an argument or 
	 * in params['pass'] array which is created by cakephp from the arguments 
	 * passed in the url. e.g.: http://localhost/Bancha/loadMetaData/User/Tag 
	 * will load the metadata from the models Users and Tags
	 * 
	 * @return array 
	 */
	public function loadMetaData() {
		$models = array();
		if(isset($this->params['data'][0])) {
			$models = $this->params['data'][0];
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

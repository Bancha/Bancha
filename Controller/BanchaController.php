<?php

/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @subpackage    Controller
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <florian@theroadtojoy.at>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

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
	
	/**
	 * The index method is called by default by cakePHP if no action is specified,
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
		
		// send an _CakeError property to the frontend
		$error = false;

		// get all possible remotable models
		$remotableModels = $this->getRemotableModels($banchaApi);
		
		//get all the remotable model actions, this can throw an error on missconfiguration
		$remotableModelsActions = array();
		try {
			$remotableModelsActions = $banchaApi->getRemotableModelActions($remotableModels);
		} catch(MissingControllerException $e) {
			$error  = 'You have exposed a model with BanchaRemotable, so Bancha requires the corresponding controller to exist.<br />';
			$error .= '<br />Bancha looks at this controller to see which CRUD functions should be exposed. <b>But the '.$e->getMessage().'</b>';
			$error .= '<br />Please create this controller!';
		}

		// build actions
		if(($actions = Cache::read('actions', '_bancha_api_')) === false) {
			$actions = array_merge_recursive(
				$remotableModelsActions,
				$banchaApi->getRemotableMethods(),
				// plugin folders are not searched, so all out exposed functions manually
				array('Bancha' => array(
					array(
						'name'	=> 'loadMetaData',
						'len'	=> 1,
					),
					array(
						'name'	=> 'logError',
						'len'	=> 1,
					),
				))
			);
			
			// cache for future requests
			Cache::write('actions', $actions, '_bancha_api_');
		}
		
		$api = array(
			'url'		=> $this->request->webroot.'bancha-dispatcher.php',
			'namespace'	=> Configure::read('Bancha.Api.stubsNamespace'),
			'type'		=> 'remoting',
			'metadata'	=> array_merge(
								$this->getMetadata($banchaApi,$remotableModels, $metadataFilter),
								array('_CakeError' => Configure::read('debug')==0 ? !!$error : $error)), // send the text only in debug mode
			'actions'	=> $actions
		);
		
		// no extra view file needed, simply output
		$this->response->body(sprintf("Ext.ns('Bancha');\n%s=%s", Configure::read('Bancha.Api.remoteApiNamespace'), json_encode($api)));
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
		$models = isset($this->params['data'][0]) ? $this->params['data'][0] : null;
		if ($models == null) {
			return;
		}
		
		// get the result
		$banchaApi = new BanchaApi();
		return $this->getMetaData(new BanchaApi(), $this->getRemotableModels($banchaApi), $models);
	}
	
	
	/**
	 * This function decorates the BanchaApi::getRemotableModels() method with caching
	 * @return see BanchaApi::getRemotableModels
	 */
	private function getRemotableModels($banchaApi) {
		if(($remotableModels = Cache::read('remotable_models', '_bancha_api_')) !== false) {
			return $remotableModels;
		}
		
		// get remotable models (iterates through all models)
		$remotableModels = $banchaApi->getRemotableModels();
		Cache::write('remotable_models', $remotableModels, '_bancha_api_');
		
		return $remotableModels;
	}
	
	/**
	 * This function decorates the BanchaApi::getMetadata() method with caching
	 * @return see BanchaApi::getMetadata
	 */
	private function getMetaData($banchaApi, $remotableModels, $metadataFilter) {
		// filter the models (performant function)
		$metadataModels = $banchaApi->filterRemotableModels($remotableModels, $metadataFilter);
		
		// build a caching key, make sure we are always using the right models
		$cacheKey = 'metadata_'.md5(implode(",", $metadataModels)); // md5 for shorter file names
		
		// check cache
		if(($metadata = Cache::read($cacheKey, '_bancha_api_')) !== false) {
			return $metadata;
		}
		
		// execute unperformant request
		$metadata = $banchaApi->getMetadata($metadataModels);
		
		// cache for next time
		Cache::write($cacheKey, $metadata, '_bancha_api_');
		
		return $metadata;
	}
	
	/**
	 * This function returns all translations, known to cakephp in the defiend language
	 * The default domain is 'bancha', but can be overwritten
	 */
	public function translations($languageCode, $domain='bancha') {

		App::uses('I18n', 'I18n');
		$i18n = I18n::getInstance();

		// force cake to load the correct language file
		$i18n->translate('whatever', 'whatever', $domain, false, 1, $languageCode);

		// get the translations
		$domains = $i18n->domains();
		$translations = $domains[$domain][$languageCode]['LC_MESSAGES'];

		// transform
		$jsTranslations = array();
		foreach($translations as $key=>$value) {
			array_push($jsTranslations, array('key'=>$key,'value'=>$value));
		}

		// no extra view file needed, simply output
		$this->response->body(json_encode($jsTranslations));
    }

	/**
	 * This function logs an javascript error to the js_error.log
	 */
	public function logError($error) {
		$this->log($error,'js_error');
		return true;
	}
}

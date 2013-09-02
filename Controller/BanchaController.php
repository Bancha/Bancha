<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Controller
 * @copyright     Copyright 2011-2013 codeQ e.U.
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
 * @package       Bancha.Controller
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Florian Eckerstorfer <florian@theroadtojoy.at>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
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
	 * For more see [how to adopt the layout](http://docs.banchaproject.org/resources/Installation.html#setting-up-extjs)
	 *
	 * @param string $metadataFilter Model metadata that should be exposed through the Bancha API. Either 'all' or '[all]'
	 *                               to get the metadata for all models or a comma separated list of models like
	 *                               '[User,Article]'.
	 * @param string $schema         Possible Values: false (default), 'development', 'packaged'
	 *                               If set to false, the default Ext.Direct definition will be output.
	 *                               In development we use the Ext.Loader for loading dependencies including the Remote API. In that
	 *                               case (/bancha-api-class.js) $schema will be set to 'development'.
	 *                               For packaging, Sencha CMD is used. Sencha CMD expects explicit define statements, these will be
	 *                               added when $schema is set to 'packaged' (/bancha-api-packaged.js)
	 * @return void
	 */
	public function index($metadataFilter='', $schema=false) {
		$metadataFilter = urldecode($metadataFilter);
		$banchaApi = new BanchaApi();

		// send as javascript
		$this->response->type('js');

		// send an _ServerError property to the frontend
		$error = false;

		//<bancha-pro>
		// get all possible remotable models
		$remotableModels = $this->getRemotableModels($banchaApi);
		//</bancha-pro>
		//<bancha-basic>
		if(Configure::read('Bancha.isPro')==false) {
			$remotableModels = array();
		}
		//</bancha-basic>

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
		if(($actions = Cache::read('actions_'.Configure::read('debug'), '_bancha_api_')) === false) {
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
						'len'	=> 2,
					),
				))
			);

			// cache for future requests
			Cache::write('actions_'.Configure::read('debug'), $actions, '_bancha_api_');
		}

		$url = (Configure::read('Bancha.Api.domain')==null) ? '' : Configure::read('Bancha.Api.domain');
		$api = array(
			'url'		=> $url.$this->request->webroot.'bancha-dispatcher.php',
			'namespace'	=> Configure::read('Bancha.Api.stubsNamespace'),
			'type'		=> 'remoting',
			'metadata'	=> array_merge(
								$this->getMetadata($banchaApi, $remotableModels, $metadataFilter),
								array('_ServerError' => Configure::read('debug')==0 ? !!$error : $error)), // send the text only in debug mode
			'actions'	=> $actions
		);

		// no extra view file needed, simply output
		$result = '';

		// Add the remote api
		// Just to keep in mind:
		// Using json_encode will quote the object keys.
		// If you are using Sencha CMD (which uses the Google Closure Compiler) this is
		// important, because we refer to the Bancha.loadMetaData function by name and so
		// the advanced mdoe renaming would rename the method, but not the string reference
		// except we quote the key.
		// For a detailed description see https://developers.google.com/closure/compiler/docs/limitations
		// under "Using string names to refer to object properties"
		if($schema == false) {
			$api = json_encode($api);
			$api = Configure::read('debug')==2 ? $this->beautifyJson($api) : $api;
			$result = sprintf("Ext.ns('Bancha');\n%s=%s", Configure::read('Bancha.Api.remoteApiNamespace'), $api);
		} else {
			$api['singleton'] = true; // the api is also our class registry, so set the class to singleton
			$api = json_encode($api);
			$api = Configure::read('debug')==2 ? $this->beautifyJson($api) : $api;
			$result = sprintf("Ext.define('%s',%s);", Configure::read('Bancha.Api.remoteApiNamespace'), $api);
		}

		if($schema === 'packaged') {
			// add the class definitions
			$result .= "\n\n";
			foreach($banchaApi->filterRemotableModels($remotableModels, $metadataFilter) as $modelName) {
				$result .= sprintf(
					"Ext.define('Bancha.model.%s', {\n".
					"    extend: 'Bancha.data.Model'\n".
					"});\n", $modelName);
			}
		}

		$this->response->body($result);
	}

	/**
	 * @access private
	 * loadMetaData returns the Metadata of the models passed.
	 *
	 * Ext.Direct will pass them in params['data'], Ext.Ajax in params['pass'].
	 *
	 * E.g. for Ext.Ajax: http://localhost/bancha-load-metadata/[User,Article].js
	 * will load the metadata from the models Users and Articles.
	 *
	 * This function is only used by Bancha internally for dependency resolution
	 * in the Bancha.loader.Models and in Ext#onModelReady. Internally this is
	 * triggered from Bancha#loadModelMetaData.
	 *
	 * @return void
	 */
	public function loadMetaData() {
		$models = null;
		if(isset($this->params['data'][0])) { $models = $this->params['data'][0]; } //for Ext.Direct
		if(isset($this->params['pass'][0])) { $models = $this->params['pass'][0]; } //sync request
		if ($models == null) {
			return false;
		}

		try {
			// get the result
			$banchaApi = new BanchaApi();
			$result = $this->getMetaData(new BanchaApi(), $this->getRemotableModels($banchaApi), $models);

			// support both direct ajax requests and Bancha requests
			if($this->params['isBancha']) {
				return $result;
			} else {
				$result = json_encode($result);
				$result = Configure::read('debug')==2 ? $this->beautifyJson($result) : $result;
				$this->response->body($result);
			}
		} catch(MissingModelException $e) {
			// in the case of an error return with false, but don't throw an exception
			// So the Bancha class loader can handle the error handling

			// support both direct ajax requests and Bancha requests
			if($this->params['isBancha']) {
				return array(
					'success' => false,
					'message' => $e->getMessage()
				);
			} else {
				// for Ajax
				throw $e;
			}
		}
	}



	/**
	 * This function decorates the BanchaApi::getRemotableModels() method with caching
	 * @return see BanchaApi::getRemotableModels
	 */
	private function getRemotableModels($banchaApi) {
		if(($remotableModels = Cache::read('remotable_models_'.Configure::read('debug'), '_bancha_api_')) !== false) {
			return $remotableModels;
		}

		// get remotable models (iterates through all models)
		$remotableModels = $banchaApi->getRemotableModels();
		Cache::write('remotable_models_'.Configure::read('debug'), $remotableModels, '_bancha_api_');

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
		$cacheKey = 'metadata_'.md5(implode(",", $metadataModels)).'_'.Configure::read('debug'); // md5 for shorter file names

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
	 * Indents a flat JSON string to make it more human-readable.
	 *
	 * http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
	 *
	 * @param string $json The original JSON string to process.
	 *
	 * @return string Indented version of the original JSON string.
	 */
	function beautifyJson($json) {

	    $result      = '';
	    $pos         = 0;
	    $strLen      = strlen($json);
	    $indentStr   = '  ';
	    $newLine     = "\n";
	    $prevChar    = '';
	    $outOfQuotes = true;

	    for ($i=0; $i<=$strLen; $i++) {

	        // Grab the next character in the string.
	        $char = substr($json, $i, 1);

	        // Are we inside a quoted string?
	        if ($char == '"' && $prevChar != '\\') {
	            $outOfQuotes = !$outOfQuotes;

	        // If this character is the end of an element,
	        // output a new line and indent the next line.
	        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
	            $result .= $newLine;
	            $pos --;
	            for ($j=0; $j<$pos; $j++) {
	                $result .= $indentStr;
	            }
	        }

	        // Add the character to the result string.
	        $result .= $char;

	        // If the last character was the beginning of an element,
	        // output a new line and indent the next line.
	        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
	            $result .= $newLine;
	            if ($char == '{' || $char == '[') {
	                $pos ++;
	            }

	            for ($j = 0; $j < $pos; $j++) {
	                $result .= $indentStr;
	            }
	        }

	        $prevChar = $char;
	    }

	    return $result;
	}

	/**
	 * This function returns all translations in the given domain, which are known to cakephp for
	 * the defined language. By default the domain bancha is used for all front-side translatable
	 * strings (see also the jsi18n shell tool). When Bancha needs to translate a string for the
	 * first time in the frontend it uses this method to load all translations.
	 *
	 * @param  string $languageCode three-letter language code, see CakePHP language codes
	 * @param  string $domain       The used domain, default is 'bancha'
	 * @return void                 No return value, the response body is set to an json object
	 *                              with all data.
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
		$output = json_encode($jsTranslations);
		$output = Configure::read('debug')==2 ? $this->beautifyJson($output) : $output;
		$this->response->body($output);
    }

    /**
     * This function logs an javascript error to eigther js_error.log or
     * missing_translation.log. This function should never be called directly.
     * use the JavaScript Bancha.log method to log errors.
	 *
     * @param  string $error the error message to log
     * @param  string $type  'js_error' or 'missing_translation'
     * @return boolean		 True to indicate that everything worked.
     */
	public function logError($error, $type) {
		if($type!=='js_error' && $type!=='missing_translation') {
			$this->log(
				'Someone send a javascript error message of type "'.$type.'" to CakePHP, but this type does not exist. '.
				'If you did this, please never use the serverside Bancha::logError directly. Instead use the JavaScript '.
				'function Bancha.log');
			return false;
		} else {
			// log error to corresponding log file
			$this->log($error, $type);
			return true;
		}
	}
}

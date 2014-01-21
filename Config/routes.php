<?php
/**
 * Routes configuration
 *
 * This file configures Banchas routing.
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Config
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */


/**
 * Enable support for the file extension js
 *
 * In CakePHP 2.2 and above Router:setExtensions could be used,
 * for legacy support we need the bug fix version below
 */
if (Router::extensions() !== true) { // if all extensions are supported we are done

	// add json and javascript to the extensions
	$extensions = Router::extensions();
	if (!is_array($extensions)) {
		$extensions = array('json', 'js');
	} else {
		array_push($extensions, 'json');
		array_push($extensions, 'js');
	}

	call_user_func_array('Router::parseExtensions', $extensions);
}


/**
 * connect the remote api
 */
Router::connect('/bancha-api',										array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'index'));
Router::connect('/bancha-api/models/:metaDataForModels',			array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'index'), array('pass' => array('metaDataForModels')));
Router::connect('/bancha-api-class',								array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'index', '', 'development'), array('pass' => array('metaDataForModels')));
Router::connect('/bancha-api-class/models/:metaDataForModels',		array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'index', 'development'), array('pass' => array('metaDataForModels')));
Router::connect('/bancha-api-packaged',								array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'index', '', 'packaged'), array('pass' => array('metaDataForModels')));
Router::connect('/bancha-api-packaged/models/:metaDataForModels',	array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'index', 'packaged'), array('pass' => array('metaDataForModels')));

/**
 * connect ajax metadata loading
 */
Router::connect('/bancha-load-metadata/:metaDataForModels',			array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'loadMetaData'), array('pass' => array('metaDataForModels')));

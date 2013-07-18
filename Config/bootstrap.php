<?php
/**
 * This file is loaded automatically by the cake for both Bancha and standard cake requests
 *
 * This file should load/create any application wide configuration settings.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Config
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.3
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

// don't make "Banchas", keep it Bancha
Inflector::rules('plural', array('/^Bancha$/i' => 'Bancha'));

// Bancha version
Configure::write('Bancha.version','PRECOMPILER_ADD_RELEASE_VERSION');

/**
 * If this config is set to true all exceptions which are send via Bancha
 * are written to the CakePHP error logs.
 * 
 * To change this please override it in your core.php
 */
Configure::write('Bancha.logExceptions',true);

// config defaults
Configure::write('Bancha.Api.AuthConfig',false);
Configure::write('Bancha.Api.stubsNamespace','Bancha.RemoteStubs');
Configure::write('Bancha.Api.remoteApiNamespace','Bancha.REMOTE_API');

Configure::write('Bancha.allowMultiRecordRequests',false);

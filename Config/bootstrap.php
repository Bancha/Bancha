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

/**
 * This is the current Bancha release version.
 */
Configure::write('Bancha.version', 'PRECOMPILER_ADD_RELEASE_VERSION');

/**
 * If you want to use exposed controller methods to a different namespace
 * then 'Bancha.RemoteStubs', you can override this in your core.php.  
 * 
 * Normally there is no reason to do this.
 */
if(Configure::read('Bancha.Api.stubsNamespace') === null) { // conditionals are needed because of loading order
	Configure::write('Bancha.Api.stubsNamespace', 'Bancha.RemoteStubs');
}

/**
 * This is the namespace for the Ext.Direct Remote API.  
 * 
 * Default is 'Bancha_REMOTE_API', you can override this in your core.php.
 * If you change this property you also need to change the JavaScript  
 * 'Bancha.remoteApi' property. There should be no reason to do this!  
 *
 * If you feel like changing this, please write us an email before to
 * support@banchaproject.org, you're probably doing something wrong.
 */
if(Configure::read('Bancha.Api.remoteApiNamespace') === null) {
	Configure::write('Bancha.Api.remoteApiNamespace', 'Bancha.REMOTE_API');
}
/**
 * There is no known reason to every enable this, please think twice before 
 * enabling it, since it maybe makes it very hard for you to find errors.  
 * 
 * If you feel like changing this, please write us an email before to
 * support@banchaproject.org, you're probably doing something wrong.  
 * 
 * If you want to send multiple multiple records from ExtJS to CakePHP in one 
 * action (this is not about request batching!), you have to enable this. 
 * Normally this is not needed and the according error is only triggered 
 * because the ExtJS store proxy is configured with batchActions:true.  
 * 
 * Please never batch records on the proxy level (Ext.Direct is batching them). 
 */
if(Configure::read('Bancha.allowMultiRecordRequests') === null) {
	Configure::write('Bancha.allowMultiRecordRequests', false);
}

/**
 * If this config is set to true all exceptions which are send via Bancha
 * are written to the CakePHP error logs.  
 * 
 * To change disable it, please override it in your core.php
 */
if(Configure::read('Bancha.logExceptions') === null) {
	Configure::write('Bancha.logExceptions', true);
}

/**
 * To find bugs more easily and fix them fast, if this feature is activated,
 * Bancha provides exceptions to the Bancha core team, including environment 
 * informations like the PHP and CakePHP version, but without any data.  
 * 
 * To disable it, please override it in your core.php
 */
if(Configure::read('Bancha.ServerLogger.logIssues') === null) {
	Configure::write('Bancha.ServerLogger.logIssues', true);
}

/**
 * To get a better idea what server environments are the most important
 * to test and when features we should implement next, if this feature
 * is activated, Bancha will provide usage information to the Bancha 
 * core team, including environment informations like the PHP and CakePHP 
 * version, but without any data.  
 * 
 * To disable it, please override it in your core.php
 */
if(Configure::read('Bancha.ServerLogger.logEnvironment') === null) {
	Configure::write('Bancha.ServerLogger.logEnvironment', true);
}

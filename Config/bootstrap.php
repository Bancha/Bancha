<?php
/**
 * This file is loaded automatically by CakePHP for both Bancha and standard
 * CakePHP requests.
 *
 * This file should load/create any application wide configuration settings.
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Config
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
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
 * By default the Ext JS or Sencha Touch web app is on the same domain
 * as CakePHP. This is enforced in browsers by the Same-Origin-Policy.
 *
 * If you are packaging your application for mobile devices, you will
 * need to set the full path to the CakePHP application. So if you are
 * building a mobile app please set this config in your core.php.
 *
 * Please also set the Bancha.allowedDomains config from below.
 *
 * See also http://bancha.io/documentation-pro-cross-domain-requests.html
 */
//Configure::write('Bancha.Api.domain', 'http://example.org');

/**
 * If this should be available from different domains, please define
 * either the string '*' or an array of domains including the protocol.
 *
 * You can set this by adding the following to your core.php:
 *
 *       Configure::write('Bancha.allowedDomains', array(
 *           'http://trusted-domain-one.org',
 *           'http://trusted-domain-two.org',
 *       ));
 *
 * See also http://bancha.io/documentation-pro-cross-domain-requests.html
 */
if (Configure::read('Bancha.allowedDomains') === null) { // conditionals are needed because of loading order
	Configure::write('Bancha.allowedDomains', false);
}

/**
 * Most exceptions thrown from a controller action are not expected to happen
 * and to give away to much information to possible hackers only that an 
 * exception happend is send to the client, but not further information.
 *
 * Some exceptions are thought to be handled in the user interface, e.g. a
 * login exception, these can be defined here. For these exceptions Bancha
 * will send the type and the message to the user.
 *
 * An example for handling authentification and wuthorization errors in the
 * frontend:
 * 
 *       Configure::write('Bancha.passExceptions', array(
 *           'MyFrontendException',
 *           'BanchaAuthLoginException',
 *           'BanchaAuthAccessRightsException'
 *       ));
 */
if (Configure::read('Bancha.passExceptions') === null) { // conditionals are needed because of loading order
	Configure::write('Bancha.passExceptions', array(
		'BanchaAuthLoginException',
		'BanchaAuthAccessRightsException'
	));
}

/**
 * If you want to use exposed controller methods to a different namespace
 * then 'Bancha.RemoteStubs', you can override this in your core.php.
 *
 * Normally there is no reason to do this.
 */
if (Configure::read('Bancha.Api.stubsNamespace') === null) { // conditionals are needed because of loading order
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
if (Configure::read('Bancha.Api.remoteApiNamespace') === null) {
	Configure::write('Bancha.Api.remoteApiNamespace', 'Bancha.REMOTE_API');
}
/**
 * There is no known reason to every enable this, please think twice before
 * enabling it, since it maybe makes it very hard for you to find errors.
 *
 * If you feel like changing this, please write us an email before to
 * support@banchaproject.org, you're probably doing something wrong.
 *
 * If you want to send multiple multiple records from Ext JS to CakePHP in one
 * action (this is not about request batching!), you have to enable this.
 * Normally this is not needed and the according error is only triggered
 * because the Ext JS store proxy is configured with batchActions:true.
 *
 * Please never batch records on the proxy level (Ext.Direct is batching them).
 */
if (Configure::read('Bancha.allowMultiRecordRequests') === null) {
	Configure::write('Bancha.allowMultiRecordRequests', false);
}

/**
 * If this config is set to true all exceptions which are send via Bancha
 * are written to the CakePHP error logs.
 *
 * To change disable it, please override it in your core.php
 */
if (Configure::read('Bancha.logExceptions') === null) {
	Configure::write('Bancha.logExceptions', true);
}

/**
 * To find bugs more easily and fix them fast, if this feature is activated,
 * Bancha provides exceptions to the Bancha core team, including environment
 * informations like the PHP and CakePHP version, but without any data.
 *
 * To disable it, please override it in your core.php
 */
if (Configure::read('Bancha.ServerLogger.logIssues') === null) {
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
if (Configure::read('Bancha.ServerLogger.logEnvironment') === null) {
	Configure::write('Bancha.ServerLogger.logEnvironment', true);
}

/**
 * This flag is true on pro versions, for feature detection.
 * Setting this to true in non-Pro versions, will result in errors,
 * since some files and js is missing. Do not change this manually.
 */
//<bancha-pro>
if (Configure::read('Bancha.isPro') === null) {
	Configure::write('Bancha.isPro', true);
}
//</bancha-pro>
//<bancha-basic>
if (Configure::read('Bancha.isPro') === null) {
	Configure::write('Bancha.isPro', false);
}
//</bancha-basic>

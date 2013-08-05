<?php
/**
 * Bancha Front Controller
 *
 * The Front Controller for handling every request from Sencha Touch or ExtJS.
 * This file should be copied to the app/webroot directory.
 *
 * This is modified version of CakePHPs webroot/index.php
 * Supports CakePHP 2.0.0 till latest
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Bancha : Ext JS and Cake PHP (http://banchaproject.org)
 * Copyright 2005-2013 Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @copyright	  Copyright 2011-2013 codeQ e.U.
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @link          http://cakephp.org CakePHP(tm) Project
 * @link 		  http://banchaproject.org Bancha Project
 * @package       Bancha
 * @subpackage    Bancha._app.webroot
 * @since         Bancha v 0.9.1
 */



/**
 * this is for the check setup page
 */
if(isset($_GET['setup-check']) && $_GET['setup-check']) {
	// send as javascript
	header('Content-type: text/javascript');
	exit('{"BanchaDispatcherIsSetup":true}');
}

/**
 * Use the DS to separate the directories in other defines
 */
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
/**
 * These defines should only be edited if you have cake installed in
 * a directory layout other than the way it is distributed.
 * When using custom settings be sure to use the DS and do not add a trailing DS.
 */

/**
 * The full path to the directory which holds "app", WITHOUT a trailing DS.
 *
 */
if (!defined('ROOT')) {
	define('ROOT', dirname(dirname(dirname(__FILE__))));
}

/**
 * The actual directory name for the "app".
 *
 */
if (!defined('APP_DIR')) {
	define('APP_DIR', basename(dirname(dirname(__FILE__))));
}
/**
 * The absolute path to the "cake" directory, WITHOUT a trailing DS.
 *
 * Un-comment this line to specify a fixed path to CakePHP.
 * This should point at the directory containing `Cake`.
 *
 * For ease of development CakePHP uses PHP's include_path.  If you
 * cannot modify your include_path set this value.
 *
 * Leaving this constant undefined will result in it being defined in Cake/bootstrap.php
 */
	//define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'lib');

/**
 * Editing below this line should NOT be necessary.
 * Change at your own risk.
 *
 */
if (!defined('WEBROOT_DIR')) {
	define('WEBROOT_DIR', basename(dirname(__FILE__)));
}
if (!defined('WWW_ROOT')) {
	define('WWW_ROOT', dirname(__FILE__) . DS);
}

// for built-in server (added in CakePHP 2.3)
if (php_sapi_name() == 'cli-server') {
	if ($_SERVER['REQUEST_URI'] !== '/' && file_exists(WWW_ROOT . $_SERVER['REQUEST_URI'])) {
		return false;
	}
	$_SERVER['PHP_SELF'] = '/' . basename(__FILE__);
}

if (!defined('CAKE_CORE_INCLUDE_PATH')) {
	if (function_exists('ini_set')) {
		ini_set('include_path', ROOT . DS . 'lib' . PATH_SEPARATOR . ini_get('include_path'));
	}
	if (!include ('Cake' . DS . 'bootstrap.php')) {
		$failed = true;
	}
} else {
	if (!include (CAKE_CORE_INCLUDE_PATH . DS . 'Cake' . DS . 'bootstrap.php')) {
		$failed = true;
	}
}
if (!empty($failed)) {
	trigger_error("CakePHP core could not be found.  Check the value of CAKE_CORE_INCLUDE_PATH in APP/webroot/bancha-dispatcher.php.  It should point to the directory containing your " . DS . "cake core directory and your " . DS . "vendors root directory.", E_USER_ERROR);
}

/**
 * On top of the normal boostrap Bancha will also load it's own bootstrap
 */
// first we need to find where Bancha is located
if (file_exists(ROOT . DS . 'plugins' . DS . 'Bancha' . DS . 'Config' . DS . 'bancha-dispatcher-bootstrap.php')) {
	// Bancha is in the general plugins folder
	if(!include(ROOT . DS . 'plugins' . DS . 'Bancha' . DS . 'Config' . DS . 'bancha-dispatcher-bootstrap.php')) {
		$failed = true;
	}
} else if (file_exists(ROOT . DS . APP_DIR . DS . 'Plugin' . DS . 'Bancha' . DS . 'Config' . DS . 'bancha-dispatcher-bootstrap.php')) {
	// Bancha is in the app Plugin folder
	if(!include(ROOT . DS . APP_DIR . DS . 'Plugin' . DS . 'Bancha' . DS . 'Config' . DS . 'bancha-dispatcher-bootstrap.php')) {
		$failed = true;
	}
}
if (!empty($failed)) {
	trigger_error("Bancha bootstrap could not be found. Check the value of ROOT or APP_DIR in APP/webroot/bancha-dispatcher.php.  It should point to the directory containing your " . DS . " ROOT or APP_DIR directory.", E_USER_ERROR);
}

// load the dispatcher and request colletion
App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

// this shouldn't be necessary, but sometime it is.. not sure why
App::import('Controller', 'Bancha.Bancha');

$Dispatcher = new BanchaDispatcher();
$raw_post_data = file_get_contents("php://input");
$Dispatcher->dispatch(
	new BanchaRequestCollection(
		$raw_post_data  ? $raw_post_data : '',
		isset($_POST) ? $_POST : array()
	),
	new CakeResponse()
);

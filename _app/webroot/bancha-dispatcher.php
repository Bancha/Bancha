<?php
/**
 * Index
 *
 * The Front Controller for handling every request
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Bancha : Ext JS and Cake PHP (http://banchaproject.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Copyright 2011-2012, StudioQ OG
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @copyright	  Copyright 2011-2012 StudioQ OG
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @link          http://cakephp.org CakePHP(tm) Project
 * @link 		  http://banchaproject.org Bancha Project
 * @package       Bancha
 * @subpackage    Bancha.app.webroot
 * @since         CakePHP(tm) v 0.2.9
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
 */
	if (!defined('CAKE_CORE_INCLUDE_PATH')) {
		define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'lib');
	}

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
	if (!defined('CORE_PATH')) {
		/*if (function_exists('ini_set') && ini_set('include_path', CAKE_CORE_INCLUDE_PATH . PATH_SEPARATOR . ROOT . DS . APP_DIR . DS . PATH_SEPARATOR . ini_get('include_path'))) {
			define('APP_PATH', null);
			define('CORE_PATH', null);
		} else {*/
			define('APP_PATH', ROOT . DS . APP_DIR . DS);
			define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
		//}
	}
	if (!include(CORE_PATH . 'Cake' . DS . 'bootstrap.php')) {
		trigger_error("CakePHP core could not be found.  Check the value of CAKE_CORE_INCLUDE_PATH in APP/webroot/index.php.  It should point to the directory containing your " . DS . "cake core directory and your " . DS . "vendors root directory.", E_USER_ERROR);
	}
	
	// load bootstrap of bancha after cake and app bootstrap is loaded above
	$failedLoadingBootstrap = true;
	if (file_exists(ROOT . DS . 'plugins' . DS . 'Bancha' . DS . 'Config' . DS . 'bancha-dispatcher-bootstrap.php')) {
		// Bancha is in the general plugins folder
		$failedLoadingBootstrap = !include(ROOT . DS . 'plugins' . DS . 'Bancha' . DS . 'Config' . DS . 'bancha-dispatcher-bootstrap.php');
	} else if (file_exists(APP_PATH . 'Plugin' . DS . 'Bancha' . DS . 'Config' . DS . 'bancha-dispatcher-bootstrap.php')) {
		// Bancha is in the app Plugin folder
		$failedLoadingBootstrap = !include(APP_PATH . 'Plugin' . DS . 'Bancha' . DS . 'Config' . DS . 'bancha-dispatcher-bootstrap.php');
	}
	if ($failedLoadingBootstrap) {
		trigger_error("Bancha bootstrap could not be loaded.  
		Check the value of ROOT in APP/webroot/index.php.  
		It should point to the directory containing your " . DS . " ROOT directory and your " . DS . "vendors root directory.", E_USER_ERROR);
	}
	
	
	if (isset($_GET['url']) && $_GET['url'] === 'favicon.ico') {
		return;
	} else {
		$Dispatcher = new BanchaDispatcher();
		$raw_post_data = file_get_contents("php://input");
		$Dispatcher->dispatch(new BanchaRequestCollection(
			$raw_post_data  ? $raw_post_data : '',
			isset($_POST) ? $_POST : array()
		));
	}

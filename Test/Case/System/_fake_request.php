<?php
/**
 * This script is used by the ConsistentModelTest to simulate executing multiple HTTP requests in parallel.
 *
 * This script can be called using:
 * php _fake_request.php client_id article_id tid title sleep_time
 *
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * @package       Bancha.Test.Case.System
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

// prepare constants, like in index.php
define('DS', DIRECTORY_SEPARATOR);

// Bancha plugin can be inside the app folder, or outside
$pathToBancha = realpath(dirname(__FILE__) . '/../../..'); // Bancha/Test/Case/System/_fake_request.php
if (basename(realpath($pathToBancha . '/..')) == 'Plugin') {
	// app/Plugin/Bancha
	define('ROOT', realpath($pathToBancha . '/../../..'));
} else {
	// plugins/Bancha
	define('ROOT', realpath($pathToBancha . '/../..'));
}	
define('APP_DIR', basename(ROOT . '/app'));
define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'lib');

define('WWW_ROOT', ROOT . DS . APP_DIR . 'webroot');
define('WEBROOT_DIR', basename(WWW_ROOT));
define('APP_PATH', ROOT . DS . APP_DIR . DS);
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);

include(CORE_PATH . 'Cake' . DS . 'bootstrap.php');
include($pathToBancha . DS . 'Config' . DS . 'bootstrap.php');

Configure::write('debug', 1);

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');
App::uses('CakeResponse', 'Network');

// make sure to import the ArticlesController, which applies the sleep time
require_once dirname(__FILE__) . '/ArticlesController.php';

// make sure to use the test database
App::uses('ClassRegistry', 'Utility');
App::uses('AppModel', 'Model');
App::uses('Article', 'Model');
$article = ClassRegistry::init('Article');
$article->setDataSource('test');

// execute query
if (isset($_SERVER['argv'][1]))
{
	$client_id = $_SERVER['argv'][1];
}
if (isset($_SERVER['argv'][2]))
{
	$article_id = $_SERVER['argv'][2];
}
if (isset($_SERVER['argv'][3]))
{
	$tid = $_SERVER['argv'][3];
}
if (isset($_SERVER['argv'][4]))
{
	$title = $_SERVER['argv'][4];
}
if (isset($_SERVER['argv'][5]))
{
	define('SLEEP_TIME', $_SERVER['argv'][5]);
}

// prepare the fake request
$rawPostData = json_encode(array(
	array(
		'action'		=> 'Articles',
		'method'		=> 'update',
		'tid'			=> $tid,
		'type'			=> 'rpc',
		'data'			=> array(array('data'=>array(
			'__bcid'		=> $client_id,
			'id'			=> $article_id,
			'title'			=> $title,
			'published'		=> true,
		))),
	),
));

// execute
$dispatcher = new BanchaDispatcher();
$dispatcher->dispatch(
	new BanchaRequestCollection($rawPostData),
	new CakeResponse()
);

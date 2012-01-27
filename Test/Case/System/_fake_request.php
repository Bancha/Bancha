<?php
/**
* @package       Bancha
* @category      TestFixtures
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

// this script is used by the ConsistentModelTest to simulate executing multiple HTTP requests in parallel.

// this script can be called using
// php _fake_request.php client_id article_id tid title sleep_time

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath(dirname(__FILE__) . '/../../../../..'));
define('APP_DIR', basename(ROOT . '/app'));
define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'lib');

define('WWW_ROOT', ROOT . DS . APP_DIR . 'webroot');
define('WEBROOT_DIR', basename(WWW_ROOT));
define('APP_PATH', ROOT . DS . APP_DIR . DS);
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);

include(CORE_PATH . 'Cake' . DS . 'bootstrap.php');
include(ROOT . DS . 'plugins' . DS . 'Bancha' . DS . 'Config' . DS . 'bootstrap.php');

Configure::write('debug',1);

App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');
App::uses('AppModel', 'Model');
App::uses('Article', 'Model');
// require_once dirname(__FILE__) . '/ArticlesController.php';

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

$dispatcher = new BanchaDispatcher();

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
$responses = json_decode($dispatcher->dispatch(
	new BanchaRequestCollection($rawPostData), array('return' => true)
));

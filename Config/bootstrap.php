<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after core.php
 *
 * This file should load/create any application wide configuration settings, such as
 * Caching, Logging, loading additional configuration files.
 *
 * You should also use this file to include any files that provide global functions/constants
 * that your application uses.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Config
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.config
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */


// load Banchaplugin
CakePlugin::load('Bancha');

// set namespace for ExtJS
Configure:write('Bancha.namespace','Bancha.RemoteStubs');


// TODO: DEFINE _ KONSTANTE _ BANCHA_CONTEXT_ True

// TODO: LOAD PLUGINS in webroot/bancha.php -> don't load it here
// load Behavior -> loaded in app/Config/bootstrap.php
//App::uses('BanchaBehavior', 'Bancha.Model/Behavior');
//App::load('BanchaBehavior');

// load exceptionhandler
App::uses('BanchaExceptionHandler', 'Bancha.Bancha/ExceptionHandler');
App::load('BanchaExceptionHandler');
// register exceptionhandler
Configure::write('Exception.handler', array('BanchaExceptionHandler', 'handleException'));


App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

//echo "hello bootstrap";

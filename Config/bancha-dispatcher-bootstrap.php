<?php
/**
 * This file is loaded automatically by the app/webroot/bancha.php file after core.php
 *
 * This file should load/create any application wide configuration settings.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha.Config
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

// load exceptionhandler
App::uses('BanchaExceptionHandler', 'Bancha.Bancha/ExceptionHandler');
App::load('BanchaExceptionHandler');
// register exceptionhandler
Configure::write('Exception.handler', array('BanchaExceptionHandler', 'handleException'));

// load helper classes for the dispatcher
App::uses('BanchaDispatcher', 'Bancha.Bancha/Routing');
App::uses('BanchaRequestCollection', 'Bancha.Bancha/Network');

// this shouldn't be necessary, but sometime it is.. maybe becaue of the caching?!
App::import('Controller', 'Bancha.Bancha');
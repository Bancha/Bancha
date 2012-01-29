<?php
/**
 * Routes configuration
 *
 * This file configures Banchas routing.
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Config
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */



/**
 * connect the remote api
 */
Router::parseExtensions('js');
Router::connect('/bancha-api', array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'index'));
Router::connect('/bancha-api/models/:metaDataForModels', array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'index'),array('pass'=>array('metaDataForModels')));

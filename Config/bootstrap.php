<?php
/**
 * This file is loaded automatically by the cake for both Bancha and standard cake requests
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
 * @since         Bancha v 0.9.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

// don't make "Banchas", keep it Bancha
Inflector::rules('plural', array('/^Bancha$/i' => 'Bancha'));

// config defaults
Configure::write('Bancha.Api.AuthConfig',false);
Configure::write('Bancha.Api.stubsNamespace','Bancha.RemoteStubs');
Configure::write('Bancha.allowMultiRecordRequests',false);
<?php
/**
 * Bancha Test Model
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 StudioQ OG
 *
 * @package       Bancha
 * @category      tests
 * @copyright     Copyright 2011-2013 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 1.2.3
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * Bancha Test Model
 *
 * This model doesn not have an associated controller, but it is configured
 * as BanchaRemotable. This should result in an error when loading the 
 * bancha-api.js
 *
 * @package       Bancha.Test.test_app.Model
 */
class ModelWithoutControllerBanchaApiTest extends AppModel {

/**
 * Behaviors
 * 
 * @var array
 */
	public $actsAs = array('Bancha.BanchaRemotable');

}

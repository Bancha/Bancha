<?php
/**
 * Bancha Test Model
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.test_app.Model_BanchaApi_MissingController
 * @copyright     Copyright 2011-2013 codeQ e.U.
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
 * @package       Bancha.Test.test_app.Model_BanchaApi_MissingController
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 1.2.3
 */
class ModelWithoutControllerBanchaApiTest extends AppModel {

/**
 * Behaviors
 *
 * @var array
 */
	public $actsAs = array('Bancha.BanchaRemotable');

/**
 * We never save or ready anything, so don't setup a 
 * database table via fixture.
 * 
 * @var boolean
 */
	public $useTable = false;

}

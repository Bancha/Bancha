<?php
/**
 * Bancha Test Model
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Test.test_app.Model_BanchaApi_MissingController
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
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

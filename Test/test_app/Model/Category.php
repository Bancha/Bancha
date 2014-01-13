<?php
/**
 * Category Model
 *
 * @package       Bancha.Test.test_app.Model
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.2.0
 */
class Category extends AppModel {
/**
 * Behaviors
 */
	public $actsAs = array('Bancha.BanchaRemotable', 'Tree');
}

<?php
/**
 * CategoryFixture
 *
 * @package       Bancha.Test.Fixture
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.2.0
 */
class CategoryFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'unsigned' => true, 'key' => 'primary'),
		'parent_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 10, 'unsigned' => false),
		'lft' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 10, 'unsigned' => false),
		'rght' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 10, 'unsigned' => false),
		'name' => array('type' => 'string', 'null' => true, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '1',
			'parent_id' => null,
			'lft' => '1',
			'rght' => '30',
			'name' => 'My Categories'
		),
		array(
			'id' => '2',
			'parent_id' => '1',
			'lft' => '2',
			'rght' => '15',
			'name' => 'Fun'
		),
		array(
			'id' => '3',
			'parent_id' => '2',
			'lft' => '3',
			'rght' => '8',
			'name' => 'Sport'
		),
		array(
			'id' => '4',
			'parent_id' => '3',
			'lft' => '4',
			'rght' => '5',
			'name' => 'Surfing'
		),
		array(
			'id' => '5',
			'parent_id' => '3',
			'lft' => '6',
			'rght' => '7',
			'name' => 'Extreme knitting'
		),
		array(
			'id' => '6',
			'parent_id' => '2',
			'lft' => '9',
			'rght' => '14',
			'name' => 'Friends'
		),
		array(
			'id' => '7',
			'parent_id' => '6',
			'lft' => '10',
			'rght' => '11',
			'name' => 'Gerald'
		),
		array(
			'id' => '8',
			'parent_id' => '6',
			'lft' => '12',
			'rght' => '13',
			'name' => 'Gwendolyn'
		),
		array(
			'id' => '9',
			'parent_id' => '1',
			'lft' => '16',
			'rght' => '29',
			'name' => 'Work'
		),
		array(
			'id' => '10',
			'parent_id' => '9',
			'lft' => '17',
			'rght' => '22',
			'name' => 'Reports'
		),
		array(
			'id' => '11',
			'parent_id' => '10',
			'lft' => '18',
			'rght' => '19',
			'name' => 'Annual'
		),
		array(
			'id' => '12',
			'parent_id' => '10',
			'lft' => '20',
			'rght' => '21',
			'name' => 'Status'
		),
		array(
			'id' => '13',
			'parent_id' => '9',
			'lft' => '23',
			'rght' => '28',
			'name' => 'Trips'
		),
		array(
			'id' => '14',
			'parent_id' => '13',
			'lft' => '24',
			'rght' => '25',
			'name' => 'National'
		),
		array(
			'id' => '15',
			'parent_id' => '13',
			'lft' => '26',
			'rght' => '27',
			'name' => 'International'
		),
	);

}

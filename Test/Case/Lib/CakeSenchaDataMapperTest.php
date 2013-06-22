<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 StudioQ OG
 *
 * @package       Bancha
 * @category      tests
 * @copyright     Copyright 2011-2013 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('CakeSenchaDataMapper', 'Bancha.Bancha');

/**
 * BanchaApiTest
 *
 * @package       Bancha
 * @category      tests
 */
class CakeSenchaDataMapperTest extends CakeTestCase {

	// see http://book.cakephp.org/2.0/en/models/retrieving-your-data.html#find-first
	private $singleRecord = array(
		'ModelName' => array(
			'id' => 83,
			'field1' => 'value1',
			'field2' => 'value2',
			'field3' => 'value3',
		),
		'AssocitedModelName' => array(
			'id' => 1,
			'field1' => 'value1',
			'field2' => 'value2',
			'field3' => 'value3',
		),
	);
	// see http://book.cakephp.org/2.0/en/models/retrieving-your-data.html#find-all
	private $singleRecordSet = array(
		array(
			'ModelName' => array(
				'id' => 83,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
			'AssocitedModelName' => array(
				'id' => 1,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
		),
	);
	private $multiRecordSet = array(
		array(
			'ModelName' => array(
				'id' => 83,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
			'AssocitedModelName' => array(
				'id' => 1,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
		),
		array(
			'ModelName' => array(
				'id' => 84,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
			'AssocitedModelName' => array(
				'id' => 1,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
		),
		array(
			'ModelName' => array(
				'id' => 85,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
			'AssocitedModelName' => array(
				'id' => 1,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
		),
	);
	// see http://book.cakephp.org/2.0/en/models/retrieving-your-data.html#find-threaded
	private $threadedRecordSet = array(
		array(
			'ModelName' => array(
				'id' => 83,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
			'AssocitedModelName' => array(
				'id' => 1,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
			'children' => array(
				array(
					'ModelName' => array(
						'id' => 84,
						'field1' => 'value1',
						'field2' => 'value2',
						'field3' => 'value3',
					),
					'AssocitedModelName' => array(
						'id' => 1,
						'field1' => 'value1',
						'field2' => 'value2',
						'field3' => 'value3',
					),
				),
			),
		),
		array(
			'ModelName' => array(
				'id' => 85,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
			'AssocitedModelName' => array(
				'id' => 1,
				'field1' => 'value1',
				'field2' => 'value2',
				'field3' => 'value3',
			),
			'children' => array()
		),
	);
	private $paginatedRecordSet = array(
		'count' => 100,
		'records' => array(
			array(
				'ModelName' => array(
					'id' => 83,
					'field1' => 'value1',
					'field2' => 'value2',
					'field3' => 'value3',
				),
				'AssocitedModelName' => array(
					'id' => 1,
					'field1' => 'value1',
					'field2' => 'value2',
					'field3' => 'value3',
				),
			),
		),
	);

	/**
	 * Test isSingleRecord function
	 *
	 * @dataProvider isSingleRecordDataProvider
	 */
	public function testIsSingleRecord($data, $expectedResult) {

		$mapper = new CakeSenchaDataMapper($data, 'ModelName');
		$result = $mapper->isSingleRecord();

		$this->assertEquals($result, $expectedResult);
	}
	/**
	 * Data Provider for testIsSingleRecord
	 */
	public function isSingleRecordDataProvider() {
		return array(
			array(
				$this->singleRecord,
				true
			),
			array(
				$this->singleRecordSet,
				false
			),
			array(
				$this->multiRecordSet,
				false
			),
			array(
				$this->threadedRecordSet,
				false
			),
			array(
				$this->paginatedRecordSet,
				false
			),
		);
	}


	/**
	 * Test isRecordSet function
	 *
	 * @dataProvider isRecordSetDataProvider
	 */
	public function testIsRecordSet($data, $expectedResult) {

		$mapper = new CakeSenchaDataMapper($data, 'ModelName');
		$result = $mapper->isRecordSet();

		$this->assertEquals($result, $expectedResult);
	}
	/**
	 * Data Provider for testIsRecordSet
	 */
	public function isRecordSetDataProvider() {
		return array(
			array(
				$this->singleRecord,
				false
			),
			array(
				$this->singleRecordSet,
				true
			),
			array(
				$this->multiRecordSet,
				true
			),
			array(
				$this->threadedRecordSet,
				true
			),
			array(
				$this->paginatedRecordSet,
				false
			),
		);
	}

	/**
	 * Test isThreadedRecordSet function
	 *
	 * @dataProvider isThreadedRecordSetDataProvider
	 */
	public function testIsThreadedRecordSet($data, $expectedResult) {

		$mapper = new CakeSenchaDataMapper($data, 'ModelName');
		$result = $mapper->isThreadedRecordSet();

		$this->assertEquals($result, $expectedResult);
	}
	/**
	 * Data Provider for testIsThreadedRecordSet
	 */
	public function isThreadedRecordSetDataProvider() {
		return array(
			array(
				$this->singleRecord,
				false
			),
			array(
				$this->singleRecordSet,
				false
			),
			array(
				$this->multiRecordSet,
				false
			),
			array(
				$this->threadedRecordSet,
				true
			),
			array(
				$this->paginatedRecordSet,
				false
			),
		);
	}


	/**
	 * Test isPaginatedSet function
	 *
	 * @dataProvider isPaginatedSetDataProvider
	 */
	public function testIsPaginatedSet($data, $expectedResult) {

		$mapper = new CakeSenchaDataMapper($data, 'ModelName');
		$result = $mapper->isPaginatedSet();

		$this->assertEquals($result, $expectedResult);
	}
	/**
	 * Data Provider for testIsPaginatedSet
	 */
	public function isPaginatedSetDataProvider() {
		return array(
			array(
				$this->singleRecord,
				false
			),
			array(
				$this->singleRecordSet,
				false
			),
			array(
				$this->multiRecordSet,
				false
			),
			array(
				$this->threadedRecordSet,
				false
			),
			array(
				$this->paginatedRecordSet,
				true
			),
		);
	}
}

<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.Lib
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 2.0.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('CakeSenchaDataMapper', 'Bancha.Bancha');

/**
 * BanchaApiTest
 *
 * @package       Bancha.Test.Case.Lib
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 2.0.0
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
	// record with hasMany association
	private $singleRecordWithHasMany = array(
		'Article' => array(
			'id' => 1001,
			'title' => 'Title 1',
			'date' => '2011-11-24 03:40:04',
			'body' => 'Text 1',
			'published' => 1,
			'user_id' => 95,
		),
		'Tag' => array(
			array(
				'id' => 1,
				'string' => 'CakePHP',
			),
			array(
				'id' => 2,
				'string' => 'Bancha',
			),
		),
	);
	// some data with recursive=3
	private $singleRecordWithDeeplyNestedData = array(
		'Article' => array(
			'id' => 1001,
			'title' => 'Title 1',
			'date' => '2011-11-24 03:40:04',
			'body' => 'Text 1',
			'published' => 1,
			'user_id' => 95,
		),
		'User' => array(
			'id' => 95,
			'user' => 'mariano',
			'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
			'created' => '2007-03-17 01:16:23',
			'updated' => '2007-03-17 01:18:31',
			'Article' => array(
				array(
					'id' => 1001,
					'title' => 'Title 1',
					'date' => '2011-11-24 03:40:04',
					'body' => 'Text 1',
					'published' => '1',
					'user_id' => 95,
					'User' => array(
						'id' => 95,
						'user' => 'mariano',
						'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
						'created' => '2007-03-17 01:16:23',
						'updated' => '2007-03-17 01:18:31',
					),
					'Tag' => array(
						array(
							'id' => 1,
							'string' => 'CakePHP',
						),
						array(
							'id' => 2,
							'string' => 'Bancha',
						)
					)
				),
				array(
					'id' => 1002,
					'title' => 'Title 2',
					'date' => '2011-12-24 03:40:04',
					'body' => 'Text 2',
					'published' => false,
					'user_id' => 95,
					'User' => array(
						'id' => 95,
						'user' => 'mariano',
						'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
						'created' => '2007-03-17 01:16:23',
						'updated' => '2007-03-17 01:18:31',
					),
					'Tag' => array()
				),
			),
		), //eo User
		'Tag' => array(
			array(
				'id' => 1,
				'string' => 'CakePHP',
			),
			array(
				'id' => 2,
				'string' => 'Bancha',
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

	/**
	 * Test walk function, simply check that each record is visited
	 *
	 * @dataProvider walkDataProvider
	 */
	public function testWalk($data, $expectedResult) {
		$this->walkerResult = array();
		$mapper = new CakeSenchaDataMapper($data, 'ModelName');
		$mapper->walk(array($this, 'walkerCallback'));

		$this->assertEquals($expectedResult, $this->walkerResult);
	}
	private $walkerResult = false;
	public function walkerCallback($modelName, $data) {
		if (!isset($this->walkerResult[$modelName])) {
			$this->walkerResult[$modelName] = 1;
		} else {
			$this->walkerResult[$modelName]++;
		}
		return array($modelName, $data);
	}
	/**
	 * Data Provider for walk
	 */
	public function walkDataProvider() {
		return array(
			array(
				$this->singleRecord,
				array(
					'ModelName'=> 1,
					'AssocitedModelName' => 1
				)
			),
			array(
				$this->singleRecordSet,
				array(
					'ModelName'=> 1,
					'AssocitedModelName' => 1
				)
			),
			array(
				$this->multiRecordSet,
				array(
					'ModelName'=> 3,
					'AssocitedModelName' => 3
				)
			),
			array(
				$this->paginatedRecordSet,
				array(
					'ModelName'=> 1,
					'AssocitedModelName' => 1
				)
			),
			array(
				$this->singleRecordWithHasMany,
				array(
					'Article'=> 1,
					'Tag' => 2
				)
			),
			array(
				$this->singleRecordWithDeeplyNestedData,
				array(
					'Article'=> 3,
					'User' => 3,
					'Tag' => 5
				)
			),
			array(
				$this->threadedRecordSet,
				array(
					'ModelName' => 3,
					'AssocitedModelName' => 3
				)
			),
		);
	}

	// walker function for below
	public function walkerRenamingCallback($modelName, $data) {
		if ($data == null) {
			// this is the empty tags array
			return array(substr($modelName, 1), $data);
		}
		if (!isset($data['id'])) {
			throw new Exception('Expected record data, instead got malformed input: '.print_r(aray($modelName, $data), true));
		}
		// remove id keys
		unset($data['id']);
		// transform model name to not have their first char
		return array(substr($modelName, 1), $data);
	}

	/**
	 * Test that the walk function is able to transform model name keys.
	 */
	public function testWalkSingleRecordRenaming() {

		// execute
		$mapper = new CakeSenchaDataMapper($this->singleRecord, 'ModelName');
		$result = $mapper->walk(array($this, 'walkerRenamingCallback'));

		// test that the top level records are renamed
		$this->assertFalse(isset($result['ModelName']));
		$this->assertTrue(isset($result['odelName']));
		$this->assertFalse(isset($result['AssocitedModelName']));
		$this->assertTrue(isset($result['ssocitedModelName']));

		// test that the ids are removed
		$this->assertFalse(isset($result['odelName']['id']));
		$this->assertFalse(isset($result['ssocitedModelName']['id']));
	}

	/**
	 * Test that the walk function is able to transform model name keys.
	 */
	public function testWalkRecordSetRenaming() {

		// execute
		$mapper = new CakeSenchaDataMapper($this->multiRecordSet, 'ModelName');
		$result = $mapper->walk(array($this, 'walkerRenamingCallback'));

		// test that the top level records are renamed
		$this->assertFalse(isset($result[0]['ModelName']));
		$this->assertTrue(isset($result[0]['odelName']));
		$this->assertFalse(isset($result[0]['AssocitedModelName']));
		$this->assertTrue(isset($result[0]['ssocitedModelName']));

		$this->assertFalse(isset($result[1]['ModelName']));
		$this->assertTrue(isset($result[1]['odelName']));
		$this->assertFalse(isset($result[1]['AssocitedModelName']));
		$this->assertTrue(isset($result[1]['ssocitedModelName']));

		$this->assertFalse(isset($result[2]['ModelName']));
		$this->assertTrue(isset($result[2]['odelName']));
		$this->assertFalse(isset($result[2]['AssocitedModelName']));
		$this->assertTrue(isset($result[2]['ssocitedModelName']));

		// test that the ids are removed
		$this->assertFalse(isset($result[0]['odelName']['id']));
		$this->assertFalse(isset($result[0]['ssocitedModelName']['id']));

		$this->assertFalse(isset($result[1]['odelName']['id']));
		$this->assertFalse(isset($result[1]['ssocitedModelName']['id']));

		$this->assertFalse(isset($result[2]['odelName']['id']));
		$this->assertFalse(isset($result[2]['ssocitedModelName']['id']));
	}

	/**
	 * Test that the walk function is able to transform model name keys.
	 */
	public function testWalkPaginatedRecordSetRenaming() {

		// execute
		$mapper = new CakeSenchaDataMapper($this->paginatedRecordSet, 'ModelName');
		$result = $mapper->walk(array($this, 'walkerRenamingCallback'));

		// test that the top level records are renamed
		$this->assertFalse(isset($result['records'][0]['ModelName']));
		$this->assertTrue(isset($result['records'][0]['odelName']));
		$this->assertFalse(isset($result['records'][0]['AssocitedModelName']));
		$this->assertTrue(isset($result['records'][0]['ssocitedModelName']));

		// test that the ids are removed
		$this->assertFalse(isset($result['records'][0]['odelName']['id']));
		$this->assertFalse(isset($result['records'][0]['ssocitedModelName']['id']));
	}
	
	/**
	 * Test that the walk function is able to transform model name keys.
	 */
	public function testWalkRecursiveRenaming() {

		// execute
		$mapper = new CakeSenchaDataMapper($this->singleRecordWithDeeplyNestedData, 'Article');
		$result = $mapper->walk(array($this, 'walkerRenamingCallback'));

		// test that the top level records are renamed
		$this->assertFalse(isset($result['Article']));
		$this->assertTrue(isset($result['rticle']));
		$this->assertFalse(isset($result['User']));
		$this->assertTrue(isset($result['ser']));
		$this->assertFalse(isset($result['Tag']));
		$this->assertTrue(isset($result['ag']));

		// test that the ids are removed from top level records
		$this->assertFalse(isset($result['rticle']['id']));
		$this->assertFalse(isset($result['ser']['id']));
		$this->assertFalse(isset($result['ag'][0]['id']));
		$this->assertFalse(isset($result['ag'][1]['id']));

		// test that the Articles key in User is renamed
		$this->assertFalse(isset($result['ser']['Article']));
		$this->assertTrue(isset($result['ser']['rticle']));

		// test that the records in User.Article are renamed
		$this->assertFalse(isset($result['ser']['rticle'][0]['User']));
		$this->assertTrue(isset($result['ser']['rticle'][0]['ser']));
		$this->assertFalse(isset($result['ser']['rticle'][0]['Tag']));
		$this->assertTrue(isset($result['ser']['rticle'][0]['ag']));

		$this->assertFalse(isset($result['ser']['rticle'][1]['User']));
		$this->assertTrue(isset($result['ser']['rticle'][1]['ser']));
		$this->assertFalse(isset($result['ser']['rticle'][1]['Tag'])); // this is a edge case, since the array is empty
		$this->assertTrue(isset($result['ser']['rticle'][1]['ag']));
		$this->assertTrue(is_array($result['ser']['rticle'][1]['ag']));
		$this->assertTrue(empty($result['ser']['rticle'][1]['ag']));

		// test that the ids are remvoed from the records in User.Article
		$this->assertFalse(isset($result['ser']['rticle'][0]['ser']['id']));
		$this->assertFalse(isset($result['ser']['rticle'][0]['ag'][0]['id']));
		$this->assertFalse(isset($result['ser']['rticle'][0]['ag'][1]['id']));

		$this->assertFalse(isset($result['ser']['rticle'][1]['ser']['id']));
	}

	/**
	 * Test that the walk function is able to remove entries from the array
	 */
	public function testWalkRemoveEntries() {

		// test removing of all tags
		$mapper = new CakeSenchaDataMapper($this->singleRecordWithDeeplyNestedData, 'Article');
		$result = $mapper->walk(array($this, 'walkerRemoveEntriesCallback1'));

		$this->assertTrue(isset($result['Article']));
		$this->assertTrue(isset($result['User']));
		$this->assertFalse(isset($result['Tag']));

		$this->assertTrue(isset($result['User']['Article']));
		$this->assertFalse(isset($result['User']['Article'][0]['Tag']));
		$this->assertFalse(isset($result['User']['Article'][1]['Tag']));

		// test removing of all users
		$mapper = new CakeSenchaDataMapper($this->singleRecordWithDeeplyNestedData, 'Article');
		$result = $mapper->walk(array($this, 'walkerRemoveEntriesCallback2'));

		$this->assertTrue(isset($result['Article']));
		$this->assertFalse(isset($result['User']));
		$this->assertTrue(isset($result['Tag']));

		// test removing of article with id 1001
		$mapper = new CakeSenchaDataMapper($this->singleRecordWithDeeplyNestedData, 'Article');
		$result = $mapper->walk(array($this, 'walkerRemoveEntriesCallback3'));

		$this->assertFalse(isset($result['Article']));
		$this->assertTrue(isset($result['User']));
		$this->assertTrue(isset($result['Tag']));

		$this->assertTrue(isset($result['User']['Article'])); // one article should still exist
		$this->assertCount(1, $result['User']['Article']);

		$this->assertEquals(1002, $result['User']['Article'][0]['id']);
		$this->assertTrue(isset($result['User']['Article'][0]['User']));
		$this->assertFalse(isset($result['ser']['rticle'][0]['Tag']));

		// edge case, removing associated data from the current models data should also remove it
		// each User removes this Article record
		$mapper = new CakeSenchaDataMapper($this->singleRecordWithDeeplyNestedData, 'Article');
		$result = $mapper->walk(array($this, 'walkerRemoveEntriesCallback4'));

		$this->assertTrue(isset($result['Article']));
		$this->assertTrue(isset($result['User']));
		$this->assertTrue(isset($result['Tag']));

		$this->assertFalse(isset($result['User']['Article']));


		// each Article removes this User record (nested ones only)
		$mapper = new CakeSenchaDataMapper($this->singleRecordWithDeeplyNestedData, 'Article');
		$result = $mapper->walk(array($this, 'walkerRemoveEntriesCallback5'));

		$this->assertTrue(isset($result['Article']));
		$this->assertTrue(isset($result['User']));
		$this->assertTrue(isset($result['Tag']));

		$this->assertTrue(isset($result['User']['Article']));
		$this->assertCount(2, $result['User']['Article']);
		$this->assertFalse(isset($result['User']['Article'][0]['User']));
		$this->assertFalse(isset($result['User']['Article'][1]['User']));

	}
	public function walkerRemoveEntriesCallback1($modelName, $data) {
		return array(($modelName=='Tag' ? false : $modelName), $data);
	}
	public function walkerRemoveEntriesCallback2($modelName, $data) {
		return array(($modelName=='User' ? false : $modelName), $data);
	}
	public function walkerRemoveEntriesCallback3($modelName, $data) {
		return array((($modelName=='Article' && $data['id']==1001) ? false : $modelName), $data);
	}
	public function walkerRemoveEntriesCallback4($modelName, $data) {
		if ($modelName=='User' && isset($data['Article'])) {
			unset($data['Article']);
		}
		return array($modelName, $data);
	}
	public function walkerRemoveEntriesCallback5($modelName, $data) {
		if ($modelName=='Article' && isset($data['User'])) {
			unset($data['User']);
		}
		return array($modelName, $data);
	}
}

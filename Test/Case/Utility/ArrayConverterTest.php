<?php
/**
 * @copyright     Copyright 2011 Bancha Project
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

set_include_path(realpath(dirname(__FILE__) . '/../../../lib/Bancha/') . PATH_SEPARATOR . get_include_path());
require_once 'Utility/ArrayConverter.php';
/**
 * ArrayConverterTest
 *
 * @package bancha.libs
 */
class ArrayConverterTest extends CakeTestCase
{
	
	public function testRenameElement()
	{
		$converter = new ArrayConverter(array(
			'foo'	=> 'bar',
		));
		$converter->renameElement('foo', 'foobar');
		$data = $converter->getArray();
		$this->assertTrue(isset($data['foobar']));
		$this->assertFalse(isset($data['foo']));
	}
	
	public function testRenameElementOverwrite()
	{
		$converter = new ArrayConverter(array(
			'foo'	=> 'bar',
			'bar'	=> 'foo',
		));
		$converter->renameElement('foo', 'bar');
		$data = $converter->getArray();
		$this->assertTrue(isset($data['bar']));
		$this->assertFalse(isset($data['foo']));
		$this->assertEquals('bar', $data['bar']);
	}
	
	public function testRemoveElement()
	{
		$converter = new ArrayConverter(array(
			'foo'	=> 'bar',
		));
		$value = $converter->removeElement('foo');
		$data = $converter->getArray();
		$this->assertFalse(isset($data['foo']));
		$this->assertEquals('bar', $value);
	}
	
	public function testRemoveElementDoesNotExist()
	{
		$converter = new ArrayConverter(array(
			'foo'	=> 'bar',
		));
		$value = $converter->removeElement('bar');
		$data = $converter->getArray();
		$this->assertNull($value);
	}
	
	public function testChangeValue()
	{
		$converter = new ArrayConverter(array(
			'action'	=> 'create',
		));
		$converter->changeValue('action', 'create', 'add');
		$data = $converter->getArray();
		$this->assertEquals('add', $data['action']);
	}
	
	public function testChangeValueCreate()
	{
		$converter = new ArrayConverter(array());
		$converter->changeValue('action', 'create', 'add');
		$data = $converter->getArray();
		$this->assertFalse(isset($data['action']));
		$converter->changeValue('action', 'create', 'add', true);
		$data = $converter->getArray();
		$this->assertTrue(isset($data['action']));
	}
	
}

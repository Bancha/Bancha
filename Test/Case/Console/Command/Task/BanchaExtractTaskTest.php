<?php
/**
 * BanchaExtractTaskTest file
 *
 * Test Case for Bancha's js i18n extraction shell task
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * @package       Bancha
 * @category      tests
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('Folder', 'Utility');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('ShellDispatcher', 'Console');
App::uses('Shell', 'Console');
App::uses('BanchaExtractTask', 'Bancha.Console/Command/Task');

/**
 * BanchaExtractTaskTest class
 *
 * @package       Cake.Test.Case.Console.Command.Task
 */
class BanchaExtractTaskTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);

		$this->Task = $this->getMock(
			'BanchaExtractTask',
			array('in', 'out', 'err', '_stop'),
			array($out, $out, $in)
		);
		$this->path = TMP . 'tests' . DS . 'extract_task_test';
		$Folder = new Folder($this->path . DS . 'locale', true);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Task);

		$Folder = new Folder($this->path);
		$Folder->delete();
		CakePlugin::unload();
	}

/**
 * testExecute method
 *
 * @return void
 */
	public function testExecute() {
		$this->Task->interactive = false;
		$this->Task->params['paths'] = App::pluginPath('Bancha') . 'Test' . DS . 'Case' . DS . 'Console' . DS . 
												'Command' . DS . 'Task' . DS . 'extraction_test_files';
		$this->Task->params['output'] = $this->path . DS;
		$this->Task->params['extract-core'] = 'no';
		$this->Task->expects($this->never())->method('err');
		$this->Task->expects($this->any())->method('in')
			->will($this->returnValue('y'));
		$this->Task->expects($this->never())->method('_stop');

		$this->Task->execute();
		$this->assertTrue(file_exists($this->path . DS . 'bancha.pot'));
		$result = file_get_contents($this->path . DS . 'bancha.pot');

		$this->assertFalse(file_exists($this->path . DS . 'default.pot'));

		// check header data
		$pattern = '/"Content-Type\: text\/plain; charset\=utf-8/';
		$this->assertRegExp($pattern, $result);
		$pattern = '/"Content-Transfer-Encoding\: 8bit/';
		$this->assertRegExp($pattern, $result);
		$pattern = '/"Plural-Forms\: nplurals\=INTEGER; plural\=EXPRESSION;/';
		$this->assertRegExp($pattern, $result);

		// extraction_tests.js
		$pattern = '/msgid "Bancha supports simple strings."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha supports simple with double."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Can support sprintf statements with multiple values: %s. %s"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha recognizes multi-lines strings"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha even recognizes joined multi-lines, a best practice for multi-line strings."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha recognizes ,joined multi-lines, a special join value."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha recognizes strange mixes between concatination and joined strings."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha recognizes xspecial joined multi-lines with sprintf like values, %s."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		// Bancha shell writtes every string only once
		$pattern = '/msgid "Bancha supports simple with double."\nmsgstr ""\n/';
		$found = preg_match_all($pattern, $result, $matches);
		$this->assertEquals(1, $found);

		// html_extraction_tests.html
		$pattern = '/msgid "BanchaI18n also searches html files."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		// check for correct line numbers
		$pattern = '/\#: (\\\\|\/)html_extraction_tests\.html:4\n/';
		$this->assertRegExp($pattern, $result);
		// check for non-translatable strings
	}

}

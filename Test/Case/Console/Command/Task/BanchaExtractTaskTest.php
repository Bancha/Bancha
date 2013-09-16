<?php
/**
 * BanchaExtractTaskTest file
 *
 * Test Case for Bancha's js i18n extraction shell task
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Test.Case.Console.Command.Task
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 1.3.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

App::uses('Folder', 'Utility');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('ShellDispatcher', 'Console');
App::uses('Shell', 'Console');
App::uses('ExtractTask', 'Console/Command/Task');
App::uses('BanchaExtractTask', 'Bancha.Console/Command/Task');

/**
 * This class simply exposed protected functions for unit testing
 */
class BanchaExtractTestTask extends BanchaExtractTask {
	public function collectJsArgument($code) {
		return $this->_collectJsArgument($code);
	}
	public function collectJsToken($code) {
		return $this->_collectJsToken($code);
	}
	public function findString($code) {
		return $this->_findString($code);
	}
	public function findVariable($code) {
		return $this->_findVariable($code);
	}
}

/**
 * BanchaExtractTaskTest class
 *
 * @package       Bancha.Test.Case.Console.Command.Task
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @since         Bancha v 1.3.0
 */
class BanchaExtractTaskTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		// use the PHPUnit getMock function to mock
		$out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);

		$this->Task = $this->getMock(
			'BanchaExtractTestTask',
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


	public function testFindString() {

		$result = $this->Task->findString('"This is a simple string"); Some more code');
		$this->assertTrue($result->isString());
		$this->assertEquals('This is a simple string', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		$result = $this->Task->findString("'This is a simple string'); Some more code");
		$this->assertEquals('This is a simple string', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		$result = $this->Task->findString('""); Some more code');
		$this->assertEquals('', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// test with one backslash before the quote
		$result = $this->Task->findString("'This is a simple escaped string \''); Some more code");
		$this->assertEquals('This is a simple escaped string \'', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// test with two backslashes before the quote
		$result = $this->Task->findString("'This is a simple escaped string \\\\'); Some more code");
		$this->assertEquals('This is a simple escaped string \\\\', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// test with three backslashes before the quote
		$result = $this->Task->findString("'This is a simple escaped string \\\\\\''); Some more code");
		$this->assertEquals('This is a simple escaped string \\\\\'', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		$result = $this->Task->findString("'Test concatinated ' + 'strings'); Some more code");
		$this->assertEquals('Test concatinated strings', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// test with error
		$result = $this->Task->findString('This one is missing some end quote.');
		$this->assertTrue($result->isError());
		$this->assertEquals('This one is missing some end quote.', $result->getRemainingCode()); //the original code
	}

	public function testFindVariable() {
		// test with parenteses
		$result = $this->Task->findVariable("someVar); Some more code");
		$this->assertEquals('someVar', $result->getVariableName());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// test with whitespace
		$result = $this->Task->findVariable("someVar ); Some more code");
		$this->assertEquals('someVar', $result->getVariableName());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// test with brace
		$result = $this->Task->findVariable("someVar}); Some more code");
		$this->assertEquals('someVar', $result->getVariableName());
		$this->assertEquals('}); Some more code', $result->getRemainingCode());

		// test with comma
		$result = $this->Task->findVariable("someVar, anotherVar); Some more code");
		$this->assertEquals('someVar', $result->getVariableName());
		$this->assertEquals(', anotherVar); Some more code', $result->getRemainingCode());

		// test with error
		$result = $this->Task->findVariable('someNoneEndingVarToken');
		$this->assertTrue($result->isError());
		$this->assertEquals('someNoneEndingVarToken', $result->getRemainingCode());
	}

	public function testCollectJsToken() {
		$result = $this->Task->collectJsToken('"This is a simple string"); Some more code');
		$this->assertTrue($result->isString());
		$this->assertEquals('This is a simple string', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		$result = $this->Task->collectJsToken("someVar); Some more code");
		$this->assertTrue($result->isVariable());
		$this->assertEquals('someVar', $result->getVariableName());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// test with error
		$result = $this->Task->collectJsToken('"someNoneEndingVarToken');
		$this->assertTrue($result->isError());

		$result = $this->Task->collectJsToken('someNoneEndingVarToken');
		$this->assertTrue($result->isError());

		// read a concatinated array of strings
		$result = $this->Task->collectJsToken('["la","la","la"].join("")); Some more code');
		$this->assertTrue($result->isString());
		$this->assertEquals('lalala', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		$result = $this->Task->collectJsToken("['la',\"la\",'la'].join('')); Some more code");
		$this->assertTrue($result->isString());
		$this->assertEquals('lalala', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// read a concatinated array of strings with spaces
		$result = $this->Task->collectJsToken('[ "la" , "la" , "la" ] . join( \'\' ) ); Some more code');
		$this->assertTrue($result->isString());
		$this->assertEquals('lalala', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// we currently support only empty-string-concated strings
		$result = $this->Task->collectJsToken('["la","la","la"].join()); Some more code');
		$this->assertTrue($result->isString());
		$this->assertEquals('la,la,la', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// don't accept read arrays
		$result = $this->Task->collectJsToken('["la","la","la"]); Some more code');
		$this->assertTrue($result->isError());
	}

	public function testCollectJsArgument() {

		// test same as above

		$result = $this->Task->collectJsArgument('"This is a simple string"); Some more code');
		$this->assertTrue($result->isString());
		$this->assertEquals('This is a simple string', $result->getStringValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		$result = $this->Task->collectJsArgument("someVar); Some more code");
		$this->assertTrue($result->isVariable());
		$this->assertEquals('someVar', $result->getVariableName());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// test with error
		$result = $this->Task->collectJsArgument('"someNoneEndingVarToken');
		$this->assertTrue($result->isError());
		$this->assertEquals('"someNoneEndingVarToken', $result->getRemainingCode());

		$result = $this->Task->collectJsArgument('someNoneEndingVarToken');
		$this->assertTrue($result->isError());
		$this->assertEquals('someNoneEndingVarToken', $result->getRemainingCode());

		// now try ternary with two strings
		$result = $this->Task->collectJsArgument('someNoneEndingVarToken ? "lala" : "lala2"); Some more code');
		$this->assertTrue($result->isTernary());
		$this->assertEquals('lala', $result->getTernaryFirstValue());
		$this->assertEquals('lala2', $result->getTernarySecondValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// now try ternary with one strings
		$result = $this->Task->collectJsArgument('someNoneEndingVarToken ? lala : "lala2"); Some more code');
		$this->assertTrue($result->isTernary());
		$this->assertEquals(false, $result->getTernaryFirstValue());
		$this->assertEquals('lala2', $result->getTernarySecondValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());

		// now try ternary with two variables
		$result = $this->Task->collectJsArgument('someNoneEndingVarToken ? lala : lala2); Some more code');
		$this->assertTrue($result->isTernary());
		$this->assertEquals(false, $result->getTernaryFirstValue());
		$this->assertEquals(false, $result->getTernarySecondValue());
		$this->assertEquals('); Some more code', $result->getRemainingCode());
	}

/**
 * testExecute method
 *
 * @return void
 */
	public function testExecute() {
		$this->Task->interactive = false;
		$this->Task->params['paths'] = dirname(__FILE__) . DS . 'extraction_test_files';
		$this->Task->params['exclude'] = dirname(__FILE__) . DS . 'extraction_test_files' . DS . 'error_tests.js';
		$this->Task->params['output'] = $this->path . DS;
		$this->Task->params['extract-core'] = 'no';
		$this->Task->expects($this->never())->method('err');
		$this->Task->expects($this->any())->method('in')
			->will($this->returnValue('y'));
		$this->Task->expects($this->never())->method('_stop');

		// the task should create apot file with all translations
		$this->Task->execute();
		$this->assertTrue(file_exists($this->path . DS . 'bancha.pot'));
		$result = file_get_contents($this->path . DS . 'bancha.pot');

		// it should not create a default translations file
		$this->assertFalse(file_exists($this->path . DS . 'default.pot'));

		// check header data
		$pattern = '/"Content-Type\: text\/plain; charset\=utf-8/';
		$this->assertRegExp($pattern, $result);
		$pattern = '/"Content-Transfer-Encoding\: 8bit/';
		$this->assertRegExp($pattern, $result);
		$pattern = '/"Plural-Forms\: nplurals\=INTEGER; plural\=EXPRESSION;/';
		$this->assertRegExp($pattern, $result);



		// extraction_tests.ctp
		$pattern = '/msgid "This is some string inside a php code"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "This is a string in a partial javascript code"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);



		// extraction_tests.html
		$pattern = '/msgid "BanchaI18n also searches html files."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);



		// extraction_tests.js
		// normal ones
		$pattern = '/msgid "Bancha supports simple strings."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha supports simple strings with double quotes."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Can support sprintf statements with multiple values: %s. %s"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha recognizes multi-lines strings"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha even recognizes joined multi-lines, a best practice for multi-line strings."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha recognizes, joined multi-lines, a special join value."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha recognizes strange mixes between concatination and joined strings."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "Bancha recognizes xspecial joined multi-lines with sprintf like values, %s."\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		// ternary
		$pattern = '/msgid "Bancha collect both strings for conditional strings"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);
		$pattern = '/msgid "Yes, even the second"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		// special sign edge cases
		$pattern = '/msgid "\( bla"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "I can\'t go home!"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "\(\) bla"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid ":\?{} bla"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "I quote \\\\"bla\\\\""\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "I quote \'bla\'"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "I quote \\\\"bla\\\\" and \'bla\'"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "I quote \\\\"bla\\\\" and \'bla\'"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);





		// Bancha shell writtes every string only once
		$pattern = '/msgid "Bancha supports simple with double."\nmsgstr ""\n/';
		$found = preg_match_all($pattern, $result, $matches);
		$this->assertEquals(1, $found);



		// check for correct line numbers in php
		$pattern = '/\#: (\\\\|\/)extraction_tests\.ctp:16\nmsgid "This is some string inside a php code./';
		$this->assertRegExp($pattern, $result);
		$pattern = '/\#: (\\\\|\/)extraction_tests\.ctp:26\nmsgid "This is a string in a partial javascript code./';
		$this->assertRegExp($pattern, $result);

		// line number is in html currently not supported

		// check for correct line numbers in js
		$pattern = '/\#: (\\\\|\/)extraction_tests\.js:2\nmsgid "Bancha supports simple strings./';
		$this->assertRegExp($pattern, $result);
		$pattern = '/\#: (\\\\|\/)extraction_tests\.js:5\nmsgid "Bancha supports simple strings with double quotes./';
		$this->assertRegExp($pattern, $result);
		$pattern = '/\#: (\\\\|\/)extraction_tests\.js:34\nmsgid "Bancha collect both strings for conditional strings/';
		$this->assertRegExp($pattern, $result);
		$pattern = '/\#: (\\\\|\/)extraction_tests\.js:44\nmsgid "I quote \\\\"bla\\\\" and \'bla\'/';
		$this->assertRegExp($pattern, $result);
	}


/**
 * testExecute method
 *
 * @return void
 */
	public function testExecute_NestedCalls() {
		$this->markTestSkipped('Add support for nested calls');
		// js code for nested calls
		// Bancha.t('This is the first part, with %', Bancha.t('a second sub-part in a nested call'));


		$this->Task->interactive = false;
		$this->Task->params['paths'] = dirname(__FILE__) . DS . 'extraction_test_files';
		$this->Task->params['exclude'] = dirname(__FILE__) . DS . 'extraction_test_files' . DS . 'error_tests.js';
		$this->Task->params['output'] = $this->path . DS;
		$this->Task->params['extract-core'] = 'no';
		$this->Task->expects($this->never())->method('err');
		$this->Task->expects($this->any())->method('in')
			->will($this->returnValue('y'));
		$this->Task->expects($this->never())->method('_stop');

		// the task should create apot file with all translations
		$this->Task->execute();
		$this->assertTrue(file_exists($this->path . DS . 'bancha.pot'));
		$result = file_get_contents($this->path . DS . 'bancha.pot');

		// it should not create a default translations file
		$this->assertFalse(file_exists($this->path . DS . 'default.pot'));

		// check nested call
		$pattern = '/msgid "This is the first part, with %"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		$pattern = '/msgid "a second sub-part in a nested call"\nmsgstr ""\n/';
		$this->assertRegExp($pattern, $result);

		// check for correct line numbers
		$pattern = '/\#: (\\\\|\/)extraction_tests\.js:47\nmsgid "This is the first part, with %/';
		$this->assertRegExp($pattern, $result);
	}

/**
 * testExecute method
 *
 * @return void
 */
	public function testExecute_Warnings() {
		$this->markTestSkipped('Write more tests for Bancha Jsi18n extraction');
		$this->Task->interactive = false;
		$this->Task->params['paths'] = dirname(__FILE__) . DS . 'extraction_test_files';
		$this->Task->params['exclude'] = dirname(__FILE__) . DS . 'extraction_test_files' . DS . 'error_tests.js';
		$this->Task->params['output'] = $this->path . DS;
		$this->Task->params['extract-core'] = 'no';
		$this->Task->expects($this->never())->method('err');
		$this->Task->expects($this->any())->method('in')
			->will($this->returnValue('y'));
		$this->Task->expects($this->never())->method('_stop');

		$this->Task->stderr->expects($this->once())->method('write')
			->with($this->stringContains('Missing action'));
	}
}

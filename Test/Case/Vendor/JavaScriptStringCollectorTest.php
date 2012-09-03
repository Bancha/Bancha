<?php
/**
 * JavaScriptStringCollectorTest file
 *
 * Test Case for Bancha's javascript string collector
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @category      tests
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */
App::import('File','JavaScriptStringCollector', array('search' => App::path('Vendor','Bancha'), 'file' => 'Jsi18nShellParsingHelper' . DS . 'JavaScriptStringCollector.php'));

/**
 * JavaScriptStringCollectorTest class
 *
 * @package       Cake.Test.Case.Console.Command.Task
 */
class JavaScriptStringCollectorTest extends CakeTestCase {

	/**
	 * Helper function, returns the tokenized version of js code
	 */
	public function tokenize($code) {
		$tokenizer = new JavaScript_Tokenizer($code);
		return $tokenizer->tokenize();
	}

	public function testSimpleString() {
		$collector = new JavaScriptStringCollector($this->tokenize('"I am a standard javascript string"'));
		$result = $collector->parse();

		$this->assertCount(1, $result); // expect on string
		$this->assertEquals('I am a standard javascript string' ,$result[0]);
	}

	public function testSimpleStringWithWhitespaces() {
		$collector = new JavaScriptStringCollector($this->tokenize("   ' I am a standard javascript string '   "));
		$result = $collector->parse();

		$this->assertCount(1, $result); // expect on string
		$this->assertEquals(' I am a standard javascript string ' ,$result[0]);
	}


	public function testParenthisedString() {
		$collector = new JavaScriptStringCollector($this->tokenize("  (' I am a standard javascript string ')   "));
		$result = $collector->parse();

		$this->assertCount(1, $result); // expect on string
		$this->assertEquals(' I am a standard javascript string ' ,$result[0]);
	}

	public function testArrayJoinedString() {
		$collector = new JavaScriptStringCollector($this->tokenize("
			[
				'A ',
				'joined ',
				'string'
			].join('')"));
		$result = $collector->parse();
		$this->assertCount(1, $result); // expect on string
		$this->assertEquals('A joined string' ,$result[0]);


		$collector = new JavaScriptStringCollector($this->tokenize("
			[
				'A ',
				'joined and '+
				'concatinated ',
				'string'
			].join('')"));
		$result = $collector->parse();
		$this->assertCount(1, $result); // expect on string
		$this->assertEquals('A joined and concatinated string' ,$result[0]);


		$collector = new JavaScriptStringCollector($this->tokenize("
			[
				'A',
				'default-joined',
				'string'
			].join()"));
		$result = $collector->parse();
		$this->assertCount(1, $result); // expect on string
		$this->assertEquals('A,default-joined,string' ,$result[0]);


		$collector = new JavaScriptStringCollector($this->tokenize("
			[
				'A',
				'point-joined',
				'string'
			].join('.')"));
		$result = $collector->parse();
		$this->assertCount(1, $result); // expect on string
		$this->assertEquals('A.point-joined.string' ,$result[0]);
	}

	public function testTernaryString() {
		$collector = new JavaScriptStringCollector($this->tokenize("  test ? 'Take eigther me' : 'or me'  "));
		$result = $collector->parse();

		$this->assertCount(2, $result); // expect two strings
		$this->assertEquals('Take eigther me', $result[0]);
		$this->assertEquals('or me', $result[1]);
	}

	public function testTernaryStringWithLogic() {
		$this->markTestSkipped("The used tokenizer fails here, see https://github.com/kbjr/UglifyJS.php/issues/2");
		$collector = new JavaScriptStringCollector($this->tokenize("  (test==true) ? 'Take eigther me' : 'or me')  "));
		$result = $collector->parse();

		$this->assertCount(2, $result); // expect two strings
		$this->assertEquals('Take eigther me', $result[0]);
		$this->assertEquals('or me', $result[1]);
	}
}

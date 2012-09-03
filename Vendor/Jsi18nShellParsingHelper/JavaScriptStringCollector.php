<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @subpackage    Console
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 1.0.1
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */
App::import('File','JavaScript_Tokenizer', array('search' => App::path('Vendor','Bancha'), 'file'=>'Jsi18nShellParsingHelper' . DS . 'parse-js.php'));

/**
 * Exceptions thrown when malformed javascript code is put into JavaScriptStringCollector
 *
 * @package       Bancha.Vendor.Jsi18nShellParsingHelper
 */
class JavaScriptStringCollectorParsingException extends Exception {
	public function __construct($msg,JS_TOKEN $token) {
		parent::__construct(
			'Parsing Error: '.$msg."\n".
			'Got illegal token '.$token->value.' of type '.$token->type.
			' at line '.$token->line.' col '.$token->col);
	}
}

/**
 * Exceptions thrown when a variable is used in a place where a string is expected
 *
 * @package       Bancha.Vendor.Jsi18nShellParsingHelper
 */
class JavaScriptStringCollectorGotVariableException extends Exception {
	public function __construct(JS_TOKEN $token) {
		parent::__construct(
			'Expected string instead of variable '.$token->value."\n".
			'Got illegal token '.$token->value.' of type '.$token->type.
			' at line '.$token->line.' col '.$token->col);
	}
}

/**
 * A Stage machine implementation to collect javascript strings.
 * This class will sue the current read position of the array as start!
 *
 * @package       Bancha.Vendor.Jsi18nShellParsingHelper
 */
class JavaScriptStringCollector {
	const EXPECT_EXPRESSION = 0;
	const EXPRESSION_END = 1;

	public static $i=0;
	private $state;
	private $tokens;
	private $str; // the currently collected string
	private $strings; // the result
	private $allowVariables = false;

	/**
	 * Creates a new string collector
	 * @param $tokens An array of JS_TOKEN's
	 * @param $allowVariables if false a JavaScriptStringCollectorGotVariableException
	 *                        gets thrown if a variable is found inside the expression
	 */
	public function __construct(Array $tokens, $allowVariables=false) {
		$this->state = self::EXPECT_EXPRESSION;
		$this->tokens = $tokens;
		$this->allowVariables = $allowVariables;
		$this->strings = array();
		$this->str = '';
	}

	// this is needed to keep track of the current position
	public function getTokens() {
		return $this->tokens;
	}

	/**
	 * Collects a string from a given token array and a given start
	 */
	public function parse() {

		// as long as string is not finished collect
		while($this->state != self::EXPRESSION_END || (current($this->tokens)->type == 'operator' || current($this->tokens)->value == '+')) {
			// string didn't end yet, try collecting

			switch($this->state) {
				case self::EXPECT_EXPRESSION:
					$this->readExpression();
					break;

				case self::EXPRESSION_END:
					// the current token is a plus operator (see while condition)
					next($this->tokens);
					$this->state = self::EXPECT_EXPRESSION;
					break;

				default:
					exit('Illegal state');
					throw new JavaScriptStringCollectorParsingException(
						'Illegal state', $this->tokens[$this->pos]);
			}
		}

		// everything read
		if($this->str != '') {
			array_push($this->strings, $this->str);
		}
		return $this->strings;
	}

	/**
	 * Start at EXPECT_EXPRESSION and read in one+ tokens
	 */
	private function readExpression() {
		switch (current($this->tokens)->type) {
			case 'string':
				$this->str .= current($this->tokens)->value;
				next($this->tokens);
				$this->state = self::EXPRESSION_END;
				break;

			case 'punc':
				$this->readSubExpression();
				break;

			case 'name':

				// this can be eigther just a string or a ternary,
				// so expecting a question mark
				if(next($this->tokens)->type == 'operator' && current($this->tokens)->value == '?') {
					next($this->tokens);
					$this->readTernaryValues();
				} else if(!$this->allowVariables) {
					throw new JavaScriptStringCollectorGotVariableException(current($this->tokens));
				}
				// nothing more to read
				break;

			default:
				throw new JavaScriptStringCollectorParsingException(
					'Illegal state',current($this->tokens));
		} //eo switch
	}

	/**
	 * Start at EXPECT_EXPRESSION and read a subexpression (current is of type 'punc', see also readExpression)
	 */
	private function readSubExpression() {

		switch(current($this->tokens)->value) {
			case '(':
				// end current string
				if($this->str != '') {
					array_push($this->strings, $this->str);
					$this->str = '';
				}

				// that was the parenthesis
				next($this->tokens);

				// collect the substrings and add them too
				// make a new machine for correct state
				$collector = new JavaScriptStringCollector($this->tokens);
				$this->strings = array_merge($this->strings, $collector->parse());
				$this->tokens = $collector->getTokens();

				// now expect a closing parenthesis
				if(current($this->tokens)->type != 'punc' || current($this->tokens)->value != ')') {
					throw new JavaScriptStringCollectorParsingException(
						'Expected closing parenthesis character.', current($this->tokens));
				}
				next($this->tokens); // just checked parenthesis end

				$this->state = self::EXPRESSION_END;
				break;

			case '[':
				// expecting a joined array of like ['str1','str2'].join('');
				next($this->tokens);

				// collect the first substr and add it
				$collector = new JavaScriptStringCollector($this->tokens);
				$result = $collector->parse();
				$this->tokens = $collector->getTokens();
				if(count($result)>1) {
					throw new JavaScriptStringCollectorParsingException(
						'Expressing inside a translation function is too complex, keep it simple', current($this->tokens));
				}
				// make it more verbose that we are collecting all strings
				$arraySubstrings = array($result[0]);

				// collect all substrings
				while(current($this->tokens)->type == 'punc' && current($this->tokens)->value == ',') {
					next($this->tokens);

					// read another substring
					$collector = new JavaScriptStringCollector($this->tokens);
					$result = $collector->parse();
					$this->tokens = $collector->getTokens();
					if(count($result)>1) {
						throw new JavaScriptStringCollectorParsingException(
							'Expressing inside a translation function is too complex, keep it simple', current($this->tokens));
					}
					array_push($arraySubstrings, $result[0]);
				}


				// now expect a .join('*') at the end!
				$valid = (current($this->tokens)->type == 'punc' && current($this->tokens)->value == ']');
				$valid = $valid ? (next($this->tokens)->type == 'punc' && current($this->tokens)->value == '.') : false;
				$valid = $valid ? (next($this->tokens)->type == 'name' && current($this->tokens)->value == 'join') : false;
				$valid = $valid ? (next($this->tokens)->type == 'punc' && current($this->tokens)->value == '(') : false;

				// the string is optional
				$joinValue = ','; // default
				if(next($this->tokens)->type == 'string') {
					$joinValue = current($this->tokens)->value;
					next($this->tokens);
				}

				$valid = $valid ? (current($this->tokens)->type == 'punc' && current($this->tokens)->value == ')') : false;
				next($this->tokens);

				if(!$valid) {
					throw new JavaScriptStringCollectorParsingException(
						'Found a array inside a translate function, these are only allowed when joined into a string, '.
						'can\'t handle pure arrays', current($this->tokens));
				}

				// join string and add it to current string
				$this->str .= implode($joinValue,$arraySubstrings);
				$this->state = self::EXPRESSION_END;
				break;

			default:
				throw new JavaScriptStringCollectorParsingException(
					'Illegal state', current($this->tokens));
		} //eo switch
	}

	/**
	 * Reads everything of a ternary after the question mark
	 */
	private function readTernaryValues() {

		try { // catch errors for more meaning full messages

			// expect substr(s)
			$collector = new JavaScriptStringCollector($this->tokens);
			$this->strings = array_merge($this->strings, $collector->parse());
			$this->tokens = $collector->getTokens();

			// expect an ':'
			if(current($this->tokens)->type != 'punc' || current($this->tokens)->value != ':') {
				throw new JavaScriptStringCollectorParsingException(
					'Trying to read ternary and therefore expected a ":" char.', current($this->tokens));
			}
			next($this->tokens);

			// expect substr(s)
			$collector = new JavaScriptStringCollector($this->tokens);
			$this->strings = array_merge($this->strings, $collector->parse());
			$this->tokens = $collector->getTokens();

			// that's it, done
			$this->state = self::EXPRESSION_END;

		} catch(JavaScriptStringCollectorParsingException $e) {
			throw new JavaScriptStringCollectorParsingException(
				'Trying to read ternary, but failed because '.$e->getMessage().'. Start at ', current($this->tokens));
		}
	}
}

<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Console.Command.Task
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 1.3.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('ExtractTask', 'Console/Command/Task');

/**
 * This class represents a token for extracting translations from JavaScript.
 * The class is used by BanchaExtractTask.
 *
 * @package       Bancha.Console.Command.Task
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 1.3.0
 */
class Bancha_JavaScriptToken {
	public static $TYPE_ERROR = false;
	public static $TYPE_STRING = 1;
	public static $TYPE_VARIABLE = 2;
	public static $TYPE_TERNARY = 3;

	private $type;
	private $content; // this is the value of the string, the name of the variable or an array of two ternary tokens
	private $remaining_code;

	function __construct($type, $content, $remainingCode) {
		if(gettype($type) == 'string') {
			$type = $type=='string' ? self::$TYPE_STRING : (
					$type=='variable' ? self::$TYPE_VARIABLE : (
					$type=='ternary' ? self::$TYPE_TERNARY : false));
		}
		$this->type = $type;
		$this->content = $content;
		$this->remainingCode = ltrim($remainingCode);
	}

	public function getType() {
		return $this->type;
	}
	public function isString() {
		return $this->type == self::$TYPE_STRING;
	}
	public function isVariable() {
		return $this->type == self::$TYPE_VARIABLE;
	}
	public function isTernary() {
		return $this->type == self::$TYPE_TERNARY;
	}
	public function isError() {
		return $this->type == self::$TYPE_ERROR;
	}

	public function getStringValue() {
		if(!$this->isString()) {
			throw new Exception('Bancha_JavaScriptToken::getStringValue should only be called for a token of type string.');
		}
		return $this->content;
	}
	public function getVariableName() {
		if(!$this->isVariable()) {
			throw new Exception('Bancha_JavaScriptToken::getVariableName should only be called for a token of type variable.');
		}
		return $this->content;
	}
	public function getTernaryFirstValue() {
		if(!$this->isTernary()) {
			throw new Exception('Bancha_JavaScriptToken::getVariableName should only be called for a token of type variable.');
		}
		return $this->content[0];
	}
	public function getTernarySecondValue() {
		if(!$this->isTernary()) {
			throw new Exception('Bancha_JavaScriptToken::getVariableName should only be called for a token of type variable.');
		}
		return $this->content[1];
	}
	public function getTernaryValues() {
		if(!$this->isTernary()) {
			throw new Exception('Bancha_JavaScriptToken::getVariableName should only be called for a token of type variable.');
		}
		return $this->content;
	}

	public function getRemainingCode() {
		return $this->remainingCode;
	}
}



/**
 * Language string extractor for Bancha.t translations
 *
 * It would be very nice here if we could use a real JavaScript tokenizer to collect all translatable strings,
 * but there doesn't exist any performant and correct working JavaScript tokenizer implementation in PHP.
 * Besides that we still would need another implementation to handle PHP files where the might not have cleanly
 * separated the javascript.
 *
 * So this is a quite robust implementation using a text search and partially tokenizing the code.
 *
 * @package       Bancha.Console.Command.Task
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @since         Bancha v 1.3.0
 */
class BanchaExtractTask extends ExtractTask {


/**
 * @access private
 * An array of directories to exclude.
 * (from core ExtractTask)
 *
 * @var array
 */
	public $exclude = array();
/**
 * @access private
 * Merge all domains string into the default.pot file
 * (from core ExtractTask)
 *
 * @var boolean
 */
	protected $_merge = false;
/**
 * @access private
 * Don't extract validation messages
 * (from core ExtractTask)
 *
 * @var boolean
 */
	protected $_extractValidation = false;

/**
 * Execution method always used for tasks
 * Removed options merge, ignore-model-validation, validation-domain, extract-core
 *
 * @return void
 */
	public function execute() {
		if (empty($this->params['exclude'])) {
			// by default exclude Bancha
			$this->params['exclude'] = dirname(dirname(dirname(dirname(__FILE__))));
		}
		$this->_exclude = explode(',', $this->params['exclude']);


		if (isset($this->params['files']) && !is_array($this->params['files'])) {
			$this->_files = explode(',', $this->params['files']);
		}
		if (isset($this->params['paths'])) {
			$this->_paths = explode(',', $this->params['paths']);
		} elseif (isset($this->params['plugin'])) {
			$plugin = Inflector::camelize($this->params['plugin']);
			if (!CakePlugin::loaded($plugin)) {
				CakePlugin::load($plugin);
			}
			$this->_paths = array(CakePlugin::path($plugin));
			$this->params['plugin'] = $plugin;
		} else {
			$this->_getPaths();
		}

		$this->_extractCore = false;

		if (!empty($this->params['exclude-plugins']) && $this->_isExtractingApp()) {
			$this->_exclude = array_merge($this->_exclude, App::path('plugins'));
		}

		$this->_extractValidation = false;
		$this->_validationDomain = false;

		if (isset($this->params['output'])) {
			$this->_output = $this->params['output'];
		} elseif (isset($this->params['plugin'])) {
			$this->_output = $this->_paths[0] . DS . 'Locale';
		} else {
			$message = __d('cake_console', "What is the path you would like to output?\n[Q]uit", $this->_paths[0] . DS . 'Locale');
			while (true) {
				$response = $this->in($message, null, rtrim($this->_paths[0], DS) . DS . 'Locale');
				if (strtoupper($response) === 'Q') {
					$this->out(__d('cake_console', 'Extract Aborted'));
					$this->_stop();
				} elseif (is_dir($response)) {
					$this->_output = $response . DS;
					break;
				} else {
					$this->err(__d('cake_console', 'The directory path you supplied was not found. Please try again.'));
				}
				$this->out();
			}
		}

		if (empty($this->_files)) {
			$this->_searchFiles();
		}
		$this->_output = rtrim($this->_output, DS) . DS;
		$this->_extract();
	}
/**
 * Removed options merge, ignore-model-validation, validation-domain, extract-core
 *
 * @return void
 */
	public function getOptionParser() {
		$parser = AppShell::getOptionParser();
		return $parser->description(__d('cake_console', 'CakePHP Language String Extraction:'))
			->addOption('app', array('help' => __d('cake_console', 'Directory where your application is located.')))
			->addOption('paths', array('help' => __d('cake_console', 'Comma separated list of paths.')))
			->addOption('output', array('help' => __d('cake_console', 'Full path to output directory.')))
			->addOption('files', array('help' => __d('cake_console', 'Comma separated list of files.')))
			->addOption('exclude-plugins', array(
				'boolean' => true,
				'default' => true,
				'help' => __d('cake_console', 'Ignores all files in plugins if this command is run inside from the same app directory.')
			))
			->addOption('plugin', array(
				'help' => __d('cake_console', 'Extracts tokens only from the plugin specified and puts the result in the plugin\'s Locale directory.')
			))
			->addOption('exclude', array(
				'help' => __d('cake_console', 'Comma separated list of directories to exclude.' .
					' Any path containing a path segment with the provided values will be skipped. E.g. test,vendors')
			))
			->addOption('overwrite', array(
				'boolean' => true,
				'default' => false,
				'help' => __d('cake_console', 'Always overwrite existing .pot files.')
			));
	}
/**
 * Extract tokens out of all files to be processed
 *
 * @return void
 */
	protected function _extractTokens() {
		foreach ($this->_files as $file) {
			$this->_file = $file;
			$this->_tokens = array();
			$this->out(__d('cake_console', 'Processing %s...', $file));

			$filename_parts = explode('.', $file);
			switch (end($filename_parts)) {
				case 'js':
					// we have a javascript file to parse
					$this->_extractTokensFromJsFile($file);
					break;
				case 'html':
					// we have a html file to parse
					$this->_extractTokensFromHtmlFile($file);
					break;
				default:
					// we have a template/php file to parse
					$this->_extractTokensFromPhpFile($file);
					break;
			} //eo switch

			$this->_parse('Bancha.t', array('singular')); // currently we doesn't support domains
			$this->_parse('Bancha.Localizer.getLocalizedString', array('singular')); // currently we doesn't support domains
		}
	}
/**
 * Extract tokens out of a js file
 *
 * @return void
 */
	protected function _extractTokensFromJsFile($file) {
		$code = file_get_contents($file);
		$this->_tokens[] = array(T_INLINE_HTML, $code, /* document starts with line number 1 */1); // this is just one snippet
	}
/**
 * Extract tokens out of a html file
 *
 * @return void
 */
	protected function _extractTokensFromHtmlFile($file) {
		$html = new DOMDocument();
		if(!@$html->loadHTMLFile($file)) {
			exit('The PHP DOMDocument class could not read file '.$file);
		}
		$elements = $html->getElementsByTagName('script');
		if (!is_null($elements)) {
			foreach ($elements as $element) {
				$this->_tokens[] = array(T_INLINE_HTML, $element->nodeValue, /* we are not aware of the line number*/false); // just add this script
			}
		}
	}
/**
 * Extract tokens out of a template or php file
 *
 * @return void
 */
	protected function _extractTokensFromPhpFile($file) {
		$code = file_get_contents($file);
		$allTokens = token_get_all($code);

		$this->_tokens = array();
		foreach ($allTokens as $token) {
			// ignore all operators, and just include strings and html tokens
			if (is_array($token) && ($token[0] == T_CONSTANT_ENCAPSED_STRING || $token[0] == T_INLINE_HTML)) {
				// add just the string, if not inline html we need to un-escape it first
				$token[0] = $token[0]==T_CONSTANT_ENCAPSED_STRING ? $this->_formatString($token[1]) : $token[1];
				$this->_tokens[] =  $token;
			}
		}
		unset($allTokens);
	}

/**
 * Finds a string inside a code and returns the string and the remaining string part
 *
 */
	public function _findString($code) {
		$originalCode = $code;

		// check if it really looks like a string
		if($code[0]!='"' && $code[0]!="'") {
			$this->_markerError($this->_file, substr($code, 0, 100),
				__d('cake_console', 'Expected a string, but instead saw '.substr($code,0,20)), $code);
			return new Bancha_JavaScriptToken('error', false, $originalCode);
		}

		// cut the single or double quote off
		$start_quote = $code[0];
		$code = substr($code, 1);

		// found the end
		$string = $start_quote; // the string starts with the quote
		$position = 0;
		$foundEnd = false;

		while (!$foundEnd) {

			// find the possible string end
			$endPosition = strpos($code, $start_quote);

			// make sure there is an end
			if($endPosition === FALSE) {
				return new Bancha_JavaScriptToken('error', false, $originalCode);
			}

			// is it really a end or is it escaped?
			// we might have an end here, make sure it is not the escaped one
			$foundEscapes = 0;
			$position = $endPosition-1;
			// collect the number of escape strings before
			while($position>=0 && $code[$position]=='\\') {
				$foundEscapes++;
				$position--;
			}
			// it must be an even number, otherwise the end string is escaped
			$foundEnd = ($foundEscapes%2 == 0);

			// put that subpart into the string
			$string .= substr($code, 0, $endPosition+1);
			$code = substr($code, $endPosition+1);
		}

		// get the real content of the string
		$string = $this->_formatString($string);

		// check if there maybe now is a concatination
		$code = ltrim($code);
		if(substr($code,0,1) == '+') {
			// there is another token, collect this (recursive)
			$code = ltrim(substr($code,1));
			$token = $this->_collectJsToken($code);
			if(!$token->isString()) {
				$this->err(__d('cake_console', '<warning>The translation function is called with a variable, near '.substr($originalCode,0,100).'</warning>'));
			}
			$string .= $token->getStringValue();
			$code = $token->getRemainingCode();
		}

		// we have an end
		return new Bancha_JavaScriptToken('string', $string, $code);
	}

/**
 * Finds a string inside a code and returns the string and the remaining string part
 *
 */
	public function _findVariable($code) {
		// just find the next whitespace or , or ) or ;
		$pos = strpos($code, ' ');
		$pos = ($pos===FALSE || ($pos>strpos($code, ',') && strpos($code, ',')!==FALSE)) ? strpos($code, ',') : $pos;
		$pos = ($pos===FALSE || ($pos>strpos($code, ')') && strpos($code, ')')!==FALSE)) ? strpos($code, ')') : $pos;
		$pos = ($pos===FALSE || ($pos>strpos($code, '}') && strpos($code, '}')!==FALSE)) ? strpos($code, '}') : $pos;
		$pos = ($pos===FALSE || ($pos>strpos($code, ';') && strpos($code, ';')!==FALSE)) ? strpos($code, ';') : $pos;

		// make sure there is an end
		if($pos === FALSE) {
			// there is no variable name end, return error
			return new Bancha_JavaScriptToken('error', false, $code);
		}

		return new Bancha_JavaScriptToken('variable', substr($code, 0, $pos), substr($code, $pos));
	}
/**
 * Collects a string or variable
 */
	public function _collectJsToken($code) {
		$code = ltrim($code);
		$character = substr($code,0,1);

		if($character=='"' || $character=="'") { // string
			return $this->_findString($code);
		}
		if($character=='[') { // array of strings
			// collect all strings
			$strs = array();
			$token = $this->_findString(ltrim(substr($code,1)));
			$originalCode = $code;
			while($token->isString()) {
				$strs[] = $token->getStringValue();

				// expect a comma now
				if(substr($token->getRemainingCode(),0,1) != ',') {
					// this was the last argument
					break;
				}

				// there is another string to collect
				$token = $this->_findString(ltrim(substr($token->getRemainingCode(),1)));
			}

			// the loop might ended because there was a variable, we can't handle that
			if($token->isVariable() || $token->isTernary()) {
				return new Bancha_JavaScriptToken('error', false, $originalCode);
			}

			// now expect a closing array sign followed by a join
			if(substr($token->getRemainingCode(),0,1) != ']') {
				// missing array end
				return new Bancha_JavaScriptToken('error', false, $originalCode);
			}
			// now expect a join
			$code = ltrim(substr($token->getRemainingCode(),1));
			$validJoin = substr($code,0,1) == '.'; // check for point
			$code = ltrim(substr($code,1));
			$validJoin = $validJoin && substr($code,0,4) == 'join'; // check for join
			$code = ltrim(substr($code,4));
			$validJoin = $validJoin && substr($code,0,1) == '('; // check for join
			$code = ltrim(substr($code,1));
			$concatBy = ',';
			if(substr($code,0,1) != ')') {
				// the array is concatinated by a string
				$token = $this->_findString($code);
				if($token->isError()) {
					// something went wrong
					return new Bancha_JavaScriptToken('error', false, $originalCode);
				}
				$concatBy = $token->getStringValue();
				$code = $token->getRemainingCode();
			}
			$validJoin = $validJoin && substr($code,0,1) == ')'; // check for join end
			$code = ltrim(substr($code,1));

			if(!$validJoin) {
				return new Bancha_JavaScriptToken('error', false, $originalCode);
			}

			// return a token for the concatinated array
			return new Bancha_JavaScriptToken('string', implode($concatBy, $strs), $code);
		}
		if(preg_match('/[a-zA-Z]/', $character) === 1) { // variable
			return $this->_findVariable($code);
		}
		// there is some kind of operator here
		return new Bancha_JavaScriptToken('error', false, $code);
	}

	public function _collectJsArgument($code) {
		$result = $this->_collectJsToken($code);

		// check if it might is a ternary
		if(substr($result->getRemainingCode(),0,1) != '?') {
			return $result;
		}

		// handle ternary
		$first = $this->_collectJsToken(ltrim(substr($result->getRemainingCode(),1)));

		// expect a double quote
		if(substr($first->getRemainingCode(), 0, 1) !== ':') {
			$this->_markerError($this->_file, substr($code, 0, 100),
				__d('cake_console', 'Expected a ternary, but instead saw '.substr($first->getRemainingCode(), 0, 5)), $code);
			return new Bancha_JavaScriptToken('error',false,$code);
		}

		$second = $this->_collectJsToken(substr($first->getRemainingCode(),1));

		// build the new ternary array
		return new Bancha_JavaScriptToken(
			'ternary',
			array(
				$first->isString() ? $first->getStringValue() : false,
				$second->isString() ? $second->getStringValue() : false,
				),
			$second->getRemainingCode());
	}

/**
 * Parse tokens
 *
 * @param string $functionName Function name that indicates translatable string, will be tokenized
 * @param array $map Array containing what variables it will find (e.g: domain, singular, plural)
 * @return void
 */
	protected function _parse($functionName, $map) {

		foreach ($this->_tokens as $key => $token) {

			// find all occurrences of $functionName
			list($type, $string, $line) = $token;
			$occurrences = explode($functionName, $string);

			foreach ($occurrences as $position => $originalCode) {
				if($position==0) {
					//the first is not a match, but we want to keep it for possible error messages
					continue;
				}

				// keep track of the line
				if($line !== false) {
					// always add the number of new line breaks in the previous token
					// // to be at the current line number
					$breaks = str_replace("\r\n", "\r", $occurrences[$position-1]);
					$breaks = str_replace("\n", "\r", $breaks);
					$line += substr_count($breaks, "\r");
				}

				// get up to 20 chars before the code
				//$wholeCode = isset($occurrences[$position-1]) ? $occurrences[$position-1] : '';
				//$wholeCode = strlen($wholeCode)>20 ? '...'.substr($wholeCode, strlen($wholeCode-20)) : '';
				// get the function invocation
				$wholeCode = $functionName . (strlen($originalCode)>60 ? substr($originalCode, 0, 60) : $originalCode);
				// and some code afterwards if there's not much code yet
				if(strlen($wholeCode)<60) {
					$wholeCode .= $functionName;
					$wholeCode .= isset($occurrences[$position+1]) ? substr($occurrences[$position+1], 0, (60-strlen($wholeCode))) : '';
				}


				// parse code
				$code = trim($originalCode);

				// expect a opening parenthesis
				if(substr($code, 0, 1) !== '(') {
					// this is probably a check if the function exists in the environment, ignore it
					continue;
				}
				$code = substr($code, 1);

				// now expect a string or a variable
				$arguments = array(
					$this->_collectJsArgument($code));

				// collect additional arguments
				while(!end($arguments)->isError() && substr(end($arguments)->getRemainingCode(),0,1) == ',') {
					// there is another argument
					$arguments[] = $this->_collectJsArgument(substr(end($arguments)->getRemainingCode(),1));
				}

				/* Add support for nested calls
				// it might be that this occures because the translatiosn are nested, try to fix this
				for($pos = $position+1; $pos<count($occurrences); $pos++) {
					if(end($arguments)->isError()) {
						// we couldn't cleanly read last arguments, try again
						$code = end($arguments)->getRemainingCode();
						array_pop($arguments); // remove latest element
						...
				 */


				// error collecting the arguments
				if(end($arguments)->isError()) {
					$this->_markerError($this->_file, $line,
						__d('cake_console', 'Expected strings or variables as arguments for %s, instead saw "%s"', $functionName, substr($code,0,20)), $wholeCode);
					continue;
				}


				// just after the arguments
				$code = end($arguments)->getRemainingCode();

				// expect a closing brace
				if(substr($code, 0, 1) !== ')') {
					$this->_markerError($this->_file, $line,
						__d('cake_console', 'Expected an closing braces after %s, instead saw "%s"', $functionName, substr($code,0,10)), $wholeCode);
					continue;
				}

				// check if the arguments make sense

				// if there is a variable we can't collect it
				if($arguments[0]->isVariable()) {
					$this->out(__d('cake_console',
						'<warning>'.$functionName." is called with a variable/statement, we can't collect this.\n".
						'Error happend near: '.substr($wholeCode,0,100).'</warning>'));
					continue;
				}
				// check against errors
				foreach ($arguments as $argument) {
					if($argument->isError()) {
						$this->_markerError($this->_file, $line,
							__d('cake_console', 'Could not read the function, code looked malformed.'), $wholeCode);
						continue;
					}
				}


				// check strings if possible %s match given arguments
				// if($arguments[0])
				// TODO $replacements = preg_match_all("%s", $singular, $matches);

				// add the translation

				// collect the domain later
				$domain = 'bancha';

				// add translation
				$details = array(
					'file' => $this->_file,
					'line' => $line,
				);
				//$details['msgid_plural'] = $plural;

				// the _addTranslation signature changed between CakePHP 2.3.8 to 2.4.0
				// we want to support both
				if(substr(Configure::version(), 2, 3) < 4) {
					// CakePHP 2.0 - 2.3
					if($arguments[0]->isString()) {
						$this->_addTranslation($domain, $arguments[0]->getStringValue(), $details);
					} else if($arguments[0]->isTernary()) {
						$this->_addTranslation($domain, $arguments[0]->getTernaryFirstValue(), $details);
						$this->_addTranslation($domain, $arguments[0]->getTernarySecondValue(), $details);
					}
				} else {
					// CakePHP 2.4+
					if($arguments[0]->isString()) {
						$this->_addTranslation('LC_MESSAGES', $domain, $arguments[0]->getStringValue(), $details);
					} else if($arguments[0]->isTernary()) {
						$this->_addTranslation('LC_MESSAGES', $domain, $arguments[0]->getTernaryFirstValue(), $details);
						$this->_addTranslation('LC_MESSAGES', $domain, $arguments[0]->getTernarySecondValue(), $details);
					}
				}
			}
		}
	}


/**
 * Indicate an invalid marker on a processed file
 *
 * @param string $file File where invalid marker resides
 * @param integer $line Line number
 * @param string $marker Marker found
 * @param integer $count Count
 * @return void
 */
	protected function _markerError($file, $line, $errorMsg, $code) {
		$this->err(__d('cake_console', "<warning>Invalid marker content in %s:%s</warning>\n%s", $file, $line, $errorMsg), true);
		$this->err(__d('cake_console', "Error happend near: %s\n\n", $code), true);
	}

/**
 * Search files that may contain translatable strings
 * Same code as ExtractTask.php except that it also includes html and js files
 * @return void
 */
	protected function _searchFiles() {
		$pattern = false;
		if (!empty($this->_exclude)) {
			$exclude = array();
			foreach ($this->_exclude as $e) {
				if (DS !== '\\' && $e[0] !== DS) {
					$e = DS . $e;
				}
				$exclude[] = preg_quote($e, '/');
			}
			$pattern = '/' . implode('|', $exclude) . '/';
		}
		foreach ($this->_paths as $path) {
			$Folder = new Folder($path);
			$files = $Folder->findRecursive('.*\.(php|ctp|thtml|inc|tpl|html|js)', true); // <--- changes: added html&js
			if (!empty($pattern)) {
				foreach ($files as $i => $file) {
					if (preg_match($pattern, $file)) {
						unset($files[$i]);
					}
				}
				$files = array_values($files);
			}
			$this->_files = array_merge($this->_files, $files);
		}
	}
}

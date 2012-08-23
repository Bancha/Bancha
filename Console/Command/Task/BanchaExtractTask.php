<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * @package       Bancha
 * @subpackage    Console
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 1.0.1
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('ExtractTask', 'Console/Command/Task');
if(!class_exists('JavaScriptStringCollector')) {
	App::import('File','JavaScriptStringCollector', array('search' => App::path('Vendor','Bancha'), 'file' => 'Jsi18nShellParsingHelper' . DS . 'JavaScriptStringCollector.php'));
}

/**
 * Language string extractor for Bancha.t translations
 *
 * @package       Bancha.Console.Command.Task
 */
class BanchaExtractTask extends ExtractTask {

	public $exclude = array();
/**
 * Merge all domains string into the default.pot file
 *
 * @var boolean
 */
	protected $_merge = false;
/**
 * Don't extract validation messages
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
		if (!empty($this->params['exclude'])) {
			$this->_exclude = explode(',', $this->params['exclude']);
		}

		// exclude Bancha plugins
		array_push($this->_exclude, App::pluginPath('Bancha') . DS . 'Test' . DS . 'JavaScriptUnitTest');

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
			$this->out(__d('cake_console', 'Processing %s...', $file));

			$code = file_get_contents($file);
			$tokenizer = new JavaScript_Tokenizer($code);

			$this->_tokens = $tokenizer->tokenize();

			$this->_parse('Bancha.t', array('singular')); // currently we doesn't support domains
			$this->_parse('Bancha.Localizer.getLocalizedString', array('singular')); // currently we doesn't support domains
		}
	}

/**
 * Parse tokens
 *
 * @param string $functionName Function name that indicates translatable string, will be tokenized
 * @param array $map Array containing what variables it will find (e.g: domain, singular, plural)
 * @return void
 */
	protected function _parse($functionName, $map) {
		$count = 0;
		$tokenCount = count($this->_tokens);

		// tokenize function name
		$tokenizer = new JavaScript_Tokenizer($functionName);
		$tokenPattern = $tokenizer->tokenize();

		// last token is always eof, so remove that one
		array_pop($tokenPattern);

		if(count($tokenPattern)==0 || $tokenPattern[count($tokenPattern)-1]->type!='name') {
			throw new Exception('The supplied functionName to search seems to be malformed, got: '.$functionName);
		}
		$tokenPatternHead = array_shift($tokenPattern);
		$tokenPatternTail = $tokenPattern;

		// expect an opening parenthesis after the functon name
		array_push($tokenPatternTail, new JS_Token('punc','(',0,0,0,''));

		// run though all tokens
		do { // for each token

			// check against first needle part
			if($tokenPatternHead->type  == current($this->_tokens)->type &&
			   $tokenPatternHead->value == current($this->_tokens)->value) {
				// found new tokenizer start
				$match = true;
				foreach($tokenPatternTail as $pos => $needle) {
					if(next($this->_tokens)->type  != $needle->type ||
					   current($this->_tokens)->value != $needle->value) {
						$match = false;

						// go these number of steps back
						for($i=0;$i<$pos;$i++) {
							prev($this->_tokens);
						}

						// stop
						break;
					}
				}
				next($this->_tokens);

				if($match) {
					// found new match
					$collector = new JavaScriptStringCollector($this->_tokens);

					// the translatable string
					$singulars = array();
					try {
						$singulars = $collector->parse();
					} catch(JavaScriptStringCollectorGotVariableException $e) {
						$this->_markerError($this->_file, $functionName, $this->_tokens, $collector->getTokens(), 
								__d('cake_console', "Expected a string instead of a variable."));
						continue;
					}

					// jump to new current position
					$this->_tokens = $collector->getTokens();

					$additionalArguments = array();
					while(current($this->_tokens)->type == 'punc' && current($this->_tokens)->value == ',') {
						// found extra comma, so expect another string
						next($this->_tokens);

						// collect the string value/variable, but don't do anything with them (just for syntax checking)
						try {
							$collector = new JavaScriptStringCollector($this->_tokens,true);
							array_push($additionalArguments, $collector->parse());
							$this->_tokens = $collector->getTokens();
						} catch(JavaScriptStringCollectorParrserException $e) {
							$this->_markerError($this->_file, $functionName, $this->_token, $collector->getTokens());
						}
					}

					// check strings if possible %s match given arguments
					// TOD: $replacements = preg_match_all("%s", $singular, $matches);

					// collect the domain later
					$domain = 'bancha';

					// add translation
					$details = array(
						'file' => $this->_file,
						'line' => current($this->_tokens)->line+1,
					);
					//$details['msgid_plural'] = $plural;
					foreach($singulars as $singular) {
						$this->_addTranslation($domain, $singular, $details);
					}

					// expect a closing parenthesis now
					if(current($this->_tokens)->type != 'punc' || current($this->_tokens)->value != ')') {
						$this->_markerError($this->_file, $functionName, $this->_token, false, 
									__d('cake_console', "Expected closing parenthesis."));
					}
					next($this->_tokens);
				}

			}
		} while(next($this->_tokens) !== false && current($this->_tokens)->type != 'eof');
	}

/**
 * Indicate an invalid marker on a processed file
 *
 * @param string $file File where invalid marker resides
 * @param string $marker Marker found
 * @param integer $startTokens The tokens with the current start token
 * @param integer $endTokens The tokens with the current end token or false
 * @param integer $errorMsg error text
 * @return void
 */
	protected function _markerError($file, $marker, $startTokens, $endTokens=false, $errorMsg=false) {
		$this->out('<warning>'.__d('cake_console', "Invalid marker content in %s:%s\n* %s(", $file, current($startTokens)->line, $marker).'</warning>', true);
		
		// if start and end is availabel write code between
		if($endTokens) {
			$this->out(__d('cake_console', "Last token here broke it: %s(",$marker),0);
			$this->out(current($startTokens)->value,0);
			while(next($startTokens) !== current($startTokens)) {
				$this->out(current($startTokens)->value,0);
			}
			$this->out(current($startTokens)->value,0);
		} else {
			// just write a few tokens
			$this->out(__d('cake_console', "Near: %s(",$marker),0);
			for($i=0; $i<5; $i++) {
				$this->out(current($startTokens)->value,0);
			}
		}
		$this->out("\n", false);

		if($errorMsg) {
			$this->out(__d('cake_console', "Reason: %s\n",$errorMsg), true);
		}
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
			$files = $Folder->findRecursive('.*\.(html|js)', true); // <--- changes
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

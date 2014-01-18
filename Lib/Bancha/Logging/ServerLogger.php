<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Lib.Bancha.Routing
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 2.0.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('PiwikTracker', 'Bancha.Bancha/Logging');

/**
 * ServerLogger
 *
 * @package       Bancha.Lib.Bancha.Logging
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */
class ServerLogger {

/**
 * Log an error to the Bancha developers.
 *
 * To find bugs more easily and fix them fast, if this feature is activated,
 * Bancha provides exceptions to the Bancha developers, including environment
 * informations like the PHP and CakePHP version, but without any data.
 *
 * To disable it, please add to your core.php
 *
 *     Configure::write('Bancha.ServerLogger.logIssues', false);
 *
 * @param string    $signature The controller invokation signature
 * @param Exception $exception The caugth exception
 * @return void
 * @since  Bancha v 2.0.0
 */
	public static function logIssue($signature, Exception $exception) {
		if (!Configure::read('Bancha.ServerLogger.logIssues')) {
			return; // don't log
		}

		$type = get_class($exception);
		if ( $type == 'CacheException' ||
			$type == 'ConfigureException' ||
			$type == 'MethodNotAllowedException' ||
			$type == 'NotFoundException' ||
			$type == 'BanchaAuthAccessRightsException' ||
			$type == 'BanchaAuthLoginException' ||
			$type == 'BanchaException' ||
			$type == 'BanchaRedirectException' ||
			in_array(get_class($exception), Configure::read('Bancha.passExceptions')) || // this is an expected exception
			class_exists('CakeTestSuiteDispatcher')) {
			return; // exception seem to be legit and not a Bancha error
		}

		// we are not interested in any data!
		$signature = substr($signature, 0, strpos($signature, '('));
		$msg = $signature . ' has caused ' . $type .
				$exception->getMessage() . ' in file ' . $exception->getFile() .
				' on line ' . $exception->getLine();

		self::_log('exception', $msg);
	}

/**
 * Log environment information to the Bancha developers.
 *
 * To get a better idea what server environments are the most important
 * to test and when features we should implement next, if this feature
 * is activated, Bancha will provide usage information, including environment informations
 * informations like the PHP and CakePHP version, but without any data.
 *
 * To disable it, please add to your core.php
 *
 *     Configure::write('Bancha.ServerLogger.logEnvironment', false);
 *
 * @since  Bancha v 2.0.0
 * @return void
 */
	public static function logEnvironment() {
		if (!Configure::read('Bancha.ServerLogger.logEnvironment')) {
			return; // don't log
		}

		self::_log('usage', 'Bancha Usage');
	}

/**
 * The underlying log function.
 *
 * @param string $type The type of message to log
 * @param string $msg  The message to send
 * @return void
 * @since  Bancha v 2.0.0
 */
	protected static function _log($type, $msg) {
		// repress all possible exceptions
		try {
			// create the tracker
			$t = new PiwikTracker($idSite = 6, 'http://environments.banchaproject.org/');
			$t->setUrl('http://environments.banchaproject.org/' . $type);

			// get datasource name
			$datasource = '';
			if (class_exists('DATABASE_CONFIG')) {
				$config = new DATABASE_CONFIG();
				if (isset($config->default['datasource'])) {
					$datasource = $config->default['datasource'];
				}
			}

			// set environment variables
			$host = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : (!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : false);
			if (!$host) {
				return; // no server info found, probably we are in the shell
			}

			$pos = strpos($_SERVER['REQUEST_URI'], '?_dc='); // used for cache busting
			$path = ($pos === -1) ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], 0, $pos);
			$t->setCustomVariable(1, 'HTTP_HOST', $host);
			$t->setCustomVariable(2, 'APP', $host . $path);
			$t->setCustomVariable(3, 'PHP_VERSION', phpversion());
			$t->setCustomVariable(4, 'BANCHA_VERSION', Configure::read('Bancha.version'));
			$t->setCustomVariable(5, 'CAKE_VERSION',
				Configure::version() .
				', mode: ' . Configure::read('debug') .
				' with ' . $datasource);

			// log it
			$t->doTrackPageView(urlencode($msg));

		} catch(Exception $e) {
			// something wen't wrong
		}
	}
}

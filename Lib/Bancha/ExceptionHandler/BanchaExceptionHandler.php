<?php
/**
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.Lib.Bancha.ExceptionHandler
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */


/**
 * BanchaExceptionHandler.
 *
 * @package       Bancha.Lib.Bancha.ExceptionHandler
 * @author        Kung Wong <kung.wong@gmail.com>
 */
class BanchaExceptionHandler extends Object {

/**
 * Handles an expection for Bancha requests.
 * 
 * @param Exception $e The exception that occured
 * @return void
 */
	public function handleException(Exception $e) {
		// first log exception
		$config = Configure::read('Exception');
		if (!empty($config['log'])) {
			$message = sprintf(
				"[%s] %s\n%s",
				get_class($e),
				$e->getMessage(),
				$e->getTraceAsString()
			);
			CakeLog::write(LOG_ERR, $message);
		}

		/* TODO: initialize renderer ?
		 * see lib/cake/errorhandler.php
		 * */

		//echo "testing the exception: " . $e;
		//throw new Exception('TestException: ');
	}

}

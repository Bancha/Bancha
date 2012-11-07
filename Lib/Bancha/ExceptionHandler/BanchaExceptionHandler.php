<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @subpackage    Lib.ExceptionHandler
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */


/**
 * BanchaExceptionHandler.
 *
 * @package    Bancha
 * @subpackage Lib.ExceptionHandler
 * @author     Kung Wong
 */
class BanchaExceptionHandler extends Object {

	public function handleException(Exception $e) {
		// first log exception
		$config = Configure::read('Exception');
		if(!empty($config['log'])) {
			$message = sprintf("[%s] %s\n%s",
						   get_class($e),
						   $e->getMessage(),
						   $e->getTraceAsString()
					  	);
			CakeLog::write(LOG_ERR, $message);
		}

		/** TODO: initialize renderer ?
		 * see lib/cake/errorhandler.php
		 * */

		//echo "testing the exception: " . $e;
		//throw new Exception('TestException: ');
	}

}

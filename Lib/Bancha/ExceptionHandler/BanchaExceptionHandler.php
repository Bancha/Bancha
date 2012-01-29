<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @subpackage    Lib.ExceptionHandler
 * @copyright     Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
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

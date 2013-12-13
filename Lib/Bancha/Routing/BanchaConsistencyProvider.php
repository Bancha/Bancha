<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha.Lib.Bancha.Routing
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 2.3.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * BanchaConsistencyProvider
 *
 * @package    Bancha
 * @subpackage Lib.Routing
 */
class BanchaConsistencyProvider {
/**
 * the folder name to save the client_ids for consistency to
 */
	private $folder = null;

/**
 * the current client id for consistency
 */
	private $clientId = null;

/**
 * the currents request tid, set during validates()
 */
	private $tid = null;

/**
 * Constructor.
 *
 * @param string $folder the folder to save consistany ids in, false to use default
 */
	public function __construct($folder = false) {
		$this->folder = $folder ? $folder : (TMP . 'bancha-clients');
	}

/**
 * Checks if it is appropriate to execute this request now
 *
 * @param $clientId the client if for this request
 * @param $tid the tid of the request to execute
 * @return true to execute the request
 */
	public function validates($clientId, $tid) {
		$this->clientId = $clientId;
		$tid = intval($tid);

		// Check if we have an old id from this client
		$savedTid = $this->getTid();

		// Check if another request is currently processing
		if($savedTid == 'x') {
			// yes, so skip this request
			$this->tid = false;
			return false;
		}

		// Check if the request wasn't yet handled
		if ($savedTid && $savedTid>=$tid) {
			// this request was already handled, so discard it
			$this->tid = false;
			return false;
		}

		// Since Ext.Direct will always send all open requests and these are ordered by tid
		// we don't need to worry that when one request was finished (e.g. tid 1) and the new one has a higher tid
		// (e.g. tid 4) there is a tid missing in the middle (tid 2 and 3 must have been without a clientId). 
		// Since not all requests need to be requests with a clientId it is fine when numbers in the middle are 
		// missing.

		// Set the tid to 'x', so that no other request can be handled in parallel
		$this->saveTid('x');

		// keep a local reference for finalizing
		$this->tid = $tid;

		// process it
		return true;
	}

/**
 * Mark that this request has been finished and next can be executed.
 * $tid is read from previous used validates();
 *
 * @return void
 */
	public function finalizeRequest() {
		if($this->tid === null) {
			return $this->handleError('Bancha internal error, executed BanchaConsistencyProvider::finalizeRequest() before BanchaConsistencyProvider::validates()!');
		}

		if($this->tid == false) {
			// this request was skipped, so nothing to do here
			return;
		}

		// request was executed, so now the next tid can be executed
		$this->saveTid($this->tid);

		return;
	}





	// helper functions

/**
 * convenience method to get the file name where the client ids are saved
 */
	public function getFileName() {
		return $this->folder . DS . $this->clientId . '.txt';
	}

/**
 * Looks if an old tid from this client exists.
 * @return null|string|integer If another process is currently working it returns 'x', otherwise the last finished tid, or null if no tid was saved yet.
 */
	public function getTid() {
		if (file_exists($this->getFileName())) {
			$tid = trim(file_get_contents($this->getFileName()));
			return $tid=='x' ? $tid : intval($tid);
		}
		return null;
	}

/**
 * Saves a tid to the client file (overwrite olds)
 * @param $tid the tid to save
 * @return in case of an error it returns false, otherwise true
 */
	public function saveTid($tid) {

		// if it doesn't exist, create a folder to save all the client_ids
		if(!is_dir($this->folder)) { // this can make problems under windows, see https://bugs.php.net/bug.php?id=39198
			if (!@mkdir($this->folder)) {
				// error handling
				return $this->handleError("Bancha was not able to create a tmp dir for saving Bancha consistency client ids, the path is: ". $client_folder);
			}
		}

		if(false === file_put_contents($this->getFileName(), $tid)) {
			// error handling
			return $this->handleError('Bancha could not write client tid for consistency to the file: '. $this->getFileName());
		}
	}

/**
 * Handles write errors from the filesystem
 * @return false
 */
	public function handleError($msg) {
		CakeLog::write('error', $msg);
		if(Configure::read('debug')>0) {
			throw new CakeException($msg);
		}
		return false;
	}
}

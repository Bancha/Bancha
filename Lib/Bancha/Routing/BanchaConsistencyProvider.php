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
	protected $_folder = null;

/**
 * the current client id for consistency
 */
	protected $_clientId = null;

/**
 * the currents request tid, set during validates()
 */
	protected $_tid = null;

/**
 * Constructor.
 *
 * @param string $folder the folder to save consistany ids in, false to use default
 */
	public function __construct($folder = false) {
		$this->_folder = $folder ? $folder : (TMP . 'bancha-clients');
	}

/**
 * Checks if it is appropriate to execute this request now
 *
 * @param string $clientId The client if for this request
 * @param string $tid      The tid of the request to execute
 * @return boolean         True to execute the request
 */
	public function validates($clientId, $tid) {
		$this->_clientId = $clientId;
		$tid = intval($tid);
		$this->_tid = $tid;

		// Check if we have an old id from this client
		$savedTid = $this->getTid();

		// Check if the request was already handled
		if ($savedTid && $savedTid >= $tid) {
			// this request was already handled, so discard it
			$this->_tid = false;
			return false;
		}

		// Check if another request is currently processing
		while ($savedTid == 'x') {
			// yes, so wait for our turn
			// Note: This is currently a super simple implementation
			// it would be better to add the request to a ACID database
			// and retrieve it later.
			sleep(0.3);
			// retrieve again
			$savedTid = $this->getTid();
		}

		// keep a local reference for finalizing
		$this->_tid = $tid;

		// Since Ext.Direct will always send all open requests and these are ordered by tid
		// we don't need to worry that when one request was finished (e.g. tid 1) and the new one has a higher tid
		// (e.g. tid 4) there is a tid missing in the middle (tid 2 and 3 must have been without a clientId).
		// Since not all requests need to be requests with a clientId it is fine when numbers in the middle are
		// missing.

		// Set the tid to 'x', so that no other request can be handled in parallel
		$this->saveTid('x');

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
		if ($this->_tid === null) {
			return $this->handleError('Bancha internal error, executed BanchaConsistencyProvider::finalizeRequest() before BanchaConsistencyProvider::validates()!');
		}

		if ($this->_tid == false) {
			// this request was skipped, so nothing to do here
			return;
		}

		// request was executed, so now the next tid can be executed
		$this->saveTid($this->_tid);
	}

	// ######################################
	// ######################################
	// ####      helper functions        ####
	// ######################################
	// ######################################

/**
 * convenience method to get the file name where the client ids are saved
 *
 * @return string The path to the file
 */
	public function getFileName() {
		return $this->_folder . DS . $this->_clientId . '.txt';
	}

/**
 * Looks if an old tid from this client exists.
 * 
 * @return null|string|integer If another process is currently working it returns 'x',
 *                             otherwise the last finished tid, or null if no tid was saved yet.
 */
	public function getTid() {
		if (file_exists($this->getFileName())) {
			$tid = trim(file_get_contents($this->getFileName()));
			return $tid == 'x' ? $tid : intval($tid);
		}
		return null;
	}

/**
 * Saves a tid to the client file (overwrite olds)
 * 
 * @param string $tid The tid to save
 * @return boolean    False if there's an error while saving, otherwise true
 */
	public function saveTid($tid) {
		// if it doesn't exist, create a folder to save all the client_ids
		if (!is_dir($this->_folder)) { // this can make problems under windows, see https://bugs.php.net/bug.php?id=39198
			if (!@mkdir($this->_folder)) {
				// error handling
				return $this->handleError(
					'Bancha was not able to create a tmp dir for saving Bancha ' .
					'consistency client ids, the path is: ' . $this->_folder
				);
			}
		}

		if (false === file_put_contents($this->getFileName(), $tid)) {
			// error handling
			return $this->handleError(
				'Bancha could not write client tid for consistency to the file: ' .
				$this->getFileName()
			);
		}
	}

/**
 * Handles write errors from the filesystem
 *
 * @param string $msg The error message to handle
 * @return false
 * @throws CakeException If in debug mode
 */
	public function handleError($msg) {
		CakeLog::write('error', $msg);
		if (Configure::read('debug') > 0) {
			throw new CakeException($msg);
		}
		return false;
	}
}

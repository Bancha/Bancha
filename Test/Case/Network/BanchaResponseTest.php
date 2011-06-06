<?php
/**
 * @copyright     Copyright 2011 Bancha Project
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('BanchaResponse', 'Bancha');
App::import('Lib','Bancha.Bancha.Network');

echo realpath(dirname(__FILE__) . '/../../../lib/Bancha') . "\n\n";

// TODO: UNNÖTIG?

set_include_path(realpath(dirname(__FILE__) . '/../../../lib/Bancha/') . PATH_SEPARATOR . get_include_path());
require_once 'Network/BanchaResponse.php';
/**
 * BanchaRequestTest
 *
 * @package bancha.libs
 */

class BanchaResponseTest extends CakeTestCase
{
    // TODO: test the addResponse function
    function testaddResponse() {
    	
    }
    
	// TODO: test the send function
	function testSend() {
		
	}
}
?>

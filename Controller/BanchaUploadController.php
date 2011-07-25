<?php

/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */


/**
 * Bancha Upload Controller
 * This class exports the ExtJS API of all other Controllers for use in ExtJS Frontends
 *
 * @author Kung Wong 
 */

class BanchaUploadController extends BanchaAppController {
	
	/**
	 * add method, saves the file in the database
	 *
	 * @return void
	 */
    function add() {
        if (!empty($this->data) &&
             is_uploaded_file($this->data['MyFile']['File']['tmp_name'])) {
            $fileData = fread(fopen($this->data['MyFile']['File']['tmp_name'], "r"),
                                     $this->data['MyFile']['File']['size']);

            $this->data['MyFile']['name'] = $this->data['MyFile']['File']['name'];
            $this->data['MyFile']['type'] = $this->data['MyFile']['File']['type'];
            $this->data['MyFile']['size'] = $this->data['MyFile']['File']['size'];
            $this->data['MyFile']['data'] = $fileData;

            $this->MyFile->save($this->data);

            $this->redirect('BanchaController/index');
        }
    }
	
    /**
	 * downloaad method, to retrieve files
	 *
	 * @return void
	 */
    function download($id) {
	    Configure::write('debug', 0);
	    $file = $this->MyFile->findById($id);
	
	    header('Content-type: ' . $file['MyFile']['type']);
	    //header('Content-length: ' . $file['MyFile']['size']);
	    header('Content-Disposition: attachment; filename="'.$file['MyFile']['name'].'"');
	    echo $file['MyFile']['data'];
	
	    exit();
	}
}

?>

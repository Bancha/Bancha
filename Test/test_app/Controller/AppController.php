<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	
	/**
	 * Use the BanchaPaginatorComponent to also support pagination
	 * and remote searching for Sencha Touch and ExtJS stores
	 */
	public $components = array('Session', 'Paginator' => array('className' => 'Bancha.BanchaPaginator'));

	/**
	 * from http://blog.mixable.de/cakephp-upload-von-dateien-und-grafiken-vereinfachen/ (in german)
	 * english source: http://www.jamesfairhurst.co.uk/posts/view/uploading_files_and_images_with_cakephp
	 */
	/**
	 * uploads files to the server
	 * @params:
	 *    $folder  = the folder to upload the files e.g. 'img/files'
	 *    $formdata   = the array containing the form files
	 *    $itemId  = id of the item (optional) will create a new sub folder
	 * @return:
	 *    will return an array with the success of each file upload
	 */
	function uploadFiles($folder, $formdata, $itemId = null) {
	   // setup dir names absolute and relative
	   $folder_url = WWW_ROOT.$folder;
	   $rel_url = $folder;

	   // create the folder if it does not exist
	   if(!is_dir($folder_url)) {
	      mkdir($folder_url);
	   }

	   // if itemId is set create an item folder
	   if($itemId)
	   {
	      // set new absolute folder
	      $folder_url = WWW_ROOT.$folder.'/'.$itemId;
	      // set new relative folder
	      $rel_url = $folder.'/'.$itemId;
	      // create directory
	      if(!is_dir($folder_url)) {
	         mkdir($folder_url);
	      }
	   }

	   // list of permitted file types, this is only images but documents can be added
	   $permitted = array('image/gif','image/jpeg','image/pjpeg','image/png');

	   // loop through and deal with the files
	   foreach($formdata as $file)
	   {
	      // replace spaces with underscores
	      $filename = str_replace(' ', '_', $file['name']);
	      // assume filetype is false
	      $typeOK = false;
	      // check filetype is ok
	      foreach($permitted as $type)
	      {
	         if($type == $file['type']) {
	            $typeOK = true;
	            break;
	         }
	      }

	      // if file type ok upload the file
	      if($typeOK) {
	         // switch based on error code
	         switch($file['error']) {
	            case 0:
	               // check filename already exists
	               if(!file_exists($folder_url.'/'.$filename)) {
	                  // create full filename
	                  $full_url = $folder_url.'/'.$filename;
	                  $url = $rel_url.'/'.$filename;
	                  // upload the file
	                  $success = move_uploaded_file($file['tmp_name'], $url);
	               } else {
	                  // create unique filename and upload file
	                  ini_set('date.timezone', 'Europe/London');
	                  $now = date('Y-m-d-His');
	                  $full_url = $folder_url.'/'.$now.$filename;
	                  $url = $rel_url.'/'.$now.$filename;
	                  $success = move_uploaded_file($file['tmp_name'], $url);
	               }
	               // if upload was successful
	               if($success) {
	                  // save the url of the file
	                  $result['urls'][] = $url;
	               } else {
	                  $result['errors'][] = "Error uploaded $filename. Please try again.";
	               }
	               break;
	            case 3:
	               // an error occured
	               $result['errors'][] = "Error uploading $filename. Please try again.";
	               break;
	            default:
	               // an error occured
	               $result['errors'][] = "System error uploading $filename. Contact webmaster.";
	               break;
	         }
	      } elseif($file['error'] == 4) {
	         // no file was selected for upload
	         $result['nofiles'][] = "No file Selected";
	      } else {
	         // unacceptable file type
	         $result['errors'][] = "$filename cannot be uploaded. Acceptable file types: gif, jpg, png.";
	      }
	   }
		return $result;
	}
}

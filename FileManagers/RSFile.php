<?php
/**
 * Base File Management Class
 *
 * @package		CJCI
 * @subpackage	StorageLibrary
 * @category	CodeIgniter Library
 * @author		Carlos Jimenez Guirao
 * @link		http://WillWriteThisSoon.todo
 * 
 */

namespace CJCI\ResStorage\FileManagers;
use Exception, CJCI\ResStorage\ResStorage;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * RSFile
 * Base File Management Class
 *
 * @package		CJCI
 * @subpackage	StorageLibrary
 * @category	CodeIgniter Library
 * @author		Carlos Jimenez Guirao
 * @link		http://WillWriteThisSoon.todo
 * 
 */

class RSFile {

	/**
	 * File Metadata 
     * @var string $filename Filename
     * @var string $mimetype File Mime type
     * @var string $hash 	 File hash (md5)
     * @var string $uuid 	 UUID uniq id for file access
     * @var string $path 	 File Stored Base Path (location whitout filename);
     * @var integer $accessed Number of times that was accessed
     * @var string $stored 	 DateTime when file was stored.
     * @var string $lastaccess DateTime when last acces happened
     */
	public $filename, $mimetype, $hash, $uuid, $path, $accessed, $stored, $lastaccess;

	/**
	 * @access private
     * @var string $_orig_path 	 Original path used before storage.
     * @var string $storage_dir  Basedir where all stored files will land,
     */
	private $_orig_path,$storage_dir;
	
	/**
	 * __construct
	 *
	 * Sets BaseDir where all files will land
	 * 
	 * @param	string $storage_dir
	 * @return	void 	
	 *
	 */
	public function __construct() {
		$argv = func_get_args(); 
		$this->storage_dir = $argv[0];
	}

	/**
	 * store
	 *
	 * Initializes the object with given source file and stores a copy of it.
	 * 
	 * @param	string $origin
	 * @return	true|false 	
	 *
	 */
	public function store($origin){
		$this->_orig_path = $origin;
		if (!file_exists($origin)){
		 throw new Exception("File doesn't exist, or not readable: ".$origin);
		 return false;
		}
		$this->filename = basename($origin);
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$this->mimetype = finfo_file($finfo, $origin);
		finfo_close($finfo);
		$this->accessed = 0;
		$this->lastaccess = $this->stored = date('Y-m-d H:i:s');
		$this->hash = md5($this->filename);
		$this->uuid =  uniqid('', true);
		$chunks = str_split($this->hash,3);
		$this->path = $this->gen_path($this->hash);
		if (!is_readable($this->path)) ResStorage::create_path($this->path);
		if (!$this->copy($origin, $this->get_full_path())){
			throw new Exception('Unable to copy file, aborting.');
			return false;
		}
		return true;
	}

	/**
	 * initialize
	 *
	 * Initializes the object with given metadata to be ready to read data
	 * 		if it's able to reach the file returns true, else returns false.
	 * 
	 * @param	$metadata array
	 * @return	true|false 	
	 *
	 */
	public function initialize($metadata){
		foreach($metadata as $property => $data){
			if (property_exists($this, $property)){
				$this->$property = $data;
			}
		}
		if (is_readable($this->get_full_path())){ 
			return true;
		}else {
			throw new Exception('Unable to reach file with given metadata, check if file exists and is readable.');
			return false;
		}
	}

	/**
	 * copy
	 *
	 * Copy a file from source to destination
	 * 
	 * @param	string $origin
	 * @param	string $destination
	 * @return	true|false 	
	 *
	 */
	protected function copy($origin, $destination){
		if (!is_readable($origin)){
			throw new Exception('File not readable: '.$origin);
			return false;
		}
		if(!copy($origin, $destination)){
			throw new Exception('Unable to copy file.');
			return false;
		}
		return true;
	}

	/**
	 * file_get_contents
	 *
	 * Returns file contents to a string
	 * 
	 * @return	string
	 *
	 */
	public function file_get_contents(){
		return file_get_contents($this->get_full_path());
	}

	/**
	 * readfile
	 *
	 * Returns file contents to be downloaded
	 *
	 */
	public function readfile(){
		header('Content-Description: File Transfer');
	    header('Content-Type: '.$this->mimetype);
	    header('Content-Disposition: attachment; filename='.$this->filename);
	    header('Content-Transfer-Encoding: binary');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . filesize($this->get_full_path()));
	    ob_clean();
	    flush();
	    readfile($this->get_full_path());
	    exit;
	}

	/**
	 * delete
	 *
	 * Delete file
	 * 
	 * @return	void
	 *
	 */
	public function delete(){
		if (file_exists($this->get_full_path())){
			unlink($new_path.$hash.'.'.$uuid);
			return true;
		}
	}

	/**
	 * get_full_path
	 *
	 * Returns file location and name as a string
	 * 
	 * @return	string
	 *
	 */
	protected function get_full_path(){
		return $this->path.$this->hash.'.'.$this->uuid;
	}
	
	/**
	 * gen_path
	 *
	 * Returns file location using it's hash
	 * 
	 * @param  string $hash
	 * @return	string
	 *
	 */
	public function gen_path($hash){
		$chunks = str_split($this->hash,3);
		return $this->storage_dir.$chunks[0].DIRECTORY_SEPARATOR.$chunks[1].DIRECTORY_SEPARATOR.$chunks[2].DIRECTORY_SEPARATOR;
	}
}

// ------------------------------------------------------------------------

<?php
/**
 * File operations Class
 * 
 * @author Ryan "Tackleberry" Marshall
 * @author Alex "Lev" Kaye
 */
class Folder {
	private $folder;
	
	public function __construct($folder) {
		$this->folder = $folder;	
	}
	  
	public function list_files($extension) {
		$files = array();
		// Open a known directory, and proceed to read its contents
		if (is_dir($this->folder)) {
		    if ($dh = opendir($this->folder)) {
		        while (($file = readdir($dh)) !== false) {
					$get_extension = explode(".",$file);
					if (is_array($get_extension)) {
						print_r($get_extension);
						if ($get_extension[1] == strtoupper($extension) || $get_extension[1] == strtolower($extension)) {
							$files[] = $file;
						}						
					}
				}
				closedir($dh);
			}
		}
		return $files;
	}
}
?>
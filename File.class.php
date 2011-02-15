<?php
/**
 * File operations Class
 * 
 * @author Ryan "Tackleberry" Marshall
 * @author Alex "Lev" Kaye
 * @package file
 * @subpackage generic_operations
 */
class File implements Iterator {

	private $handle;
	private $file_name;
	private $fileString;
	private	$autoSave = true;
	private $separator;
	private $line_length;
	private $end_of_file;
	
	/**
	 * Construct
	 * 
	 * Setup error logger and open file
	 * 
	 * @param string $filename Name of file to work with
	 * @param string $mode File operations mode
	 * @link http://uk3.php.net/manual/en/function.fopen.php
	 * @param string $separator file field separator
	 * @param integer $line_length Length of line to fetch in bytes
	 */
	public function __construct($filename, $mode = 'w', $separator = ",", $line_length = '4096') {
		$this->file_name 	= $filename;
		$this->handle 		= fopen($filename, $mode) or $error = new Error(3, 'Error opening file: '. $this->file_name);
		$this->separator 	= $separator;
		$this->line_length 	= $line_length;
		fseek($this->handle, 0, SEEK_END);
		$this->end_of_file	= ftell($this->handle);
		$this->rewind();
	}

	/**
	 * Rewind file pointer to start of file
	 * Iterator interface method
	 */
    function rewind() {
        rewind($this->handle);
    }

	/**
	 * Get Current line
	 * Fetches current line and moves pointer back 1 position. 
	 * This is required so the iterator method next() will move the pointer to the correct record.
	 * Without this the pointer would have moved on 2 as as the fgets method used in readLineIntoArray automatically moves the pointer on 1 position.
	 * Iterator interface method
	 */
    function current() {
		$data = $this->readLineIntoArray($this->separator);
		fseek($this->handle, -$this->line_length, SEEK_CUR);
        return $data;
    }

	/**
	 * Return file pointer position 
	 * Iterator interface method
	 */
    function key() {
        return ftell($this->handle);
    }

	/**
	 * Move file pointer on one line
	 * Iterator interface method
	 */
    function next() {
        fseek($this->handle, $this->line_length, SEEK_CUR);
    }
	
	/**
	 * Checks for valid file pointer
	 * Iterator interface method
	 */
    function valid() {
		if (ftell($this->handle) >= $this->end_of_file) {
			return false;
		}
		return true;
    }
	
	/**
	 * Destruct - Close file
	 */
	public function __destruct() {
		fclose($this->handle);
	}

	/**
	 * Read a line from file into a data array
	 * 
	 * @param $handle file handle
	 * @param string $separator file field separator
	 * 
	 * @return array/boolean $data current line as data array
	 */
	public function readLineIntoArray() {
		if ($this->handle) {
		    $data = fgets($this->handle, $this->line_length);
		    $data = explode($this->separator, $data);
		    return $data;
		}
		return false;
	}
	
	/**
	 * Set autoSave status
	 * 
	 * @param boolean $autoSave
	 */
	public function setAutoSave($autoSave) {
		$this->autoSave = $autoSave;
	}
	/**
	 * Write a line to file
	 * 
	 * Write to file or save to $this->fileString if autoSave==false
	 * 
	 * @param string $string string to write   
	 */
	public function addLine($string) {
		if ($this->autoSave == true) {   
			@fwrite($this->handle, $string) or $error = new Error(3, 'Error writing to file: '. $this->file_name);		
		} else {
			$this->fileString .= $string;	
		}
	}
	/**
	 * Write a line to CSV
	 * 
	 * @param string $string string to write
	 * @param string $delimiter file field delimiter (default = ",")
	 * @param string $enclosure file field enclosure (default = '"')
	 */
	public function addRow($data, $delimiter = ",", $enclosure = '"') {
		if ($this->autoSave == true) {
			chown ($this->file_name, 'dbell');
			if (fputcsv($this->handle, $data, $delimiter, $enclosure) or $error = new Error(3, 'Error writing to file: '. $this->file_name));		
		} else {
			foreach ($data AS $fields) {
				$this->fileString .= $string;
			}
		}			
	}
	/**
	 * Print to Screen
	 * 
	 * Print $this-FileString to screen
	 */
	public function printToScreen() {
		if ($this->fileString) {
			print_r($this->fileString);
		} else {
			$error = new Error(1,'File string is empty!');
			$error->show();
		}
	}
	/**
	 * Replace \r\n, \r\, \n with <br />
	 * 
	 * @param string $string str to replace
	 * 
	 * @return string replacement string
	 */
	public function nl2brReplace($string) {
		return str_replace(array("\r\n", "\r", "\n"), '<br />', $string);
	}
}
?>
<?php
/**
 * AFrame
 * 
 * Strong, light and clever PHP application framework for PHP 5.3 upwards.
 * 
 * @pakage		AFrame
 * @version		1.0
 * @copyright	Alex Kaye 2010
 * @author		Alex Kaye <aframe@alexkaye.co.uk>
 * @link		http://aframe.alexkaye.co.uk
 * @license		http://aframe.alexkaye.co.uk/license
 */

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * Error Logger class
 * 
 * Class to log a group of Error instances
 *
 * @since		Version 1.0
 * @author		Alex Kaye <aframe@alexkaye.co.uk>
 */ 
class ErrorLogger {	

	/**
	 * A history of all errors triggered for this instance
	 */
	protected $history = array();

	
	/**
	 * Creates an error logger instance for logging Errors.
	 */
	public function __construct() {
		
	}
	
	/**
	 * Magic method which returns all errors in string format for lazy debugging.
	 */
	public function __toString() {
		// Use our getAsString method which returns the errors as a string
		return $this->getAsString() !== false ? $this->getAsString() : '';
	}
	
	/**
	 * Adds the given Error insatnce to our log
	 *
	 * @param	Error	$error			The instance of the Error class to add to the log
	 */
	public function log(Error $error) {
		// Add the error to our history
		$this->history[] = $error;
	}
	
	/**
	 * Retrieves an Error instance (for the given error ID) containing several properties useful for debugging.
	 *
	 * @param	integer		$id				ID of the error to retrieve (0 will be the first error, 3 will be the fourth etc.)
	 *
	 * @return	Error|false					Returns an Error instance if the error exists, false otherwise
	 */
	public function getError($id) {
		if(array_key_exists($id, $this->history)) {
			// Returns the specified Error instance from our history
			return $this->history[$id];
		} else {
			return false;
		}
	}
	
	/**
	 * Retrieves an Error instance for the last error generated, containing several properties useful for debugging.
	 *
	 * @return	Error|false					Returns an Error instance if the error exists, false otherwise
	 */
	public function getLastError() {
		if(count($this->history) > 0) {
			// Returns the last error in our history
			return $this->history[count($this->history) - 1];	
		} else {
			return false;	
		}
	}
	
	/**
	 * Retrieves an array of Error instances, containing several properties useful for debugging.
	 *
	 * @param	integer		$error_code		Optional filter by error code (will accept our custom error codes or PHPs predefined constants)
	 *
	 * @return	array|false					Returns an array of Error instances if any errors exist, false otherwise
	 */
	public function getErrors($error_code = false) {
		// Retrieve our history 
		$history = $this->getHistory($error_code);
		return $history;
	}
	
	/**
	 * Retrieves our debugging text for all errors logged.
	 *
	 * @param	integer		$error_code		Optional filter by error code (should be our custom error code)
	 *
	 * @return	string|false				Returns a string of debugging text for the errors if they exist, false otherwise
	 */
	public function getAsString($error_code = false) {
		// Retrieve our history 
		$history = $this->getErrors($error_code);
		$errors = array();
		if($history) {
			// Loop through each history item
			foreach($history as $i => $error) {
				// Turn the error into a string
				$errors[] = $error->getAsString();
			}
			// Join each error with a new line character and return it
			return implode("\n", $errors);
		} else {
			return false;	
		}
	}

	/**
	 * Prints our debugging text for all errors logged.
	 *
	 * @param	integer		$error_code		Optional filter by error code (should be our custom error code)
	 */
	public function show($error_code = false) {
		echo $this->getAsString($error_code);
	}

	/**
	 * Returns the total number of errors in the log.
	 *
	 * @param	integer		$error_code		Optional filter by error code (should be our custom error code)
	 *
	 * @return	integer						Returns the total number of errors generated
	 */
	public function countErrors($error_code = false) {
		$history = $this->getErrors($error_code);
		return $history !== false ? count($history) : 0;
	}

	/**
	 * Clears the current log of errors.
	 */
	public function clearLog() {
		$this->history		= array();
	}

	
	/**
	 * Gets our full errors log and filters it by error code if necessary.
	 *
	 * @param	integer		$error_code		Optional filter by error code (will accept our custom error codes or PHPs predefined constants)
	 *
	 * @return	array|false					Returns an array of Error instances if any exist, false otherwise
	 */
	protected function getHistory($error_code = false) {
		// Grab our history
		$history = $this->history;			
		// Check whether we should filter the history by error code
		if($error_code) {
			$filtered_history = array();
			foreach($history as $error) {
				// Check whether the error code matches the one we are filtering by
				if($error->getCode() == $error_code) {
					$filtered_history[] = $error;	
				}
			}
			$history = $filtered_history;
		}
		if(count($history) > 0) {
			return $history;
		} else {
			return false;	
		}
	}
}
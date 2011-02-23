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
 * Error class
 * 
 * Class to create and show Error instances
 *
 * @since		Version 1.0
 * @author		Alex Kaye <aframe@alexkaye.co.uk>
 */ 
class Error {
	/**
	 * Our custom error codes, and their English equivalent
	 */
	private $errorCodes	= array(
		1	=>	'Notice',
		2	=>	'Warning',
		3	=>	'Error'
	);
	
	/**
	 * Code of the error (see $errorCodes property)
	 */
	private $code;
	
	/**
	 * String associated with the error for debugging
	 */
	private $string;
	
	/**
	 * File where the error was triggered
	 */
	private $file;
	
	/**
	 * Line of the file where the error was triggered
	 */
	private $line;

	/**
	 * Minimum level (code) at which a fatal error is triggered
	 */
	private static $minFatalLevel = 3;

	/**
	 * Minimum level (code) at which a fatal error is triggered
	 */
	private static $showErrors = true;

	/**
	 * Context in which the error was generated for easier reading of debug information
	 */
	private static $webContext = false;
	
	/**
	 * Flag set if a fatal error has already occured to avoid recursive loops
	 */
	private static $fatalError = false;
	
	/**
	 * Creates a new error instance
	 *
	 * @param	integer		$code		Code relating to the type of error to instantiate (see $errorCodes properties)
	 * @param	string		$string		String associated with the error for debugging
	 * @param	string		$file		File where the error was triggered
	 * @param	integer		$line		Line of the file where the error was triggered
	 */	 
	public function __construct($code, $string, $file = false, $line = false) {
		// Store our error properties
		$this->code		= $code;
		$this->string	= $string;
		if(!$file || !$line) {
			// As we use our own method to trigger an error we call debug_backtrace to find the file and line number where the log was made
			$all_backtrace	= debug_backtrace();
			// Move to the last item in the backtrace array since it will be that function which used one of our methods
			//$backtrace		= $all_backtrace[count($all_backtrace) - 1];
			$backtrace	= $all_backtrace[0];
			$file		= $file ? $file : $backtrace['file'];
			$line		= $line ? $line : $backtrace['line'];
		}
		$this->file		= $file;
		$this->line		= $line;
		
		// See whether we have an ErrorCatcher to catch all errors in a single location
		if(class_exists('ErrorCatcher')) {
			// Get the ErrorCatcher instance
			$error_catcher = ErrorCatcher::getInstance();
			$error_catcher->log($this);
			
		}
		
		// Check whether the error is a fatal one
		if($code >= self::$minFatalLevel) {
			$this->triggerFatalError();
		}
	}
	
	/**
	 * Magic method returning the error in string format for lazy debugging.
	 */
	public function __toString() {
		return $this->getAsString();
	}
	
	/**
	 * Get the code of the error
	 *
	 * @return		integer				Returns the code relating to the type of error to instantiate (see $errorCodes properties)
	 */
	public function getCode() {
		return $this->code;	
	}
	
	/**
	 * Get the type of error
	 *
	 * @return		string				Returns the type of error (see $errorCodes properties)
	 */
	public function getType() {
		return $this->errorCodes[$this->code];	
	}
	
	/**
	 * Get the string associated with the error
	 *
	 * @return		string				Returns the string associated with the error for debugging
	 */
	public function getString() {
		return $this->string;	
	}

	/**
	 * Get the file where the error was triggered
	 *
	 * @return		string				Returns the file where the error was triggere
	 */
	public function getFile() {
		return $this->file;	
	}

	/**
	 * Get the line of the file where the error was triggered
	 *
	 * @return		integer				Returns the line of the file where the error was triggered
	 */
	public function getLine() {
		return $this->line;	
	}

	/**
	 * Get the error in string format for debugging
	 *
	 * @return		integer				Returns the error in string format for debugging
	 */
	public function getAsString() {
		// Create an empty string which we can build up
		$lines = array();
		$lines[] = '';
		$lines[] = '-------------------------------------------';
		$lines[] = 'ERROR';
		$lines[] = 'Code: ' . $this->getCode();
		$lines[] = 'Type: ' . $this->getType();
		$lines[] = 'File: ' . $this->getFile();
		$lines[] = 'Line: ' . $this->getLine();
		$lines[] = 'String: ' . $this->getString();
		$lines[] = '-------------------------------------------';
		$lines[] = '';
		// Join each line with a new line character
		$string = implode("\r\n", $lines);
		// Return the string with HTML tags if necessary
		return self::$webContext ? nl2br($string) : $string;
	}

	/**
	 * Prints the error in string format for debugging
	 */
	public function show() {
		if(self::$showErrors) {
			echo $this->getAsString();
		}
	}
	
	/**
	 * Sets the minimum level (code) at which errors become fatal errors
	 *
	 * @param	integer		$level		Minimum level (code) at which script execution will cease after the error is triggered
	 */
	 public static function minFatalLevel($level) {
		self::$minFatalLevel = $level; 
	 }

	/**
	 * Prevents any error information from being printed
	 */
	 public static function hideAll() {
		self::$showErrors = false; 
	 }

	/**
	 * Allows any error information to being printed
	 */
	 public static function showAll() {
		self::$showErrors = true; 
	 }

	/**
	 * Sets the context at which errors are generated. This allows debugging information to be printed in a more readable format. Should be set to TRUE
	 * for errors generated from the web, FALSE for errors generated at the command line.
	 *
	 * @param	boolean		$web_context	Whether errors are being generated by a web (HTTP/HTTPS) request or via the command line
	 */
	 public static function webContext($web_context) {
		self::$webContext = $web_context; 
	 }
	
	/**
	 * Creates a fatal error
	 */
	private function triggerFatalError() {
		if(self::$webContext) {
			// To avoid recursive loops lets check whether we have already raised a fatal error
			if(!self::$fatalError) {
				// Show the 500 Error page and tell it that it was called while execution was halted
				self::$fatalError = true;
				$args = array('call_time' => 2);
				DebugLog::systemLog('Fatal error occured, using Error controller file');
				ControllerAccessor::show500($args);
			}
		} else {
			// Die immediately after showing error info when running from the command line
			$this->show();
			die();			
		}
	}
}
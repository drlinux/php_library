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
 * Error catching class
 * 
 * Catches all user and non-user Errors and stores them in the singleton ErrorCatcher instance
 * 
 * @since		Version 1.0
 * @author		Alex Kaye <aframe@alexkaye.co.uk>
 * @todo		Correct scope of __construct(), should not be public since we should restrict instantiation to the
 * 				static get() method to prevent multiple instances
 */
class ErrorCatcher extends ErrorLogger {
	/**
	 * ErrorCatcher instance
	 */
	private static $instance;
	
	/**
	 * Store the ErrorLogger instance which holds our errors
	 */
	public $errors;
	
	/**
	 * Native PHP error codes mapped to our custom equivalents.
	 */
	private $errorMappings = array(
		E_NOTICE			=>	1,
		E_USER_NOTICE		=>	1,
		E_STRICT			=>	1,
		E_DEPRECATED		=>	1,
		E_USER_DEPRECATED	=>	1,
		E_WARNING			=>	2,
		E_USER_WARNING		=>	2,
		E_ERROR				=>	3,
		E_USER_ERROR		=>	3,
		E_COMPILE_ERROR		=>	3
	);
	
	/**
	 * Creates an instance of the ErrorCatcher. Maps PHP errors and exceptions to our own methods for improved
	 * handling
	 */
	public function __construct() {
		// Tell our native php errors to use our handleError method
		set_error_handler(array($this, 'catchPHPError'));
		// Catch exceptions aswell
		set_exception_handler(array($this, 'catchPHPException'));
		// Catch fatal errors aswell
		register_shutdown_function(array($this, 'catchPHPFatalError'));
	}
	
	
	/**
	 * Gets the singleton ErrorCatcher instance. Creates the instance first if it has not already been created.
	 * 
	 * @return	ErrorCatcher				Returns the singleton ErrorCatcher instance
	 */
	public static function getInstance() {
		if(self::$instance === null) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}
	
	/**
	 * Catches PHP errors like those generated using the native trigger_error() function. Raises one of our custom
	 * Error instances which is automatically logged using the log() method of the parent class.
	 *
	 * @param	integer		$code			This will be the PHP error code (see PHP error reporting constants)
	 * @param	string		$string			Associated debugging message
	 * @param	string		$file			Absolute path to the file where the error was triggered
	 * @param	integer		$line			Line number from which the error was triggered
	 */
	public function catchPHPError($code, $string, $file, $line) {
		$this->raisePHPError($code, $string, $file, $line);
	}

	/**
	 * Catches PHP fatal errors (E_ERRORs) errors like those generated if an undefined function is called.
	 * Raises one of our custom Error instances which is automatically logged using the log() method of the
	 * parent class.
	 */
	public function catchPHPFatalError() {
		$last_error = error_get_last();
		if($last_error['type'] === E_ERROR) {
			$this->raisePHPError(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
	
	/**
	 * Catches any uncaught PHP Exceptions. Raises one of our custom Error instances which is automatically logged
	 * using the log() method of the parent class.
	 *
	 * @param	Exception	$exception		Uncaught exception (see PHP Exceptions)
	 */	
	public function catchPHPException(Exception $exception) {
		// We handle the exception the same way we handle native errors. However since execution halts after
		// exceptions we need to raise a fatal error so we can debug it (or show a 500 response)
		$code	= E_USER_ERROR;
		$string	= $exception->getMessage();
		$file	= $exception->getFile();
		$line	= $exception->getLine();
		$this->raisePHPError($code, $string, $file, $line);
	}
	
	/**
	 * Generic function for raising one of our caught errors or exceptions
	 */
	protected function raisePHPError($code, $string, $file, $line) {
		// Map the PHP error code to our custom error code
		$code = $this->errorMappings[$code];
		// Raise a new error, all errors call our log() method
		new Error($code, $string, $file, $line);		
	}
}
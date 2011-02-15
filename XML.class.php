<?php
/**
 * XML class creates instances of the currently chosen xml parser class (simpleXMLElement)
 * 
 * @author Ryan "Tackleberry" Marshall
 * @package file
 * @subpackage xml
 */
class XML {
	private $element;
	public $errors;
	
	public function __construct($element) {
		$this->handleErrors();
		try {
			$this->element = new SimpleXMLElement($element);
		} catch(Exception $e) {
			$this->errors->log(new Error(3, $e->getMessage(), $e->getFile(), $e->getLine()));
		}
	}
	
	public function handleErrors() {
		// Disables libxml standard errors to allow custom error handling	
		libxml_use_internal_errors(true);
	 	$this->errors = new ErrorLogger();		
	}
	/**
	 * Static methods for simple xml functions used to create an object of SimpleXMLElement
	 * 
	 * @return XML
	 */
	public static function loadFromString($xml) {
		$class = __CLASS__;
		return new $class($xml);
	}
	
	public static function loadFromFile($xml) {
		$class = __CLASS__;
		return new $class(file_get_contents($xml));
	}
	
	public static function importDOM($xml) {
		$simple_xml = simplexml_import_dom($xml);
		$simple_xml_string = $simple_xml->asXML();
		$class = __CLASS__;
		return new $class($simple_xml_string);
	}
	
	public function __get($property) {
		return $this->element->$property;
	}
	
	public function __call($method, $args) {
		return call_user_func_array(array($this->element, $method), $args);
	}
	
	/**
	 * Enable standard libxml errors
	 */
	public function enableStandardErrors() {
		libxml_use_internal_errors(false);
	}
	 
	/**
	 * Get XML errors and display
	 * 
	 * Libxml does not fully implement column reporting so may on occasion return 0.
	 * 
	 * @return ErrorLogger
	 */
	public function getErrors() {
	    foreach(libxml_get_errors() as $error) {
	    	// Column number is not fully implemented by libxml and may return 0
	        $this->errors->log(new Error(2, 'XML Error: '. $error->message .' Occured on line '. $error->line .' at column '. $error->column));
	    }
		return $this->errors;
	}
}
?>
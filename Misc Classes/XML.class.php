<?php
/**
 * XML class creates instances of the currently chosen xml parser class (simpleXMLElement)
 * 
 * @author Ryan Marshall
 * @package file
 * @subpackage xml
 */
class XML {
	private $element;
	public $errors;
	
	public function __construct($element) {
		try {
			$this->element = new SimpleXMLElement($element);
		} catch(Exception $e) {
			echo 'Message '. $e->getMessage() .', File: '. $e->getFile() .', Line: '. $e->getLine();
		}
	}
	
	public function useCustomErrors() {
		// Disables libxml standard errors to allow custom error handling	
		libxml_use_internal_errors(true);	
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
	 */
	public function getErrors() {
	    foreach(libxml_get_errors() as $error) {
	    	echo 'Error code '. $error->code .' on line '. $error->line .' @ column '. $error->column .' (File: '. $error->file .')'; 
	    }
	}
}
?>
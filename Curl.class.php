<?php
/**
 * cURL class
 *
 * @package transfer
 * @subpackage curl
 * @author Ryan "Tackleberry" Marshall
 * @author Alex "Lev" Kaye
 */
class Curl extends Base {
	public $ch;
	public $url;
	public $timeout = 3;
	public $options = array();
	
	public function __construct($url, $options) {
		parent::__construct();
		$this->url 		= $url;
		$this->options 	= $options;
		$this->ch 		= curl_init();	
	}
	
	public function __destruct() {
		curl_close($this->ch);
	}
	
	public function execute() {
		curl_setopt($this->ch, CURLOPT_URL, $this->url);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt_array($this->ch, $this->options);
		$output = curl_exec($this->ch);
		return $output;
	}
	
	public function getInfo() {
		print_r(curl_getinfo($this->ch));
	}
	
	public function showErrors() {
		if (curl_errno($this->ch) != 0) {
			$this->getInfo();
			echo 'cURL error number:' .curl_errno($this->ch) . "\r\n"; 
			echo 'cURL error:' . curl_error($this->ch). "\r\n";
			return true;
		} 
		return false;
	}
	
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
	}
}
?>
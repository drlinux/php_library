<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); // Remove line to use class outside of codeigniter
/*
* @author Ryan Marshall <ryan@irealms.co.uk>
* @link www.irealms.co.uk
* @date 15/04/2011
* @package gravatar
*
* Irealms.co.uk Gravatar library for use with codeigniter
*/

class Gravatar_helper {
	private static $base_url = 'http://www.gravatar.com/';
	private static $secure_base_url = 'https://secure.gravatar.com/';

	public static function set_email($email)
	{
		$email = strtolower($email);
		$email = trim($email);

		if( ! filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE)
		{
			return md5($email);
		}

		return FALSE;
	}

	/*
	* get_gravatar_url
	* 
	* @see http://en.gravatar.com/site/implement/images/ for available options
	*
	* @param string $rating defaults to g
	* @param string $size defaults to 80
	* @param string $default_image
	* @param boolean $secure set to TRUE if a secure url is required
	*
	* @return string gratavar url
	*/
	public static function get_gravatar_url($email, $rating = FALSE, $size = FALSE, $default_image = FALSE, $secure = FALSE)
	{
		$query_string = FALSE;
		$options = array();
		if ($rating !== FALSE) {
			$options['r'] = $rating;
		}
		if ($size !== FALSE) {
			$options['s'] = $size;
		}
		if ($default_image !== FALSE) {
			$options['d'] = $default_image;
		}
		
		if (count($options) > 0) {
			$query_string = '?'. http_build_query($options);
		}

		$hash = self::set_email($email);
		
		if ($secure !== FALSE) {
			$base = self::$secure_base_url;
		}
		else
		{
			$base = self::$base_url;
		}
		
		return $base .'avatar/'. $hash . $query_string;
	}

	public static function get_full_profile($email)
	{
		$hash = self::set_email($email);
		libxml_use_internal_errors(true);
		$str = file_get_contents(self::$base_url . $hash .'.xml');
		$xml = simplexml_load_string($str);

		if ($xml === FALSE)
		{
			echo "Failed loading XML\n";
			foreach(libxml_get_errors() as $error)
			{
				echo $error->message;
			}
		}
		else
		{
			return $xml->entry;
		}
	}
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); // Remove line to use class outside of codeigniter
/*
* Irealms.co.uk Gravatar helper for use with codeigniter
* 
* @author Ryan Marshall <ryan@irealms.co.uk>
* @link www.irealms.co.uk
* @date 15/04/2011
* @package gravatar
*
*/

class Gravatar_helper {
	private static $base_url = 'http://www.gravatar.com/';
	private static $secure_base_url = 'https://secure.gravatar.com/';

	/*
	 * Set the email to be used, converting it into an md5 hash as required by gravatar.com
	 * 
	 * @param string $email
	 * 
	 * @return string|boolean Email hash or if email didn't validate then returnpw FALSE
	 */
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
	* @param string $default_image default sets can be found on the above link
	* @param boolean $secure set to TRUE if a secure url is required
	*
	* @return string gratavar url
	*/
	public static function get_image_url($email, $rating = FALSE, $size = FALSE, $default_image = FALSE, $secure = FALSE)
	{
		$hash = self::set_email($email);
		
		if ($hash === FALSE) {
			$hash = 'invalid_email';
		}
		
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

		if ($secure !== FALSE) {
			$base = self::$secure_base_url;
		}
		else
		{
			$base = self::$base_url;
		}
		
		return $base .'avatar/'. $hash . $query_string;
	}

	/*
	 * Grab the full profile data for a given email from gravatar.com in xml format
	 * 
	 * @param string $email
	 * @param string fetch_method defaults to file, 'curl' is the other option
	 * 
	 * @return object $xml->entry
	 */
	public static function get_profile($email, $fetch_method = 'file')
	{
		$hash = self::set_email($email);
		
		if ($hash === FALSE) {
			return FALSE;
		}
		
		libxml_use_internal_errors(true);
		
		if ($fetch_method === 'file') {
			if (ini_get('allow_url_fopen') == FALSE) {
				return FALSE;
			}

			$str = file_get_contents(self::$base_url . $hash .'.xml');			
		}

		if ($fetch_method === 'curl') {
			if ( ! function_exists('curl_init')) {
				return FALSE;
			}
			
			$ch	= curl_init();
			$options = array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_URL	=> self::$secure_base_url . $hash .'.xml',
				CURLOPT_TIMEOUT => 3
			);
			curl_setopt_array($ch, $options);
			$str = curl_exec($ch);	
		}
		
		$xml = simplexml_load_string($str);

		if ($xml === FALSE)
		{
			$errors = array();
			foreach(libxml_get_errors() as $error)
			{
				$errors[] = $error->message.'\n';
			}
			$error_string = implode('\n', $errors);
			//throw new Exception('Failed loading XML\n'. $error_string);
		}
		else
		{
			return $xml->entry;
		}
	}
}
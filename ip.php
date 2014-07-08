<?php
/**
 *  @author Joshua D Conn
 */
 
/**
 * Gets info about IP address from ipinfo.io
 * @param string $ip dot-decimal format ip address
 * @return mixed FALSE on failure associative array on success, which *should*
 * 	have the following keys (that depends on ipinfo.io):
 * 		ip
 * 		hostname
 * 		loc
 * 		org
 * 		city
 * 		region
 * 		country
 * 		phone
 * @see http://ipinfo.io/developers
 */
function ipinfo($ip)
{
	$result = FALSE;
	if(valid_ip($ip))
	{
		$curl = curl_init();
		if($curl)
		{
			//make call to ipinfo.io to get json-encoded information
			curl_setopt($curl, CURLOPT_URL, "http://ipinfo.io/".$ip."/json");
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($curl);
			curl_close($curl);
			
			if(is_string($result))
			{
				$result = json_decode($result, true);
				if(!is_array($result))
				{
					return FALSE;
				}
			}
		}
	}
	return $result;
}

/**
 * Checks whether the variable is a properly formatted dot-decimal ip address
 * @param string $ip_string
 * @return boolean
 */
function valid_ip($ip_string)
{
	if(!is_string($ip_string))
	{
		return false;
	}
	$parts = explode('.', $ip_string);
	if (count($parts) != 4)
	{
		return false;
	}
	foreach ($parts as $number)
	{
		if ($number != (int)$number ||
			$number < 0 ||
			$number > 255)
		{
			return false;
		}
	}
	return true;
}
?>

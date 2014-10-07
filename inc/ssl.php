/**
 * inc/ssl.php
 * Funcitons related to SSL
 * @author Joshua David Conn
 */

/**
 * Tests whether SSL (https) is being used and the communication is therefore encrpted
 * @return boolean true if SSL is used or false if it isn't
 */
function usingSSL()
{
	//in some enviroments you may need to uncomment later part of the condition
	if(!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on' /* ||
	 ($_SERVER["HTTP_X_FORWARDED_PORT"] == '443' && $_SERVER["HTTP_X_FORWARDED_PROTO"] == 'https')*/)
    {
    	return true;
    }
    return false;
}

/**
 * Returns the SSL (HTTPS) version of the current URL
 * @return boolean true if SSL is used or false if it isn't
 */
function secureURL()
{
	if(!empty($_SERVER['SERVER_NAME']) && !empty($_SERVER['PHP_SELF'])) {
		$retval = "https://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
		if(!empty($_SERVER['QUERY_STRING']))
			$retval .= '?'.$_SERVER['QUERY_STRING'];
		return $retval;
	}
	return false;
}

/**
 *	Redirects to HTTPS version of the same page if not using SSL
 *  WILL FAIL SILENTLY IF THERE IS ALREADY OUTPUT!
 *  @param string $failure_output what to send to the client (browser) upon detectable failure
 */
function forceSSL($failure_output)
{
	if(!usingSSL()) {
		if ($secureUrl = secureURL())
			header("Location: ".secureURL()); //redirect
		else
		{
			//TODO: log failure
			echo $failure_output;
		}
		exit();
	}
}

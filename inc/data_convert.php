/**
 * data_convert.php
 * Basic data conversion functions
 * @author Joshua David Conn
 */

/**
 * Handy way to rener a boolean value as a string inline
 * @param boolean $condtion condition to test
 * @param string $true	what to return if $condtion is true
 * @param string $false	what to return if $condtion is false
 */
function bool2str($condtion, $true='TRUE', $false='FALSE')
{
	if($condtion)
	{
		return $true;
	}
	return $false;
}

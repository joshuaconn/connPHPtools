<?php
/**
 * Functions for analyzing or processing arrays not built into PHP
 * @author Joshua Conn
 */

/**
 *	Test whether every item in a list passes a test. 
 *  Inspired by all() in Python
 *	@param array $list values to be tested
 *	@param callback $tester_callback a function that accepts one parameter and
 *		returns true or false. If null, list items are evaluated directly.
 *  @example every(array('1',"2"),'is_string'); //returns true
 *  @example every(array('1',"2",3),'is_string'); //returns false
 *	@return boolean
 *  @see any()
 */
function every(array $list, $tester_callback=NULL)
{
	if(is_null($tester_callback))
	{
		foreach($list as $l)
			if(!$l)
				return false;
	}
	else
	{
		foreach($list as $l)
			if(!call_user_func($tester_callback,$l))
				return false;
	}
	return true;
}

/**
 *	Test whether any item in a list passes a test.
 *  Inspired by any() in Python
 *	@param array $list values to be tested
 *	@param callback $tester_callback a function that accepts one parameter and
 *		returns true or false.
 *  @example any(array('1',"2",3),'is_string'); //returns true
 *  @example any(array(1,2,3),'is_string'); //returns false
 *	@return boolean
 *  @see every()
 */
function any(array $list, $tester_callback=NULL)
{
	if(is_null($tester_callback))
	{
		foreach($list as $l)
			if($l)
				return true;
	}
	else
	{
		foreach($list as $l)
			if(call_user_func($tester_callback,$l))
				return true;
	}
	return false;
}
?>

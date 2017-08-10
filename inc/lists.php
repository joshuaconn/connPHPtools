<?php
/**
 * Functions for analyzing or processing arrays not built into PHP
 * @author Joshua Conn
 */

/**
 *	Test whether every item in a list passes a test. 
 *	Inspired by all() in Python
 *	@param array $list values to be tested
 *	@param callback $tester_callback a function that accepts one parameter and
 *		returns true or false. If null, list items are evaluated directly.
 *	@example every(array('1',"2"),'is_string'); //returns true
 *	@example every(array('1',"2",3),'is_string'); //returns false
 *	@return boolean
 *	@see any()
 *	@see mostly()
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
 *	Inspired by any() in Python
 *	@param array $list values to be tested
 *	@param callback $tester_callback a function that accepts one parameter and
 *		returns true or false.
 *	@example any(array('1',"2",3),'is_string'); //returns true
 *	@example any(array(1,2,3),'is_string'); //returns false
 *	@return boolean
 *	@see every()
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

/**
 *	Test whether most items in a list passes a test.
 *	Inspired by any() and all() in Python
 *	@param array $list values to be tested
 *	@param callback $tester_callback a function that accepts one parameter and
 *		returns true or false.
 *	@return boolean
 *	@see every()
 */
function mostly(array $list, $tester_callback=NULL)
{
	$tot=count($list);
	$positives=0;
	if(is_null($tester_callback))
	{
		foreach($list as $l)
			if($l)
				$positives++;
	}
	else
	{
		foreach($list as $l)
			if(call_user_func($tester_callback,$l))
				$positives++;
	}
	return $positives>$tot/2;
}

/**
 * Sorts an array of associative arrays by a common key in the sub arrays
 * If the sub-arrays were rows from the result of an SQL query, 
 * this would be the same as using an ORDER BY in that query
 *
 * @example sortBy(sortBy([
 *       ['word'=>'in',   'pos'=>3],
 *       ['word'=>'put',   'pos'=>1],
 *       ['word'=>'order','pos'=>4],
 *       ['word'=>'this',  'pos'=>2] ],
 *   'pos'); // returns  [ ['word'=>'put', 'pos'=>1], ['word'=>'this','pos'=>2],['word']=>'in','pos'=>3],['word']=>'order','pos'=>4]]
 * @param $arr array of associative arrays to be sorted
 * @param $key associative array to sort by - must be an index in every single array
 * @return array sorted array
 */
function sortBy(array $arr,$key) {
    $retval = $arr;
    $sort_arr = [];
    foreach ($arr as $row) {
        assert(array_key_exists($key,$row));
        $sort_arr[]=$row[$key];
    }
    array_multisort($sort_arr,SORT_ASC,$retval);
    return $retval;
}
?>

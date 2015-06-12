<?php
/**
 * What The PHunc
 * ==============
 * What The PHunc, pronounced like "wut thuh funk" is a
 *  web interface for testing libraries in PHP that use a functional paradigm
 *
 * WARNING: This can be VERY dangerous if not use properly! Mostly the obvious:
 *
 * - Arbitrary PHP functions can do just about anything on a server with the
 * 	right permissions, so make sure the ones you are testing have no chance of
 *  overwritting files, dropping database records, sending emails you don't want
 *  to send, etc. before testing. This is for testing PHP functions, but not all
 *  functions are safe to test
 *
 * - Any function you have defined can be executed using this, so don't include
 * 	this on a public web server with libraries that contain functions you
 * 	wouldn't want anyone in the world to be able to execute
 *
 * GET PARAMS:
 * ===========
 * f - the name of the PHP function to show the defined parameters
 *
 * POST PARAMS:
 * ===========-
 * f  - name of the PHP function to execute (all other post params require this)
 * p0 - the first parameter to send to f
 * p1 - the second parameter to send to f
 * ...
 * pn - the nth - 1 parameter to send to f
 * e  - if present and set to 1, params are evaluated,
 * 	rather than always being treated as string literals which is the default
 *  can be disabled with $allow_eval
 *
 * @file what_the_phunc.php
 * @author Joshua Conn
 * @version 0.1
 */

////////////////////////////////////////////////////////////////////////////////
// CONFIG
////////////////////////////////////////////////////////////////////////////////
ini_set('display_errors', '1');

// name of functions not to show in the drop-down or allow for execution
$omited_functions = array('you_should_not_see_this');

// if set to true, user can eval code CAREFUL - THAT MEANS THEY CAN DO ANYTHING!
$allow_eval = true;

////////////////////////////////////////////////////////////////////////////////
// SIMPLE TEST FUNCTIONS
// comment these and include your own library to test it's functions instead
function doNothing(){}
function sayHi(){echo "hi";}
function say($words="Say what?"){echo $words;}
function return42(){return 42;}
function you_should_not_see_this(){echo "Oh no!";};   //Omited by default above
function return1more($num){return $num+1;}
function add($n1,$n2){return $n1+$n2;}
function borrow($item){return $item;}
function yes_no($bool){if($bool)return'yes';return'no';}
////////////////////////////////////////////////////////////////////////////////

$defined_functions = get_defined_functions();
$defined_user_functions = array_diff($defined_functions['user'], $omited_functions);
sort($defined_user_functions);

if(in_array($_POST['f'], $defined_user_functions)) {
	$use_eval = $allow_eval && $_POST['e']=='1';
	if($_POST['e']=='1')
		if ($allow_eval)
			$use_eval = true;
		else
			exit('evaluation not permitted');

	$selected_func_name = $_POST['f'];
	$selected_func = new ReflectionFunction($selected_func_name);
	$exec=true;

	//determine number of params given.
	$num_params_given=0;
	$params_given=array();
	if($use_eval)
		while (array_key_exists('p'.$num_params_given++, $_POST))
			eval('$params_given[]='.$_POST['p'.($num_params_given-1)].';');
	else
		while (array_key_exists('p'.$num_params_given++, $_POST))
			$params_given[]=$_POST['p'.($num_params_given-1)];
	$num_params_given--;

	//call the function
	ob_start();
	$retval = call_user_func_array($_POST['f'],$params_given);
	$output = ob_get_clean();

} elseif(in_array($_GET['f'], $defined_user_functions)) {
	$selected_func_name = $_GET['f'];
	$selected_func = new ReflectionFunction($_GET['f']);
}

////////////////////////////////////////////////////////////////////////////////
// MAIN OUTPUT
////////////////////////////////////////////////////////////////////////////////

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
echo '<HTML><HEAD><TITLE>PHP Web Function Tester</TITLE></HEAD><BODY><H1>What The PHunc</H1>';
echo '<FORM method="get">';
echo 'Function:';
echo '<SELECT name="f" onchange="document.getElementById(\'post_select\').style.visibility = \'hidden\';" >';
foreach ($defined_user_functions as $func) {
	if($selected_func_name===$func)
		echo '<OPTION selected>';
	else
		echo '<OPTION>';
	echo htmlentities($func);
	echo '</OPTION>';
}
echo '</SELECT>';
echo '<INPUT type="submit" value="Show Params"/>';
echo '</FORM>'."\n";
echo '<DIV id="post_select">';
if(!empty($selected_func)) {

	////////////////////////////////////////
	// PARAM FORM
	////////////////////////////////////////
	echo '<FORM method="post">';
	$num_params=$selected_func->getNumberOfParameters();
	$num_requried_params=$selected_func->getNumberOfRequiredParameters();
	echo '<H2>'.htmlentities($selected_func_name).'() Params:</H2>';
	if($num_params>0) {
		echo '<OL>';
		$params = $selected_func->getParameters();
		$i=0;
		foreach ($params as $p) {
			echo '<LI>$'.htmlentities($p->getName());
			echo ' <INPUT type="text" name="p'.$i.'"';
			if (array_key_exists('p'.$i++, $_POST) && !empty($_POST['p'.($i-1)])) {
				if($use_eval)
					echo ' value="'.htmlentities(var_export($params_given[($i-1)],true)).'"';
				else
					echo ' value="'.htmlentities($_POST['p'.($i-1)]).'"';
			}
			elseif ($p->isDefaultValueAvailable() || $num_requried_params<$i){
				echo ' value="'.htmlentities(var_export($p->getDefaultValue(),true)).'"';
			}
			echo ' />';
			echo '</LI>';
		}
		echo '</OL>'."\n";
		if($allow_eval) {
			echo '<INPUT type="checkbox" name="e" value="1" '.($use_eval && $_POST['e']=='1'?'checked':'').'>';
			echo ' Treat Parameters as PHP rather than strings CAREFUL! ';
		}
	} else {
		echo '<P>This function does not accept parameters</P>';
	}
	echo '<INPUT type="hidden" name="f" value="'.htmlentities($selected_func_name).'">';

	echo '<INPUT type="submit" value="Exec">';
	echo '</FORM>';

	////////////////////////////////////////
	// RESULT DISPLAY
	////////////////////////////////////////
	if(isset($exec)) {
		echo "<H2>RETURN VALUE:</H2>\n";
		echo "<CODE>".htmlentities(var_export($retval,true))."</CODE>";
		echo "\n<H2>OUTPUT:</H2>\n";
		echo "<CODE>".htmlentities($output)."</CODE>";
	}
}
echo '</DIV><!-- post_select ->';
echo "</BODY></HTML>";
?>

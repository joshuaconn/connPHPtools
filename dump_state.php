<?php
/**
  * dumps defined variables, constants, and a backtrace to the browser
  */
function dump_state()
{
	echo "<h1>defined vars:</h1>\n";
	$defined_vars = get_defined_vars();
	var_dump($defined_vars);
	echo "\n<h1>defined constants:</h1>\n";
	$defined_constants = get_defined_constants ();
	var_dump($defined_constants);
	echo "\n<h1>backtrace:</h1>\n";
	$backtrace = debug_backtrace();
	var_dump($backtrace);
	exit;
}
?>

<?php
/**
  * dumps defined variables, constants, and a backtrace to the browser
  * @example dump_state(get_defined_vars());
  */
function dump_state($defined_vars)
{
	echo "<h1>defined vars:</h1>\n";
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

/**
 * @param string $inStr substring of JavaScript literal
 * @param string $quotChar type of quotes used: " or '
 * @example 
 *      $whose = "Josh's/Your";
 *      echo "&lt;script>alert('This is ".jsEscStr($whose)." test');&lt;/script>";
 * @example 
 *      $type = '"Double-quote"';
 *      echo '&lt;script>alert("This is a '.jsEscStr($type).' test");&lt;/script>';
 */
function jsEscStr($inStr,$quotChar="'"){
	if($quotChar!="'" && $quotChar!='"')
		trigger_error(
			'Bad quote passed to js_esc_str_literal():'.print_r($quotChar,true),
			E_USER_ERROR);
	return str_replace(
		array("\\",$quotChar), 
		array("\\\\","\\".$quotChar), 
		$inStr);
}

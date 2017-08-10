<?php
/**
 * Turns an array into an HTML seletion box for use in a form
 * @param $options keys are values for the option elements; values are what gets displayed
 * @param $default if this matches one of the $options keys, that is selected by default
 * @param $id the HTML id and name used for the selection box rendered
 * @return string the rendered HTML
 */
function disp_options(array $options,$default=null,$id='')
{
	if($id!='')
		$id=" id='$id' name='$id'";
	$retval = "<select$id>";
	foreach ($options as $n=>$v)
		$retval.='<option value="'.htmlentities($n).'"'.($n==$default?' selected':'').'>'.
			htmlentities($v).'</option>';
	return  $retval."</select>";
}

/**
 * Takes an associative array and renders an html description list representation
 * @param array $a keys are terms; values descriptions
 * @return string the rendered HTML
 * @example arr2dl(['Foo'=>'bar','Bim'=>'Bash']); //returns "<dl><dt>Foo</dt><dd>bar</dd><dt>Bim</dt><dd>bash</dd></dl>"
 */
function arr2dl(array $a)
{
	$r="<dl>";
	foreach ($a as $k=>$v){
		$r.="<dt>".htmlentities($k)."</dt><dd>".htmlentities($v)."</dd>";
	}
	return $r."</dl>";
}

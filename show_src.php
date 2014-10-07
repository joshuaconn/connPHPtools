<?php
/**
 * WARNING: BE VERY CAREFUL WHERE YOU PUT THIS!
 *
 * Renders the source code of and internal file to the browser
 * @var $_GET['path']
 */
	$path=$_GET['path'];
?>
<html>
<body>
<h1>Source of <?=html_out($path)?></h1>
	<div style="background-color:#ddd;">
		<code>
			<?=nl2br(
				str_replace(array(' ',"\t"),array('&nbsp;','&nbsp; &nbsp; &nbsp;'),html_out(
						file_get_contents($path)
				)))?>
		</code>
	</div>
</body>
</html>

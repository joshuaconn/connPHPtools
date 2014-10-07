<?php
/**
 * WARNING: BE VERY CAREFUL WHERE YOU PUT THIS!
 *
 * Renders the source code of and internal file to the browser
 * @var $_GET['path']
 * @author Joshua Conn
 */
	$path=$_GET['path'];
	$contents = file_get_contents($path);
?>
<html>
<body>
<h1>Source of <?=htmlentities($path)?></h1>
<?php
	if($contents!==FALSE) {
	?>
	<div style="background-color:#ddd;">
		<code>
			<?=nl2br(
				str_replace(array(' ',"\t"),array('&nbsp;','&nbsp; &nbsp; &nbsp;'),htmlentities(
						$contents
				)))?>
		</code>
	</div>
	<?php } else { ?>
	<div style="background-color:#faa;">
		Cannot display the contents of this file.
	</div>
	<?php } ?>
</body>
</html>

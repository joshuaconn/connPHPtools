<?php
/**
 * To be used with show_src.php
 *
 * Used to display the source code of an internal PHP file along the right of
 * the renederd PHP file itself.
 *
 * @var string $_GET['path']
 * @author Joshua Conn
 */
$path=$_GET['path'];
?>
<frameset cols="50%,50%">
	<frame src="show_src.php?path=<?=urlencode($path)?>" />
	<frame src="<?=$path?>" />
</frameset>

<?php
/**
 * File browser
 *
 */
$dir = dirname(__FILE__);
if(array_key_exists('dir', $_GET))
{
	if (is_dir($_GET['dir']))
	{
		$dir = $_GET['dir'];
	}
	else
	{
		echo "INVALID FILE";
	}
}
$fsi = new FilesystemIterator($dir);
$dirpathinfo = pathinfo($dir);


function SplFileInfo2html(SplFileInfo $file)
{
	$ret_html = '';
	if($file->isDir())
	{
		$ret_html .= dirlink($file);
	}
	else
	{
		$ret_html .= $file->getFilename();
	}
	return $ret_html;
}

function dirlink(SplFileInfo $dir)
{
	assert($dir->isDir());
	return '<a href="?dir='.urlencode($dir->getRealPath()).'">'.
			htmlentities($dir->getFilename()).'</a>';
}

function human_filesize($bytes, $decimals = 2)
{
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

echo '<a href="?dir='.urlencode($dirpathinfo['dirname']).'">&lt;&lt;UP</a>';
echo '<strong>'.htmlentities($dir).'</strong><br />';
echo '<table>';
foreach ($fsi as $f)
{
	echo "<tr><td>";
	echo SplFileInfo2html($f);
	echo "</td><td style='text-align:right;'>";
	echo htmlentities(human_filesize($f->getSize(),0));
	echo "</td></tr>";
}
echo '</table>';
?>

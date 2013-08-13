<?php
/******************************************
 * Write content to file.
 ******************************************/
function writeToFile($file,$content)
{
	if(file_exists($file)) unlink($file);
	$fh = fopen($file, 'w') or die("can't open $file");
 	fwrite($fh,$content);
  fclose($fh);
}
?>
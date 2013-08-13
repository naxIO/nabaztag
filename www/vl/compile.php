<?php
/****************************************
 * Set correct checksum for V1
 ****************************************/
function compile($data)
{
	$debug=false;
	
	$iMind = checksum("mind");
	$len = count($data);
	$chk=0;
	
	for($i = 2; $i < $len -13; $i++)  //skip 1st char, last 13 are tail
	{
		//$chk += ord($data[$i]);  //ord returns ascii value of character
		$chk += $data[$i];
		$chk &= 255;
		if($debug) echo("$i = 0x" . dechex($data[$i]) . ", chk = 0x" . dechex($chk) . "<br>");
	}
			
	$iRes1  = $chk; 
	
	if($debug) echo("iRes1 = $iRes1<br>");
	if($debug) echo("iMind = $iMind<br>");
	$iFinal = 255-$iRes1-$iMind;
	$iFinal &= 255;
	
	if($debug) echo("checksum = 0x" . dechex($iFinal) . "<P>");
	$data[$len-5] = $iFinal;  //set the checksum
	
	if($debug) var_dump($data);
	return $data;
}


/************************************************************
 * Return the checksum for a string.  doesn't work with lists
 ************************************************************/
function checksum($data)
{
	$l=strlen($data);
	$i=0;
	$chk=0;
	
	for($i=0;$i<$l;$i++)
	{
		$chk += ord($data[$i]);  //ord returns ascii value of character
		$chk &= 255;
	}
	
	return $chk;
}

?>
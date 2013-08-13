<?php
/*****************************************************************
 * RSS feed thru google
 *****************************************************************/
function doTTS($t,$lang,$sn) 
{
	$hutch = "./vl/hutch/$sn";
	
	if(! is_dir($hutch))
		mkdir($hutch);
	
	//send to google TTS
	$request = "http://translate.google.com/translate_tts?tl=$lang&q=" . urlencode($t);
	
	//curl and save to mp3
	$response = goCurl($request);
	if(strlen($response) < 1) return;  //no response from service
	
	$file="$hutch/rss.mp3";
	writeToFile($file,$response);
}	
?>
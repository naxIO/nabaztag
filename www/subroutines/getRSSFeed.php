<?php
/**********************************************
 * Get feed from RSS and return list.
 * $request - the URL
 * $element - the element to extract from the XML
 **********************************************/
function getRSSFeed($request,$element)
{
	if(strlen($request) < 1) return; 
	
	$ch = curl_init();
  curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
	curl_setopt($ch, CURLOPT_URL, $request);
	$response = curl_exec($ch);
	
	if($response == "") $response = curl_exec($ch); //try again
	
	curl_close($ch);
 
 	if(strlen($response) < 1) return;  //no response from service
 	
	$doc = new DOMDocument();
	$doc->preserveWhiteSpace=false;
	
	ini_set('log_errors', 0);

	try
	{
		$doc->loadXml($response);
	}
	catch(exception $e)
	{
		logError("Error loading XML Doc. The path to load was $request.");
	}

	ini_set('log_errors', 1);
	
	$titles = $doc->getElementsByTagName($element);
	
	if($titles->length > 5)
	{
		$t1 = trim($titles->item(1)->nodeValue);  //make this a parm
	  $t2 = trim($titles->item(2)->nodeValue);
	  $t3 = trim($titles->item(3)->nodeValue);
	  $t4 = trim($titles->item(4)->nodeValue);
	  $t5 = trim($titles->item(5)->nodeValue);
	  $t6 = trim($titles->item(6)->nodeValue);
	
		$t1 = str_replace('CDATA','',$t1);
		$t2 = str_replace('CDATA','',$t2);
		$t3 = str_replace('CDATA','',$t3);
		$t4 = str_replace('CDATA','',$t4);
		$t5 = str_replace('CDATA','',$t5);
		$t6 = str_replace('CDATA','',$t6);
				
		/*
		echo $t1 . '<br>';
		echo $t2 . '<br>';
		echo $t3 . '<br>';
		echo $t4 . '<br>';
		echo $t5 . '<br>';
		*/
		
	 	return array($t1,$t2,$t3,$t4,$t5,$t6);
	}
}
?>
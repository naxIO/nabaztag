<?php
/**********************************************
 * Get feed from Twitter and return latest tweet.
 * $request - the URL
 * $element - the element to extract from the XML
 **********************************************/
function getFollow($request,$element)
{
	if(strlen($request) < 1) return; 

	$protocol = strtolower(substr($request,0,4));

	if($protocol == 'feed')
	{
		$protocol = 'http';
		$request = str_replace('feed://','http://',$request);
	}
	
	$ch = curl_init();
  curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
	curl_setopt($ch, CURLOPT_URL, $request);
	$response = curl_exec($ch);
	
	if($response == "") $response = curl_exec($ch); //try again

	curl_close($ch);
 
  if(strlen($response) < 1)
 	{
 		//logError("getFollow no curl response for $request");
 		return;  //no response from service
 	}
 	
 	//echo "response=$response <P>";
 	
 	ini_set('log_errors', 0);

	$doc = new DOMDocument();
	$doc->preserveWhiteSpace=false;
	
	try
	{
		$doc->loadXml($response);
	}
	catch(exception $e)
	{
		logError("Error loading XML Doc. The path to load was $request.");
	}

	ini_set('log_errors', 1);
	
	$element="title"; //item";
	
	$titles = $doc->getElementsByTagName($element);
	
	//echo "value = " . $titles->item(1)->nodeValue . "<P>";
	
	if($titles->length > 0)
	{
		$t1 = trim($titles->item(1)->nodeValue);  //this is the tweet
	 	$t1 = str_replace('CDATA','',$t1);
	 	return array($t1,$t2,$t3,$t4,$t5,$t6);
	}

	
}
?>
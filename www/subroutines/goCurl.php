<?php
/*********************************************
 * Curl utility
 *********************************************/
function goCurl($request)
{
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
	curl_setopt($ch, CURLOPT_URL, $request);
	$response = curl_exec($ch);
	if($response == "") 
    {
        sleep(1);
        $response = curl_exec($ch); //try again
    }
	curl_close($ch);
	return $response;
}
?>

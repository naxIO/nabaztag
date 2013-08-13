<?php
/*************************************************
 * language decode
 *************************************************/
function getLanguage($language)
{
	$len = strlen($language);
	$language = substr($language,$len-2,2);
	
	switch($language)
	{
		case 'uk':
		case 'us':
			$lang='en';
			break;
		
		case 'es':
			$lang='es';
			break;
		
		case 'de':
			$lang='de';
			break;
		
		case 'it':
			$lang='it';
			break;
		
		default:
			$lang='en';
			break;
	}
	
	return $lang;

}
?>
<html>
<META NAME="ROBOTS" CONTENT="NONE">
<META NAME="GOOGLEBOT" CONTENT="NOARCHIVE">
<?php
/*
NabaztagLives 

Copyright (C) 2013 Pokey (Pokey@nabaztaglives.com)

Comments, questions, and bug reports should be submitted via
http://sourceforge.net/projects/nabaztaglives/

More details can be found at the project home page:
http://nabaztaglives.com

This file is part of NabaztagLives.

NabaztagLives is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

NabaztagLives is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with NabaztagLives.  If not, see <http://www.gnu.org/licenses/>.
*/
//error_reporting(E_ALL);  //don't use this in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log','../etc/nabaztag_error.log');
//error_reporting(0);  //don't use this, use ini_set or nothing goes to the log

include './subroutines/clean.php';
include './subroutines/getLanguage.php';
include './subroutines/goCurl.php';
include './subroutines/writeToFile.php';
include './subroutines/logError.php';
include './subroutines/queryWithRetry.php';

$msg_idle = '7fffffff';
if(isset($_GET['tts'])) $tts = $_GET['tts'];  //text msg

if(!isset($_GET['sn']))
{
    echo 'No sn';
    sleep(2);
    return;
}

$sn = $_GET['sn']; //serial #
if(isset($_GET['rf'])) $rfid = $_GET['rf'];
if(isset($_GET['color'])) $color= $_GET['color'];
if(isset($_GET['play'])) $play=$_GET['play'];  //play mp3
if(isset($_GET['stream'])) $stream=$_GET['stream']; //stream mp3

$debug=false;

if(strlen($sn) == 0)
{
	echo 'No sn provided.';
	sleep(2);
	return;
}

if(substr_count($sn, ':') < 5)
{
	echo "'$sn' is not a valid sn.";
	sleep(2);
	return;
}

if(strlen($sn) < 17)
{
	echo "'$sn' is not a valid sn. ";
	sleep(2);
	return;
}

$sn = clean($sn);
$sn = strtolower($sn);

date_default_timezone_set('America/Chicago'); //for warning

$hour = date("H"); //military 00-23
$min  = date("i"); //minute 00-59
$sec  = date("s"); //secs 00-59

$hutch = "./vl/hutch/$sn";
	
/* V1 doesn't have a hutch
if(! is_dir($hutch))
{
	echo 'Hutch not found.  Is your rabbit registered?';
	return;
}
*/

//lookup the rabbit by serial #
include '../etc/nabaztag_db.php';
$con = mysqli_connect($host,$user,$pass,$db);
if (!$con) 
{
	logError('Api.php: Could not connect: ' . mysqli_connect_errno() . mysqli_connect_error());
	echo 'The operation failed.  The head rabbit has been placed on notice.';
	return;
}

$ip = $_SERVER['REMOTE_ADDR'];
$request = $_SERVER['REQUEST_URI'];

/************************************
 * log visit
 ***********************************/
 
$file="../etc/api_calls.log";
file_put_contents($file,$request);
	
$cmd = "call sp_LogConnect('" . $sn . "','" . $ip . "','" . $request . "',@rabbitID)";
if($debug) echo $cmd . '<p>';
$result = mysqli_query($con,$cmd);
if (!$result) 
{
	logError('Api.php: Invalid log connect call. ' . mysqli_error($con));
	echo 'The operation failed.  The head rabbit has been placed on notice.';
	return;
}

$result = mysqli_query($con,"select @rabbitID");
if (!$result) 
{
	logError('Api.php: Invalid log query. ' . mysqli_error($con));
	echo 'The operation failed.  The head rabbit has been placed on notice.';
	return;
}
while($row = mysqli_fetch_row($result))
{
	$rabbitID = $row[0];
}

if($rabbitID < 1)  //rows found in DB
{
	echo "No rabbit";
	sleep(2);  //for attackers
	return;
}

mysqli_next_result($con);  //required to avoid sync error

/*************************************
 * get rabbit
 *************************************/
$cmd = "call sp_GetRabbit('" . $sn. "')";
if($debug) echo $cmd . '<p>';
$result = mysqli_query($con,$cmd);
if (!$result) 
{
	logError('Api.php: Invalid getRabbit query. ' . mysqli_error($con));
	echo 'The operation failed.  The head rabbit has been placed on notice.';
	return;
}

while($row = mysqli_fetch_row($result))
{
	$rabbitID    = $row[0];
	$name        = $row[1];
	$timeZone    = $row[2];
	$weatherCode = $row[3];
	$email       = $row[4];
	$sleepHour   = $row[5];
	$wakeHour    = $row[6];
	$language    = $row[7];
	$degrees     = $row[8];
	$lastRequest = $row[9];
	$bottomColor = $row[10];
	$idleAction  = $row[11];
	$weekendWakeHour = $row[12];
	$buttonAction = $row[13]; //cck
	$reboot = $row[15];
	$follow = $row[16];
	$version = $row[17];  //0, 1, or 2
	$wavTimeout = $row[18];
	$clockType = $row[19];

}

mysqli_next_result($con);  //required to avoid sync error

if(($sn == "00904B8C6C6D") || ($sn == '000000000000'))
{
	/*
	$out = 	'./vl/FR/bin2/api.bin';
	$msg = file_get_contents($out);
	//echo $msg . '<P>';
	
	$data = unpack('C*', $msg);
	//var_dump($data);
	
	//if($data[74] == 77) echo '<P>yes its a 77<P>';
	
	$data[74] = 1; //red
	
	//$packed='';
	
	for($i=1; $i<=count($data); $i++)
	{
		$packed .= pack('C*',$data[$i]);
	}
	
	//var_dump($packed);
	//echo "<P>$packed<P>";
	
	$hutch = "./vl/hutch/$sn";

	if(! is_dir($hutch)) mkdir($hutch);
	
	$file="$hutch/api.bin";
	
	file_put_contents($file,$packed);
	
	$file = "../hutch/$sn/api.bin";  //the P3.jsp is in vl/FR/
	
	$min=$min+1;
	if($min > 59) $min=0;  //send next minute.  this min already has a nop.bin
	
	queueV1($file,$rabbitID,$min,$con); //add item to queue
	
	echo 'OK';
  mysqli_close($con);
	exit(0);
	*/
}

$min  = date("i"); //minute 00-59
$sec  = date("s"); //secs 00-59

$min = $min+1;

/********************************************************
 * check queue - dynamic SQL due to proc collation issue
 ********************************************************/
//delete cmd if older than 3 mins

$cmd = "delete from queue " 
		 . "where fkRabbitID=$rabbitID "
		 . "and time_to_sec(timediff(now(),lastUpdate)) > 180 "
		 . "order by id; ";  

$result = mysqli_query($con,$cmd);
if (!$result) 
{
	logError('Api.php: Invalid delete queue query: ' . mysqli_error($con));
}

mysqli_next_result($con);  //required to avoid sync error

//need to check here to make sure there is not already a msg in the queue from multiple attempts

$cmd = " select count(*) " .
			 " from queue " .
			 " where fkRabbitID=$rabbitID " .
			 "   and minute=$min" .
			 "   and sent=0";  

$result = queryWithRetry($con,$cmd,$name,"API queue.");
	
if (!$result) 
{
	exit(0);
}

while($row = mysqli_fetch_row($result))
{
	$count = $row[0];
}

if($count > 0)
{
	echo "Already queued.";
	exit(0);
}
	
mysqli_next_result($con);  //required to avoid sync error

/**************************************************************
 * LED color
 ***************************************************************/
if(strlen($color) > 0)
{
	if($version != '1')  //00:90:4B:8C:6C:6D
	{
	  	echo 'Not supported by V2 rabbits.';
		return;
	}

	if(! is_numeric($color)) 
	{
		echo 'Color code must be numeric.';
		return; //invalid code
	}
	
	if($color < 1 || $color > 254)
	{
		echo 'Color code must be between 1 and 254.';
		return;
	}
	
	$out = 	'./vl/FR/bin2/api.bin';
	$msg = file_get_contents($out);
	
	$data = unpack('C*', $msg);
	
	//var_dump($data);
	
	$data[74] = $color; //red
	//echo '<P>checksum is ' . $data[117] . '<P>';
	
	$data[117] = $data[117]-$color+1; //checksum is 0x19 in source file, need to port checksum or port compiler
	
	for($i=1; $i<=count($data); $i++)
		$packed .= pack('C*',$data[$i]);

	//var_dump($packed);
	//echo "<P>$packed<P>";
	
	$hutch = "./vl/hutch/$sn";

	if(! is_dir($hutch)) mkdir($hutch);
	
	$file="$hutch/api.bin";
	
	if(file_exists($file)) unlink($file);
	
	file_put_contents($file,$packed);
	
	$file = "../hutch/$sn/api.bin";  //the P3.jsp is in vl/FR/
	
	queueV1($file,$rabbitID,$min,$con); //add item to queue
	
	$wait = 60 - $sec;
	
	echo "OK. $wait seconds.";
  	mysqli_close($con);
	exit(0);

}

/************************************************************
 * TTS
 ************************************************************/
$tts=substr($tts,0,100);  //max 100 chars

if(strlen($tts) > 0)
{
	if($version == '1')  //00:90:4B:8C:6C:6D
	{
	 	echo 'Not supported by V1 rabbits.';
		return;
	}
	
	queue($sn,$min,$tts,$con,$language,$rabbitID);
	$wait = 60 - $sec;
	echo "OK. $wait seconds.";
  	return;
}

/************************************************************
 * PLAY MP3
 ************************************************************/
$url=substr($play,0,244);  //max 244 chars in url

if(strlen($url) > 0)
{
	if($version == '1')  //00:90:4B:8C:6C:6D
	{
	  	echo 'Not supported by V1 rabbits.';
	 	return;
	}
	
	//$url = str_replace($url,'http://','');
	
	$cmd = "PLAY2 $url";
	
	/* doesn't quite get converted correctly so doesn't work
	$len = strlen($url);
	
	$data = array(0x7F
	             ,0x0A
	             ,0x00
	             ,0x00  
	             ,$len + 11
	             ,0x20
	             ,ord('M')
	             ,ord('C')
	             ,0x20);

	for($i=0; $i < $len; $i++)	
		$data[] = ord($url[$i]);
	
	$data[] = 0xFF;
	$data[] = 0x0A;
	
	var_dump($data);
		
	$len = count($data);
	
	echo "<P>array length is $len";
	
	for($i=0; $i<=count($data); $i++)
		$packed .= pack('C*',$data[$i]);

	echo "<P>";
	//var_dump($packed);
	
	$hutch = "./vl/hutch/$sn";
	
	if(! is_dir($hutch))
		mkdir($hutch);
	
	$file = "$hutch/play.bin";
	//echo $file;
	if(file_exists($file)) unlink($file);
	
	file_put_contents($file,$packed);
	*/
	
	queueCmd($sn,$min,$cmd,$con,$language,$rabbitID);
	mysqli_close($con);
	
	$wait = 60 - $sec;
	echo "OK. $wait seconds.";
  	exit(0);

}

/************************************************************
 * PLAY MP3
 ************************************************************/
$url=substr($stream,0,244);  //max 244 chars in url

if(strlen($url) > 0)
{
	if($version == '1')  //00:90:4B:8C:6C:6D
	{
	  	echo 'Not supported by V1 rabbits.';
	 	return;
	}
	
	$cmd = "STREAM $url\nFINISH";

	queueCmd($sn,$min,$cmd,$con,$language,$rabbitID);
	
	$wait = 60 - $sec;
	echo "OK. $wait seconds.";
  	return;

}
	
return;

/***********************************************************
 * queue up next command to rabbit.  cmd written by p3.php.
 ***********************************************************/
function queueV1($msg,$rabbitID,$min,$con)
{
	if(strlen($msg) < 1) return;
	
	mysqli_next_result($con);  //required to avoid sync error
	
	$cmd = "call sp_Queue2('" . $rabbitID . "'
			              ,'" . $min . "'
						  ,'" . $msg . "'
						  ,@msg
						  )";
                             
  	$result = queryWithRetry($con,$cmd,$rabbitID,"api.php queue function.");
	
	if (!$result) 
	{
	  	logError("Api.php: Queue query failed. " . mysqli_error($con));
		mysqli_next_result($con);  //required to avoid sync error
		return;
	}
	
	mysqli_next_result($con);  //required to avoid sync error

	$result = mysqli_query($con,"select @msg");

	while($row = mysqli_fetch_row($result))
		$msg = $row[0];

	if($msg != 'OK')
		logError("Api.php: Queue function: $msg");
	
	mysqli_next_result($con);  //required to avoid sync error
	
}

/*****************************************************************
 * queue command to rabbit
 *****************************************************************/
function queueCmd($serNbr,$min,$msg,$con,$lang,$rabbitID)
{
	if(strlen($msg) < 1) 
	{
		echo "You need to enter a command to send to the rabbit. $back";
		return;
	}
	
	$cmd = "call sp_Queue('" . $serNbr . "'
						 ,'" . $min . "'
						 ,'" . $msg . "'
						 ,@msg
						 )";
                                            
	$result = mysqli_query($con,$cmd);
	
	//retry transaction
	if(!$result)
	{
	usleep(1000000); //1 sec
	//usleep(100000);  //100 ms
	
	$result = mysqli_query($con,$cmd);
	}
	
	if (!$result) 
	{
		logError('API.php: Queue function invalid insert after retry: ' . mysqli_error($con));
		mysqli_next_result($con);  //required to avoid sync error
		return;
	}
	
	mysqli_next_result($con);  //required to avoid sync error

	$result = mysqli_query($con,"select @msg");

	while($row = mysqli_fetch_row($result))
	{
		$msg = $row[0];
	}

	if($msg != 'OK')
	{
		logError($msg);
	}
	
	mysqli_next_result($con);  //required to avoid sync error

}

/*****************************************************************
 * queue TTS message to rabbit
 *****************************************************************/
function queue($serNbr,$min,$tts,$con,$lang,$rabbitID)
{
	if(strlen($tts) < 1) 
	{
		echo "You need to enter some text to send to the rabbit. $back";
		return;
	}
	
	$tts = mysqli_real_escape_string($con, $tts);

	$lang = getLanguage($lang);
	
	doTTS($tts,$lang,$serNbr);
		
	$hutch = "./hutch/$serNbr";
    $msg="PLAY $hutch/tts.mp3";

	$cmd = "call sp_Queue('" . $serNbr . "'
											 ,'" . $min . "'
											 ,'" . $msg . "'
											 ,@msg
											 )";
                                            
	$result = mysqli_query($con,$cmd);
	
	//retry transaction
	if(!$result)
	{
	usleep(1000000); //1 sec
	//usleep(100000);  //100 ms
	
	$result = mysqli_query($con,$cmd);
	}
	
	if (!$result) 
	{
		logError('API.php Queue function invalid insert after retry: ' . mysqli_error($con));
		mysqli_next_result($con);  //required to avoid sync error
		return;
	}
	
	mysqli_next_result($con);  //required to avoid sync error

	$result = mysqli_query($con,"select @msg");

	while($row = mysqli_fetch_row($result))
	{
		$msg = $row[0];
	}

	if($msg != 'OK')
	{
		logError($msg);
	}
	
	mysqli_next_result($con);  //required to avoid sync error

}

/***********************************************************
 * write output to bunny and update DB with last action.  V1
 ***********************************************************/
function outV1($out,$rabbitID,$min,$con)
{
	queue($out,$rabbitID,$min,$con); //add item to queue
  
  	$clean_msg = mysqli_real_escape_string($con, $out);

	$cmd = "call sp_SetLastCommand($rabbitID,'" . $clean_msg . "',@msg)";

	$result = mysqli_query($con,$cmd);

	if (!$result) 
	{
		logError('Out function invalid update last Command query: ' . mysqli_error($con));
		mysqli_next_result($con);  //required to avoid sync error
		return;
	}
	
	mysqli_next_result($con);  //required to avoid sync error

	$result = mysqli_query($con,"select @msg");

	while($row = mysqli_fetch_row($result))
		$msg = $row[0];
	
	if($msg != 'OK')
		logError($msg);
	
	mysqli_next_result($con);  //required to avoid sync error
}


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
	
	$file="$hutch/tts.mp3";
	writeToFile($file,$response);
}	

?>
</html>
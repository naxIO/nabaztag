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
session_start();
?>
<html>
<link rel="stylesheet" type="text/css" href="main.css" />
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<Form Name ="frmStream" Method ="POST" ACTION = "stream_post.php">
<div="standalone">
<body>
<?php
//error_reporting(E_ALL);  //don't use this in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log','../etc/nabaztag_error.log');
//error_reporting(0);  //don't use this, use ini_set or nothing goes to the log

$back=" Click your browser's back button to go back and correct this.";

$serialNbr   = $_REQUEST['hidSerialNbr'];
$url         = $_REQUEST['txtURL'];

if(strlen($url) < 1)
{
	echo 'URL cannot be blank. ' . $back;
	return;
}

$protocol = substr($url,0,4);

if($protocol != 'http' && $protocol != 'HTTP')
{
	echo 'URL must start with HTTP. ' . $back;
	return;
}

include '../etc/nabaztag_db.php';

$con = mysqli_connect($host,$user,$pass,$db);
if (!$con) die('Could not connect: ' . mysqli_error());


if(strlen($serialNbr) < 12)
{
	$serialNbr = $_REQUEST['txtSerNbr'];
	
	if(substr_count($serialNbr, ':') < 5)
	{
		echo "'$serNbr' is not a valid serial number.  Remember to include the colons.  $back";
		return;
	};
	
	if(strlen($serialNbr) < 17)
	{
		echo "'$serialNbr' is not a valid serial number. $back";
		return;
	};
}

include 'clean.php';

$_SESSION['rawSerialNbr'] = $serialNbr;
$serNbr = clean($serialNbr);
$result = mysqli_query($con,"call sp_GetRabbit('$serNbr')");
if (!$result) die('Invalid getRabbit query: ' . mysqli_error($con));

while($row = mysqli_fetch_row($result))
{
	$rabbitID = $row[0];
	$name = $row[1];
	$time = $row[2];
	$weather = $row[3];
	$email = $row[4];
	$sleepHour = $row[5];
	$wakeHour = $row[6];
	$language = $row[7];
	$temp     = $row[8];
	$bottomColor = $row[10];
	$idle = $row[11];
	$weekendWakeHour = $row[12];
}

if(intval($rabbitID) < 1)
{
	echo "Whoops!  We can't find your rabbit.  Did you enter the correct serial number? $back\n";
	$_SESSION['rawSerialNbr'] = '';
	return;
}

mysqli_next_result($con);
mysqli_close($con);

//date_default_timezone_set('America/Chicago'); //for warning
date_default_timezone_set($time); //for warning

$hour = date("H"); //military 00-23
$min  = date("i"); //minute 00-59
$sec  = date("s"); //secs 00-59

queue("STREAM " . $url,$rabbitID,$min);

echo 'Sent to rabbit.<P><a href="index.php">Home</a>';

/***********************************************************
 * queue up next command to rabbit
 ***********************************************************/
function queue($cmd,$rabbitID,$min)
{
	if(strlen($cmd) < 1) return;
	
	include '../etc/nabaztag_db.php';
	
	$con = mysqli_connect($host,$user,$pass,$db);
	if (!$con) 
	{
		logError('Queue function could not connect: ' . mysqli_error($con));
		return;
 	}
 	
 	//add to queue if not already handled for minute
 	$cmd = "insert into queue (fkRabbitID,cmd,minute) "
 	     . "select $rabbitID,'" . mysqli_real_escape_string($con,$cmd) . "',$min "
 	     . "from dual " 
 	     . "where not exists(select 1 from queue " 
 	     .                  "where fkRabbitID = $rabbitID "
 	     .                  "and minute = $min and sent = 1);";

	//echo $cmd;
	
	$result = mysqli_query($con,$cmd);

	if (!$result) 
	{
		logError('Queue function invalid insert Command query: ' . mysqli_error($con));
		mysqli_next_result($con);  //required to avoid sync error
		mysqli_close($con);
		return;
	}
	
	mysqli_next_result($con);  //required to avoid sync error
	mysqli_close($con);
}

?>
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

//*********************************************************************
//* 12/23/12 1.35 CCK - Added cheerlights.
//*********************************************************************
//error_reporting(E_ALL);  //don't use this in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log','../../../etc/nabaztag_error.log');
//error_reporting(0);  //don't use this, use ini_set or nothing goes to the log

include '../../subroutines/clean.php';
include '../../subroutines/getLanguage.php';
include '../../subroutines/goCurl.php';
include '../../subroutines/writeToFile.php';
include '../../subroutines/getRSSFeed.php';
include '../../subroutines/queryWithRetry.php';
include '../../subroutines/getFollow.php';
include '../../../etc/nabaztag_db.php';
    
$msg_idle = '7fffffff';
$url = $_SERVER["REQUEST_URI"];

if(isset($_GET['sd'])) $sd = $_GET['sd'];  //for button press event
$tc = $_GET['tc'];
$sn = strtolower($_GET['sn']); //serial #
$file = "output.txt";
$debug=false;

if(strlen($sn) == 0)
{
    echo 'no sn';
    return;
}

date_default_timezone_set('America/Chicago'); //for warning

$hour = date("H"); //military 00-23
$min  = date("i"); //minute 00-59
$sec  = date("s"); //secs 00-59

//lookup the rabbit by serial #

ini_set('log_errors', 0);  //localhost error
$con = mysqli_connect($host,$user,$pass,$db);

$max=4;
$retry=0;

while(!$con && $retry < $max)
{
    usleep(1000000);  //1 sec
    $con = mysqli_connect($host,$user,$pass,$db);
    $retry++;
}

ini_set('log_errors', 1);

//end cck

if (!$con) 
{
    logError("P4.php could not connect after $max attempts. " . mysqli_connect_errno() . mysqli_connect_error());
    return;
}

$ip = $_SERVER['REMOTE_ADDR'];
$request = $_SERVER['REQUEST_URI'];

/************************************
 * log visit
 ***********************************/
$cmd = "call sp_LogConnect('" . $sn . "','" . $ip . "','" . $request . "',@rabbitID)";
if($debug) echo $cmd . '<p>';
$result = mysqli_query($con,$cmd);
if (!$result) 
    logError("Invalid log proc call for sn $sn. " . mysqli_error($con));

$result = mysqli_query($con,"select @rabbitID");
if (!$result) 
    logError('Invalid log query: ' . mysqli_error($con));

while($row = mysqli_fetch_row($result))
{
    $rabbitID = $row[0];
}

if($rabbitID < 1)  //rows found in DB
{
    echo "No rabbit";
    mysqli_close($con);
    return;
}

mysqli_next_result($con);  //required to avoid sync error

/*************************************
 * get rabbit
 *************************************/
$cmd = "call sp_GetRabbit('" . $sn. "')";

$result = mysqli_query($con,$cmd);

if (!$result)
{
    logError('Invalid getRabbit query: ' . mysqli_error($con));
    mysqli_close($con);
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
    $buttonAction = $row[13];
    $reboot = $row[15];
    $follow = $row[16];
    $version = $row[17];
      $wavTimeout = $row[18];
      $clockType = $row[19];
}

switch($language)
{
    case('english-uk'):
        $broad='broad';
        $lang='uk';
        break;
        
    case('english-us'):
        $broad='broad_us';
        $lang='us';
        break;
    
    case('espanol-es'):
        $broad='broad_es';
        $lang='es';
        break;
    
    case('deutsch-de'):
        $broad='broad_de';
        $lang='de';
        break;
    
    case('italiano-it'):
        $broad='broad_it';
        $lang='it';
        break;
    
    default:
        $broad='broad';
        $lang='uk';
        break;
}

mysqli_next_result($con);  //required to avoid sync error

/********************************************************
 * set the version of the rabbit V1 if not set already
 ********************************************************/
$cmd = "call sp_SetVersion($rabbitID, 1);";

$result = mysqli_query($con,$cmd);

if (!$result)
    logError('Invalid update rabbit version query: ' . mysqli_error($con));

mysqli_next_result($con);  //required to avoid sync error

//delete cmd if older than 3 mins

$cmd = "call sp_DelQueue($rabbitID);";

$result = mysqli_query($con,$cmd);

if (!$result)
    logError('Invalid delete queue query: ' . mysqli_error($con));

mysqli_next_result($con);  //required to avoid sync error

date_default_timezone_set("$timeZone");  //sets to local time zone e.g london
$hour = date("H",time());
$min  = date("i",time());
$day = date("D",time());

//////////////////////////////////////////////////
//check for sleep
//////////////////////////////////////////////////
if($debug) echo "<br>hour = $hour<br>";

if($sleepHour == '00') $sleepHour='24';

if($hour == $sleepHour && $min == '00' && intval($sec) < 41) 
{
    out2('./bin2/sleep.bin',$rabbitID,$con);
    mysqli_close($con);
    return;
}

if(intval($hour) == intval($sleepHour))  //into sleep?
{
    out2('./bin2/sleep.bin',$rabbitID,$con);
    mysqli_close($con);
    return;
}

if($timeZone == 'Asia/Dubai')
{
    if($day == 'Fri' || $day == 'Sat') 
        if(! is_null($weekendWakeHour))
            $wakeHour = $weekendWakeHour;
}
else //rest of the world
{
if($day == 'Sat' || $day == 'Sun') 
    if(! is_null($weekendWakeHour))
        $wakeHour = $weekendWakeHour;
}

//graveyard - sleep 3 AM, wake 5 AM
if(intval($sleepHour) < intval($wakeHour)) 
{
    if(intval($hour) >= intval($sleepHour) && intval($hour) < intval($wakeHour))
    {
        out2('./bin2/sleep.bin',$rabbitID,$con);
        mysqli_close($con);
        return;
    }
}

//normal people
if(intval($sleepHour) > intval($wakeHour)) 
{
    if(intval($hour) >= intval($sleepHour))  
    {
        out2('./bin2/sleep.bin',$rabbitID,$con);
        mysqli_close($con);
        return;
    }
    
    if(intval($hour) < intval($wakeHour))
    {
        out2('./bin2/sleep.bin',$rabbitID,$con);
        mysqli_close($con);
        return;
    }
}

if($wakeHour == '00') $wakeHour = '24';

//if($hour == $wakeHour && $min == '00')
    //out2("EARSUP",$rabbitID,$con);  //wake up



/*********************************************
 * get schedules
 *********************************************/
$cmd = "call sp_GetSchedule($rabbitID)";
if($debug) echo $cmd . '<p>';
$result = mysqli_query($con,$cmd);
if (!$result) logError('Invalid getSchedule query: ' . mysqli_error($con));

while($row = mysqli_fetch_row($result))
{
    $enabled    = $row[0];
    $fromHour   = $row[1];
    $toHour     = $row[2];
    $minute     = $row[3];
    $description= $row[4];
    $seq        = $row[5];
    $msg        = $row[6];

    if($enabled==1)
    {
        if($toHour == '00') $toHour = $hour;  //round the clock
    
        $rss = '';
        $hutch = "../hutch/$sn";
        
        if((intval($hour) >= intval($fromHour)) && (intval($hour) <= intval($toHour)) && ($minute == $min)  && (intval($sec) < 59)) 
            invokeFunction($description,$hour,$min, $sec,$rabbitID,$sn,$broad,$lang,$con,$degrees,$weatherCode,$rss,$hutch,$follow,true);
        
    }
}

mysqli_next_result($con);  //required to avoid sync error

//get next from queue
//note you may need to send the minute to the proc if you get duplicate messages 
 
$cmd = "call sp_GetQueue($rabbitID);";             
$result = queryWithRetry($con,$cmd,$name,"P3 queue select.");
        
if (!$result) 
{
    logError('Error in queryWithRetry. ' . mysqli_error($con));
    mysqli_close($con);
    return;
}

$msg='';

while($row = mysqli_fetch_row($result))
{
    $id  = $row[0];
    $msg = $row[1];
}

mysqli_next_result($con);  //required to avoid sync error

if(strlen($msg) > 0)
{
    $msg = file_get_contents($msg);
    echo $msg;

    //flag cmd just sent to rabbit
    $cmd = "call sp_UpdQueue($id, @msg);";    
    $result = mysqli_query($con,$cmd);
    
    if (!$result) 
    {
        logError('Queue function update failed: ' . mysqli_error($con));
        return;
    }
    
    $result = mysqli_query($con,"select @msg");

    while($row = mysqli_fetch_row($result))
        $msg = $row[0];
    
    if($msg != 'OK') logError($msg);
    
    return;       //no idle action if msg was sent
}

/////////////////////////////////////////////////////////
// Idle action
/////////////////////////////////////////////////////////

if(
 $min != '00' && $min != '15' && $min != '30' && $min != '45' &&
 $min != '10' && $min != '20' && $min != '40' && $min != '50' &&
 $min != '05' && $min != '25' && $min != '35' && $min != '55' 
 )
  {
      
      if($min == '59')  //help crashing
      {
            out2('./bin2/nop.bin',$rabbitID,$con);
     }
      else
      {
      
        switch($idleAction)
        {
            case 'Test Loop (V1 only)':
                testLoop($rabbitID, $min,$con);
                break;
    
            case 'Cheerlights':
                cheerlights($rabbitID,$min,$con,$sn);
                break;
                
            default: //send the nop.  this must be sent or the rabbit will not connect
                out('./bin2/nop.bin',$rabbitID,$min,$con);
                //out2('./bin2/nop.bin',$rabbitID,$con);
                break;
        }
    }        
}

mysqli_close($con);
return;

//////////////////////////////////////////////////////////////
// SUBROUTINES
//////////////////////////////////////////////////////////////

//*************************************************
// cheerlights - see cheerlights.com
//*************************************************
function cheerlights($rabbitID,$min,$con,$sn)
{
    $request='http://api.thingspeak.com/channels/1417/field/1/last.txt';
    $color = goCurl($request);

    if(strlen($color) < 1) 
    {
        out('./bin2/nop.bin',$rabbitID,$min,$con);
        return;
    }
    
    //echo "Received $color from cheerlights<P>";
    
    switch($color)
    {
        case "red":
            $clr = 1;
            break;
        
        case "green":
            $clr = 2;
            break;
    
        case "yellow":
            $clr = 3;  
            break;
                
        case "blue":
            $clr = 4;
            break;
    
        case "purple":
            $clr = 5;  
            break;
            
        case "cyan":
            $clr = 6;
            break;
            
        case "white":
            $clr = 7;  
            break;
            
        case "warmwhite":
            $clr = 11; 
            break;
    
        case "orange":
            $clr = 15;  
            break;
            
        case "magenta":
            $clr = 25;  
            break;
            
        default:
            $clr=2;
            break;
    
    }
    
    $out =     "./bin2/cheer.bin";
    $msg = file_get_contents($out);
    
    $data = unpack('C*', $msg);
    
    //var_dump($data);
    
    $data[42] = $clr; //color of LEDs 
        
    $data = compile($data);  //set the checksum
    
    $packed = '';
    
    for($i=1; $i<=count($data); $i++)
        $packed .= pack('C*',$data[$i]);

    $hutch = "../hutch/$sn";

    if(! is_dir($hutch)) mkdir($hutch);
    
    $file="$hutch/cheer.bin";
    
    if(file_exists($file)) unlink($file);
        
    file_put_contents($file,$packed);
    
    //$file="./bin2/cheerlights$clr.bin";
    
    out($file,$rabbitID,$min,$con);
    //out2($file,$rabbitID,$con);
    
    
    
    
    
}


/****************************************
 * Set correct checksum
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




//*************************************************
// test mode - pick a random file to play for #2
//*************************************************
function testLoop($rabbitID, $min,$con)
{
    $dir = "./bin/"; 
    
    if (glob($dir . '*.bin') != false)
    {
        $filecount = count(glob($dir . "*.bin"));
    
        if($filecount > 0)
        {
            $nbr = mt_rand(2,$filecount);  //not sure what 1 is, speech?
            $file = (string)$nbr . ".bin";
            $rand = $dir . $file;
        }
                
        if(!file_exists($rand))
        {
            logError($file . ' does not exist');
            mysqli_close($con);
            return;
        }
                    
        out($dir . $file,$rabbitID,$min,$con);
    }
        
    mysqli_next_result($con);  //required to avoid sync error
}
    
/***********************************************************
 * queue up next command to rabbit
 ***********************************************************/
function queue($msg,$rabbitID,$min,$con)
{
    if(strlen($msg) < 1) return;
    
    //echo "msg=$msg<br>rabbitID=$rabbitID<br>min=$min<br>";
    
    mysqli_next_result($con);  //required to avoid sync error
    
    $cmd = "call sp_Queue2('" . $rabbitID . "'
                              ,'" . $min . "'
                                               ,'" . $msg . "'
                                              ,@msg
                                              )";
                             
  //http://yorokobu.es/                           
    $result = queryWithRetry($con,$cmd,$rabbitID,"P3 queue function.");
    
    if (!$result) 
    {
      logError("Queue query failed. " . mysqli_error($con));
        mysqli_next_result($con);  //required to avoid sync error
        return;
    }
    
    mysqli_next_result($con);  //required to avoid sync error

    $result = mysqli_query($con,"select @msg");

    while($row = mysqli_fetch_row($result))
        $msg = $row[0];

    if($msg != 'OK')
        logError("Queue function: $msg");
    
    mysqli_next_result($con);  //required to avoid sync error
    //mysqli_close($con);
}

/***********************************************************
 * write output to bunny and update DB with last action
 ***********************************************************/
function out($out,$rabbitID,$min,$con)
{
    queue($out,$rabbitID,$min,$con); //add item to queue
    setLastCmd($con, $out,$rabbitID);
      
}

/*************************************************************
 * Set the last command sent in the DB
 *************************************************************/
function setLastCmd($con, $out, $rabbitID)
{
    $clean_msg = mysqli_real_escape_string($con, $out);

    $cmd = "call sp_SetLastCommand($rabbitID,'" . $clean_msg . "',@msg)";

    $result = mysqli_query($con,$cmd);

    if (!$result) 
    {
        logError('setLastCmd: invalid update last Command query: ' . mysqli_error($con));
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

/****************************************************************
 * write output to bunny and update DB with last action. no queue
 ****************************************************************/
function out2($out,$rabbitID,$con)
{
    if(!file_exists($out))
    {
        logError("Error in p3.out2. $out does not exist");
        return;
    }
    
    setLastCmd($con, $out, $rabbitID);

    $msg = file_get_contents($out);
      echo $msg;
}        
    

/************************************************************
 * Tell time
 ************************************************************/
function tellTime($hour,$sec,$rabbitID,$sn,$broad,$lang,$min,$con)
{
    //speak the time. currently only 1 can be sent at a time
    
    $dir = "../$broad/config/clock/$lang/" . $hour . "/";   
    $time1 = $dir . "1.bin";
    out("$time1",$rabbitID,$min,$con);
        
}

/************************************************************
 * Tell time no queue
 ************************************************************/
function tellTime2($hour,$sec,$rabbitID,$sn,$broad,$lang,$min,$con)
{
    //speak the time. currently only 1 can be sent at a time
    
    $dir = "../$broad/config/clock/$lang/" . $hour . "/";   
    $time1 = $dir . "1.bin";
    out2("$time1",$rabbitID,$con);
        
}

/*****************************************
 * Do taichi
 *****************************************/
function doTaichi($rabbitID,$min,$con)
{
    out('./bin2/taichi.bin',$rabbitID,$min,$con);
    return;
}


/////////////////////////////////////////////////////////////////////////
// Evaluate function and invoke
/////////////////////////////////////////////////////////////////////////
function invokeFunction($description,$hour,$min, $sec,$rabbitID,$sn,$broad,$lang,$con,$degrees,$weatherCode,$rss,$hutch,$follow,$queue)
{
    switch($description)
    {
        case "Taichi":
            doTaichi($rabbitID,$min,$con);
            break;    
    
        case "Tell the time":
            tellTime($hour,$sec,$rabbitID,$sn,$broad,$lang,$min,$con);
            break;
    
        case "Test Loop (V1 only)":
            testLoop($rabbitID,$min,$con);
            break;    
    
    
    
    }

/*
        case "Random announcement":
            randomMsg($rabbitID,$broad,$lang,$min,$queue,$con);
            break;

        case "Random arcade sound":
            randomArcade($rabbitID,$min,$con,$queue);
            break;
        
        case "Random Star Trek sound":
            randomTrek($rabbitID,$sn,$min,$queue,$con);
            break;
            
        case "Weather forecast":
            doWeather($weatherCode,$sn,$degrees,$rabbitID,$broad,$lang,$min,$hour,$queue,$con);
            break;
    
        case "Current temperature":
            doCurrentWeather($weatherCode,$sn,$degrees,$rabbitID,$broad,$lang,$min,$hour,$queue,$con);
            break;
    
        case "Dow Jones Industrial Average":
            dowJones($rabbitID,$broad,$lang,$min,$sn,$queue,$con);
            break;
            
        case 'BBC World News headlines':
            doRSSFeed('http://feeds.bbci.co.uk/news/world/rss.xml?edition=uk',$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);  
            break;
            
        case 'Engadget headlines':
            doRSSFeed('http://www.engadget.com/rss.xml',$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);  
            break;
    
        case 'RSS feed 1':
            doRSSFeed($rss[0],$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);  
            break;
    
        case 'RSS feed 2':
            doRSSFeed($rss[1],$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);  
            break;
    
        case 'RSS feed 3':
            doRSSFeed($rss[2],$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);  
            break;
            
        case 'Twitter Follow':
            doFollow($follow,$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);
            break;
    }
    */
}

//log error.  You must create file and permission in advance.
function logError($msg)
{
    $date = date("Y-m-d H:i:s");
    $msg = "[$date] $msg \n";
    $file='../../../etc/nabaztag_error.log';
    error_log($msg,3,$file);
}

    
?> 
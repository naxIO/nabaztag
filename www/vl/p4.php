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
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log','../../etc/nabaztag_error.log');
//error_reporting(0);  //don't use this, use ini_set or nothing goes to the log

include '../subroutines/clean.php';
include '../subroutines/getLanguage.php';
include '../subroutines/goCurl.php';
include '../subroutines/writeToFile.php';
include '../subroutines/getRSSFeed.php';
include '../subroutines/queryWithRetry.php';
include '../subroutines/getFollow.php';

$msg_idle = '7fffffff';
$url = $_SERVER["REQUEST_URI"];

if(isset($_GET['sd'])) 
    $sd = $_GET['sd'];  //for button press event
else
    $sd = 0;
    
$sn = $_GET['sn']; //serial #

if(isset($_GET['rf']))  //RFID tag
    $rfid = $_GET['rf'];
else
    $rfid = '';

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

$hutch = "./hutch/$sn";
if(! is_dir($hutch)) mkdir($hutch);

include '../../etc/nabaztag_db.php';

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

$result = mysqli_query($con,$cmd);

if (!$result) 
    logError("P4.php: Invalid log proc call for sn $sn. " . mysqli_error($con));

$result = mysqli_query($con,"select @rabbitID");

if (!$result) 
{
    logError('P4.php: Invalid log query: ' . mysqli_error($con));
    return;
}

while($row = mysqli_fetch_row($result))
{
    $rabbitID = $row[0];
}

if($rabbitID < 1)  //rows found in DB
{
    echo "No rabbit";
    return;
}

/*************************************
 * get rabbit
 *************************************/
$cmd = "call sp_GetRabbit('" . $sn. "')";
if($debug) echo $cmd . '<p>';
$result = mysqli_query($con,$cmd);

if (!$result) 
{
    logError('Invalid getRabbit query: ' . mysqli_error($con));
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

if($sn == '000000000000')  //for testing
{
    //doWeather($weatherCode,$sn,$degrees,$rabbitID,$broad,$lang,$min,$hour,false,$con);  //false=echo
    //doFollow($follow,$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);
        
    //out2("PLAY ./arcade/26.mp3\nFINISH\nPLAY ./arcade/26.mp3\nFINISH\nPLAY ./arcade/26.mp3\nFINISH\n");
    //$ans = shell_exec('./lame');
    //logError($ans);
    
    /*
    //$file='try2.msg'; //try5.msg'; 
    //$msg = file_get_contents($file);
    //echo $msg;
    //out2('STREAM rush.mp3',1,$con);
    */
    
    //doRSSFeed('http://feeds.bbci.co.uk/news/world/rss.xml?edition=uk',$sn,$rabbitID,$lang,$min,$hour,false,$hutch);
    //doRSSFeed('http://www.engadget.com/rss.xml',$sn,$rabbitID,$lang,$min,$hour,false);
    
    //out('STREAM 188.40.41.39/stream/webstream-promo.mp3',12);
    //out('STREAM http://212.125.100.52:8000/radiofantasy.mp3',12);
    //out('STREAM http://downloads.bbc.co.uk/podcasts/worldservice/globalnews/globalnews_20110831-1634a.mp3',1);
    //out('CHORSTREAM',$rabbitID);
    //out('BOTTOMCOLOR GREEN');
    //out2('NETTIME 10000',1,$con);
    //out2("PLAY bbc.mp3",1,$con);
    //out("PLAY $file\nNETTIME $wavTimeout",$rabbitID,$min,$con);
    //out2('PLAY rush.mp3',1,$con);
    //out2('PLAY warp.wav',1,$con); //works
    //doFollow("from:vloxy",$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);
    //out2('PLAY bbc.mp3',1,$con);
    //tellTime($hour,$sec,$rabbitID,$sn,$broad,$lang,$min,$con,$wavTimeout,$clockType);
    //out2('NETTIME 5000',1,$con);
  
    //dowJones(1,'broad','uk',$min,$sn,false,$con);
 
    //$msg = '\$00\$00\$0f\$6f\$00';  //skip 1st five header and waiting 
    //$msg = $msg . '\$01\$0A\$00\$00\$08\$01\$04\$01\$07\$04\$00\$33\$ff';
    //echo $taichi;

    /*
    $file='restart.msg'; //'try2.msg';
    $msg = file_get_contents($file);
    echo $msg;
    */
    //return;
}

if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.
   
/********************************************************
 * set the version of the rabbit V2 if not set already
 ********************************************************/
if($version == 0)  //default DB value is 0
{
    $cmd = "call sp_SetVersion($rabbitID,2);";

    $result = mysqli_query($con,$cmd);

    if (!$result) 
    {   
        logError('p4.php: Invalid update rabbit version query: ' . mysqli_error($con));
        return;
    }

    if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.
}

/********************************************************
 * delete queue items older than 3 mins
 ********************************************************/
$cmd = "call sp_DelQueue($rabbitID)";

$result = mysqli_query($con,$cmd);

if (!$result)
{
    logError('p4.php: Invalid delete queue query: ' . mysqli_error($con));
    return;
}

if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.

date_default_timezone_set("$timeZone");  //sets to local time zone of rabbit e.g london
$hour = date("H",time());
$min  = date("i",time());
$day  = date("D",time());

/********************************************************
 * check for reboot flag
 ********************************************************/
if($hour == '23' && $min == '59' & $reboot==1)
{
    $cmd = "call sp_PurgeQueue('$sn',@msg)";
  
    $result = queryWithRetry($con,$cmd,$name,"P4 queue purge.");
        
    if (!$result) return;
    
    $result = mysqli_query($con,"select @msg");

    while($row = mysqli_fetch_row($result)) $msg = $row[0];

    if($msg != 'OK') logError("P4 purge queue failed with $msg for $name");
    
    $file='restart.msg'; 
    $msg = file_get_contents($file);
  
    out2($msg,$rabbitID,$con);
    return;
}

/********************************************************
 * check for sleep
 ********************************************************/

if($debug) echo "<br>hour = $hour<br>";

if($sleepHour == '00') $sleepHour='24';

if($hour == $sleepHour && $min == '00' && intval($sec) < 41) 
{
    //out2("EARSUP\nWAIT 5000\nEARSDOWN\nPLAY ./$broad/config/surprise/$lang/1.mp3",$rabbitID,$min);  //crashed with all orange and blinking bottom orange/green
    out2("EARSDOWN\nPLAY ./$broad/config/surprise/$lang/1.mp3",$rabbitID,$con);
    return;
}

if(intval($hour) == intval($sleepHour))  //into sleep?
{
    out2("SLEEP",$rabbitID,$con);
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
        out2('SLEEP',$rabbitID,$con);
        return;
    }
}

//normal people
if(intval($sleepHour) > intval($wakeHour)) 
{
    if(intval($hour) >= intval($sleepHour))  
    {
        out2('SLEEP',$rabbitID,$con);
        return;
    }
    
    if(intval($hour) < intval($wakeHour))
    {
        out2('SLEEP',$rabbitID,$con);
        return;
    }
}

if($wakeHour == '00') $wakeHour = '24';

if($hour == $wakeHour && $min == '00')
    out2("EARSUP",$rabbitID,$con);  //wake up

/********************************************************
 * check for button press
 ********************************************************/

if($sd == 3)
{
    switch($buttonAction)
    {
        case 'Weather forecast':
            doWeather($weatherCode,$sn,$degrees,$rabbitID,$broad,$lang,$min,$hour,false,$con);  //false=echo
            return;
    
        case 'BBC World News headlines':
            doRSSFeed('http://feeds.bbci.co.uk/news/world/rss.xml?edition=uk',$rabbitID,$lang,$min,$hour,false,$con,$hutch);  
            return;
    
        case 'Engadget headlines':
            doRSSFeed('http://www.engadget.com/rss.xml',$rabbitID,$lang,$min,$hour,false,$con,$hutch);  
            return;
    
          case 'Current temperature':
            doCurrentWeather($weatherCode,$sn,$degrees,$rabbitID,$broad,$lang,$min,$hour,false,$con);  
            return;
    
        case 'Dow Jones Industrial Average':
            dowJones($rabbitID,$broad,$lang,$min,$sn,false,$con);
            return;
    
        case 'Random announcement':
            randomMsg($rabbitID,$broad,$lang,$min,false,$con,$sn);
            return;
            
        case "Random Star Trek sound":
            randomTrek($rabbitID,$sn,$min,false,$con);
            return;
                
        case 'Twitter Follow':
            doFollow($follow,$rabbitID,$lang,$min,$hour,false,$con,$hutch);
            return;
            
    }
}

if(($min == '10') || ($min == '20') || ($min == '40') || ($min == '50')) //set bottomColor
{
    if($bottomColor != 'Violet')  //did they choose a custom color?
    {
        
        if($bottomColor == 'Random with arcade sound')
        {
            $bottomColor = 'Random';
            randomArcade($rabbitID,$min,$con,true);
        }
        
        if($bottomColor == 'Random')
        {
            //white is ugly, blue kills the LED for some reason in the real rabbit
            
            $cmd="call sp_GetRandomColor();";
            
            $result = mysqli_query($con,$cmd);
            
            if (!$result) 
            {
                logError('Invalid get color query: ' . mysqli_error($con));
                return;
            }
            
            $i=1;
            $nbr = rand(1,5);  //note this 5 should not be hard coded
            
            while($row = mysqli_fetch_row($result))
            {
                if($i == $nbr) $bottomColor = $row[0];
                $i=$i+1;
            }
            
            if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.

        }
        
        out2("BOTTOMCOLOR $bottomColor",$rabbitID,$con);  //double message
    }        
}

/********************************************************
 * get RSS
 ********************************************************/

$cmd = "call sp_GetRSS($rabbitID)";
if($debug) echo $cmd . '<p>';
$result = mysqli_query($con,$cmd);
if (!$result) 
{
    logError('Invalid getRSS query: ' . mysqli_error($con));
    return;
}

$rss = array();

while($row = mysqli_fetch_row($result))
{
    $rss_enabled = $row[0];
    $rss[]       = $row[1];
}

if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.

/*************************************************
 * Check for RFID scan
 *************************************************/
if(strlen($rfid) > 0)
{    
    //queue must be used for RFID because of the read speed is very fast and results in duplicate calls
    if($rfid == '0000000000000000')  //bad read
        return;
    
    $cmd = "call sp_CaptureRFID($rabbitID,'" . $rfid . "')";
    $result = queryWithRetry($con,$cmd,$name,"P4 capture RFID.");
    
    if(!$result) return;
    
    //get existing mappings for tags, if any
    $cmd = "call sp_GetRFID($rabbitID)";
    $result = queryWithRetry($con,$cmd,$name,"P4 get RFID.");
    
    if(!$result) return;
    
    while($row = mysqli_fetch_row($result))
    {
        $tag         = $row[0];
        $description = $row[1];
        
        if($rfid == $tag)  //already have it so play function
        {
            invokeFunction($description,$hour,$min,$sec,$rabbitID,$sn,$broad,$lang,$con,$degrees,$weatherCode,$rss,$hutch,$follow,$wavTimeout,$clockType,true);
            return;
        }
    }
}

/*********************************************
 * get schedules
 *********************************************/
$cmd = "call sp_GetSchedule($rabbitID)";
if($debug) echo $cmd . '<p>';
$result = mysqli_query($con,$cmd);
if (!$result) 
{
    logError('Invalid getSchedule query: ' . mysqli_error($con));
    return;
}

while($row = mysqli_fetch_row($result))
{
    $enabled    = $row[0];
    $fromHour   = $row[1];
    $toHour     = $row[2];
    $minute     = $row[3];
    $description= $row[4];
    $seq        = $row[5];
    $msg        = $row[6];

    if($toHour == '00') $toHour = $hour;  //round the clock
       
    if((intval($hour) >= intval($fromHour)) && (intval($hour) <= intval($toHour)) && ($minute == $min)  && (intval($sec) < 59)) 
        invokeFunction($description,$hour,$min, $sec,$rabbitID,$sn,$broad,$lang,$con,$degrees,$weatherCode,$rss,$hutch,$follow,$wavTimeout,$clockType,true);

}

if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.

//get next from queue

$cmd="call sp_GetQueue($rabbitID)";       

$result = mysqli_query($con,$cmd);

if (!$result) 
{
    logError('p4.php: Get Queue function failed: ' . mysqli_error($con));
    return;
}

$msg='';

while($row = mysqli_fetch_row($result))
{
    $id  = $row[0];
    $msg = $row[1];
 }

if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.

if(strlen($msg) > 0)
{
    echo $msg;  //send to rabbit
    
    //flag cmd just sent to rabbit as sent
    
    $cmd = "call sp_UpdQueue($id, @msg);";
    
    $result = mysqli_query($con,$cmd);
    
    if (!$result) 
    {
        logError('p4.php: Queue function update failed: ' . mysqli_error($con));
        return;
    }
    
    $result = mysqli_query($con,"select @msg");

    while($row = mysqli_fetch_row($result)) $msg = $row[0];

    if($msg != 'OK') logError("p4.php: Queue function update failed: $msg");
   
    return;  //no idle action if msg was sent
}

//idle action

if($min != '00' && $min != '15' && $min != '30' && $min != '45' &&
   $min != '01' && $min != '16' && $min != '31' && $min != '46' &&
   $min != '10' && $min != '20' && $min != '40' && $min != '50' && 
   $min != '11' && $min != '21' && $min != '41' && $min != '51' && 
   $min != '05' && $min != '25' && $min != '35' && $min != '55' &&
   $min != '06' && $min != '26' && $min != '36' && $min != '56')

/*
if($min != '00' && $min != '15' && $min != '30' && $min != '45' &&
   $min != '10' && $min != '20' && $min != '40' && $min != '50' && 
   $min != '05' && $min != '25' && $min != '35' && $min != '55')
*/

if(strlen($msg) < 1)
{
    switch($idleAction)
    {
        case 'PacMan Lights':
            pacMan($min);
            break;
            
        case 'PacMan Lights & Twitchy Ears':
            twitchEars($min);
            pacMan($min);
            break;
    
        case 'Twitchy Ears':
            twitchEars($min);
            break;
            
        case 'Weather Lights & Twitchy Ears':
            twitchEars($min);
            weatherLights($rabbitID,$sn);
            break;
    
        case 'Weather Lights':
            weatherLights($rabbitID,$sn);
            break;
            
        case 'Cheerlights':
            cheerLights($rabbitID,$sn);
            break;
    }        
}

//mysqli_close($con);  //php will free all connections upon script completion
exit(0);

/***********************************************************
 * queue up next command to rabbit
 ***********************************************************/
function queue($msg,$rabbitID,$min,$con)
{
    if(strlen($msg) < 1) return;
    
    $cmd = "call sp_Queue2('" . $rabbitID . "'
                          ,'" . $min . "'
                           ,'" . $msg . "'
                          ,@msg
                          )";
                             
    $result = queryWithRetry($con,$cmd,$rabbitID,"P4 queue function.");
    
    if (!$result) return;
    
    $result = mysqli_query($con,"select @msg");

    while($row = mysqli_fetch_row($result))
        $msg = $row[0];

    if($msg != 'OK')
        logError("Queue function: $msg");
    
}


/*****************************************************************
 * BBC World news RSS feed thru google
 *****************************************************************/
function doRSSFeed($url,$rabbitID,$lang,$min,$hour,$queue,$con,$hutch) 
{
    $lang = getLanguage($lang);
    $t = getRSSFeed($url,'title');
    $max=4;
    
    if(strlen($t[0]) < 1) 
        return; //invalid RSS feed
    
    for($i=0; $i < $max; $i++)
    {
        //send to google TTS
        $request = "http://translate.google.com/translate_tts?tl=$lang&q=" . urlencode($t[$i]);
        
        //curl and save to mp3
        $response = goCurl($request);
        $file="$hutch/rss$i.mp3";
        
        writeToFile($file,$response);
    }
    
    //combine to one mp3
    /*
    $fca = array();
    for($i=0; $i < $max; $i++)
        $fca[] = "$hutch/rss$i.mp3";
    
    $file="$hutch/rss.mp3";
    if(file_exists($file)) unlink($file);
    
    $fh = fopen($file, 'w') or die("can't open $file");
    
    for($i=0; $i < count($fca); $i++)
         fwrite($fh,file_get_contents($fca[$i]));
  
    fclose($fh);
    */
    
    $fca = array();
    
    for($i=0; $i < $max; $i++)
        $fca[] = "$hutch/rss$i.mp3";
        
    $file="$hutch/rss_MP3WRAP.mp3";
    if(file_exists($file)) unlink($file);
    
    $inmp3 = '';
    
    for($i=0; $i < count($fca); $i++)
        $inmp3 .= $fca[$i] . ' ';
        
    $ans = shell_exec("mp3wrap $file $inmp3"); //only on pi - oink
    
    $final = "$hutch/rss.mp3";
    if(file_exists($final)) unlink($final);
    
    $ans = shell_exec("lame -V 1 -B 16 $file $final"); //only on pi - oink
    
    if($queue == true)
        out("PLAY $final",$rabbitID,$min,$con);
    else
        echo("PLAY $final");

}    

/*****************************************************************
 * Twitter Follow
 * Note - in May 2013, Twitter moved to requiring authentication
 * on all API calls so this won't work anymore.  Someone will need
 * to fix.  See https://dev.twitter.com/docs/api/1.1/overview
 *****************************************************************/
function doFollow($follow,$rabbitID,$lang,$min,$hour,$queue,$con,$hutch) 
{
    $lang = getLanguage($lang);
    $url = "http://search.twitter.com/search.rss?q=$follow&result_type=recent&rpp=1";
    $t = getFollow($url,"title");
    $max=1;
    
    if(strlen($t[0]) < 10) return; //invalid RSS feed or no tweet
    
    $from = "Tweet " . str_replace(':',' ',$follow) . ". ";
    
    //google tts is max 100 chars, max tweet length is 140, leave 30 for introduction
    if(strlen($t[0]) > 70) 
    {
        $max=2;
        $len = strlen($t[0]);
        $t[1] = substr($t[0],70,$len-70);
        $t[0] = substr($t[0],0,70);            
    }
    
    for($i=0; $i < $max; $i++)
    {
        //send to google TTS
        if($i > 0) $from=""; //introduction on 1st msg only
        
        $request = "http://translate.google.com/translate_tts?tl=$lang&q=" . urlencode($from) . urlencode($t[$i]);
        
        //curl and save to mp3
        $response = goCurl($request);
        
        $file="$hutch/rss$i.mp3";
        
        writeToFile($file,$response);
    }
    
    //combine to one mp3
    /*
    $fca = array();
    
    for($i=0; $i < $max; $i++)
        $fca[] = "$hutch/rss$i.mp3";
 
    $file="$hutch/rss.mp3";
    $file2="$hutch/tweet.mp3";
    
    if(file_exists($file)) unlink($file);
    $fh = fopen($file, 'w') or die("can't open $file");
    
    for($i=0; $i < count($fca); $i++)
         fwrite($fh,file_get_contents($fca[$i]));
  
    fclose($fh);
    */
    
    $file="$hutch/rss.mp3";
    if(file_exists($file)) unlink($file);
    
    $inmp3 = '';
    
    for($i=0; $i < count($fca); $i++)
    {
        $inmp3 .= $fca[$i] . ' ';
    }
    
    $ans = shell_exec("mp3wrap $file $inmp3"); //only on pi - oink
    
    $final="$hutch/rss_MP3WRAP.mp3";
    
    if($queue == true)
        out("PLAY $final",$rabbitID,$min,$con);
    else
        echo("PLAY $final");


}    


/************************************************************
 * Send a restart announcement to a rabbit
 ************************************************************/
function announceRestart($sn,$con)
{
    $fca = array();
    $fca[] = "rabbit_tones.mp3";
    $fca[] = "restart.mp3";

    //check for folder in hutch
    
    $hutch = "./hutch/$sn";
    
    $file="$hutch/announcement.mp3";
    
    if(file_exists($file)) unlink($file);

    $fh = fopen($file, 'w') or die("can't open $file");
    
    for($i=0; $i < count($fca); $i++)
    {
         fwrite($fh,file_get_contents($fca[$i]));
    }

    fclose($fh);
    
    out("PLAY $file",$rabbitID,$min,$con);

}

/********************************************************
 * Evaluate function and invoke
 ********************************************************/
function invokeFunction($description,$hour,$min, $sec,$rabbitID,$sn,$broad,$lang,$con,$degrees,$weatherCode,$rss,$hutch,$follow,$wavTimeout,$clockType,$queue)
{
    switch($description)
    {
        case "Tell the time":
            tellTime($hour,$sec,$rabbitID,$sn,$broad,$lang,$min,$con,$wavTimeout,$clockType);
            break;

        case "Taichi":
            doTaichi($rabbitID,$min,$con);
            break;

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
            if(!isset($rss[0])) break;
            doRSSFeed($rss[0],$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);  
            break;
    
        case 'RSS feed 2':
            if(!isset($rss[1])) break;
            doRSSFeed($rss[1],$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);  
            break;
    
        case 'RSS feed 3':
            if(!isset($rss[2])) break;
            doRSSFeed($rss[2],$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);  
            break;
            
        case 'Twitter Follow':
            doFollow($follow,$rabbitID,$lang,$min,$hour,$queue,$con,$hutch);
            break;
    }
}

/********************************************************
 * Do Dow Jones
 ********************************************************/
function dowJones($rabbitID,$broad,$lang,$min,$sn,$queue,$con)
{
    $day = date("D",time());
    if(stristr($day,'sat') || stristr($day,'sun')) return;
    
    //$request='http://download.finance.yahoo.com/d/quotes.csv?s=indu&f=l1'; //doesn't work anymore due to contract
    //$response = file_get_contents($request);  //disabled per policy
    
    $request='http://finance.yahoo.com/q?s=indu&ql=11';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch, CURLOPT_URL, $request);
    $response = curl_exec($ch);
    
    if($response == "") $response = curl_exec($ch); //try again
    
    curl_close($ch);
 
    if(strlen($response) < 1) return;  //no response from service
     
    //<span id="yfs_l10_^dji">11,043.86</span> //this is what it looks like.  Dow 11k?
     
    $search = '<span id="yfs_l10_^dji">';
     
    $pos = stripos($response,$search);
     
    if(! $pos) return; //quote not found
     
    $dow = substr($response,$pos + strlen($search),9);
    $dow = str_replace(',','',$dow);
    $dow = round(intval($dow));
     
    $fca = array();
    $fca[] = "bell.mp3";  
        
     //get each # and announce
    for($i=0; $i < strlen($dow); $i++)
    {
        $nbr = substr($dow,$i,1);
        $fca[] = "./$broad/config/weather/$lang/temp/$nbr.mp3";
    }
    
    $hutch = "./hutch/$sn";
    
    $file="$hutch/dow_MP3WRAP.mp3";
    
    if(file_exists($file)) unlink($file);

    $inmp3 = '';
    
    for($i=0; $i < count($fca); $i++)
    {
        $inmp3 .= $fca[$i] . ' ';
    }
    
    $ans = shell_exec("mp3wrap $file $inmp3"); //only on pi - oink

    /*
    $fh = fopen($file, 'w') or die("can't open file $file");
    
    for($i=0; $i < count($fca); $i++)
    {
         fwrite($fh,file_get_contents($fca[$i]));
    }

    fclose($fh);
    */
    
    if($queue == true)
        out("PLAY $file",$rabbitID,$min,$con);
    else
        echo "PLAY $file";
}

/********************************************************
 * current temp
 ********************************************************/
function doCurrentWeather($weatherCode,$sn,$degrees,$rabbitID,$broad,$lang,$min,$hour,$queue,$con)
{
    list ($text,$high,$temp) = getForecast($weatherCode,$degrees,$hour);
    if($high == '') $high='0';
    if($temp == '') $temp='0';
        
    $fca = array();
    $fca[] = "./$broad/config/weather/$lang/signature.mp3";
    
    if($degrees=='F')
    {
        //get each # in the forecast since we do not have fharenheit
        for($i=0; $i < strlen($temp); $i++)
        {
            $nbr = substr($temp,$i,1);
            $fca[] = "./$broad/config/weather/$lang/temp/$nbr.mp3";
          
        }
    }
    else  //celsius
    {
        $fca[] = "./$broad/config/weather/$lang/temp/$temp.mp3";
        $fca[] = "./$broad/config/weather/$lang/degree.mp3";
    }
  
    //$fca[] = "./$broad/config/weather/$lang/good.mp3";
    
    //check for folder in hutch
    
    $hutch = "./hutch/$sn";
    
    $file="$hutch/weather.mp3";
    
    if(file_exists($file)) unlink($file);

    $fh = fopen($file, 'w') or die("can't open file");
    
    for($i=0; $i < count($fca); $i++)
         fwrite($fh,file_get_contents($fca[$i]));

    fclose($fh);
    
    if($queue == true)
        out("PLAY $hutch/weather.mp3",$rabbitID,$min,$con);
    else 
        echo "PLAY $hutch/weather.mp3";
  
}

/********************************************************
 * weather
 ********************************************************/
function doWeather($weatherCode,$sn,$degrees,$rabbitID,$broad,$lang,$min,$hour,$queue,$con)
{
    
  list ($text,$high,$temp) = getForecast($weatherCode,$degrees,$hour);
    if($high == '') $high='0';
    if($temp == '') $temp='0';
        
    $fca = array();
    $fca[] = "./$broad/config/weather/$lang/signature.mp3";
    
    if(intval($hour) < 18)
        $fca[] = "./$broad/config/weather/$lang/today.mp3";
    else
        $fca[] = "./$broad/config/weather/$lang/tomorrow.mp3";
    
    if(stristr($text,'t-storm'))
        $fca[] = "./$broad/config/weather/$lang/sky/5.mp3"; 
    elseif(stristr($text,'Thunderstorm'))
        $fca[] = "./$broad/config/weather/$lang/sky/5.mp3"; 
    elseif(stristr($text,'strong storm'))
        $fca[] = "./$broad/config/weather/$lang/sky/5.mp3"; 
    elseif(stristr($text,'sunny'))
        $fca[] = "./$broad/config/weather/$lang/sky/0.mp3"; 
    elseif(stristr($text,'hot'))
        $fca[] = "./$broad/config/weather/$lang/sky/0.mp3"; 
    elseif(stristr($text,'fair'))
        $fca[] = "./$broad/config/weather/$lang/sky/0.mp3"; 
    elseif(stristr($text,'clear'))
        $fca[] = "./$broad/config/weather/$lang/sky/0.mp3"; 
    elseif(stristr($text,'cold'))
        $fca[] = "./$broad/config/weather/$lang/sky/0.mp3"; 
    elseif(stristr($text,'windy'))
        $fca[] = "./$broad/config/weather/$lang/sky/0.mp3"; 
    elseif(stristr($text,'blustery'))
        $fca[] = "./$broad/config/weather/$lang/sky/0.mp3"; 
    elseif(stristr($text,'cloud'))
        $fca[] = "./$broad/config/weather/$lang/sky/1.mp3"; 
    elseif(stristr($text,'smoky'))
        $fca[] = "./$broad/config/weather/$lang/sky/1.mp3"; 
    elseif(stristr($text,'haze'))
        $fca[] = "./$broad/config/weather/$lang/sky/1.mp3"; 
    elseif(stristr($text,'dust'))
        $fca[] = "./$broad/config/weather/$lang/sky/1.mp3"; 
    elseif(stristr($text,'fog'))
        $fca[] = "./$broad/config/weather/$lang/sky/2.mp3"; 
    elseif(stristr($text,'rain'))
        $fca[] = "./$broad/config/weather/$lang/sky/3.mp3"; 
    elseif(stristr($text,'hurricane'))
        $fca[] = "./$broad/config/weather/$lang/sky/3.mp3"; 
    elseif(stristr($text,'tropical storm'))
        $fca[] = "./$broad/config/weather/$lang/sky/3.mp3"; 
    elseif(stristr($text,'tornado'))
        $fca[] = "./$broad/config/weather/$lang/sky/3.mp3"; 
    elseif(stristr($text,'drizzle'))
        $fca[] = "./$broad/config/weather/$lang/sky/3.mp3"; 
    elseif(stristr($text,'showers'))
        $fca[] = "./$broad/config/weather/$lang/sky/3.mp3"; 
    else
        $fca[] = "./$broad/config/weather/$lang/sky/4.mp3"; //snow 
    
    //////////////////////////////////////////////
    // led light code
    //////////////////////////////////////////////
    $led='';
    $ix=2;
    
    if(stristr($fca[$ix],'0.mp3')) //sun 3 yellow lights
    {
        $led = "LED1 YELLOW\nLED2 YELLOW\nLED3 YELLOW\nWAIT 16000";
    }
    elseif(stristr($fca[$ix],'1.mp3')) //2 blue lights surrounding a yellow one : cloudy
    {
        $led = "LED1 TEAL\nLED2 YELLOW\nLED3 TEAL\nWAIT 16000";
    }
     elseif(stristr($fca[$ix],'2.mp3')) //3 blue lights flickering alltogether: fog
    {
        for($i=0; $i<25;$i++)
        {
            $led = $led . "LED1 TEAL\nLED2 TEAL\nLED3 TEAL\nLEDOFF\n";
        }
    }
    elseif(stristr($fca[$ix],'3.mp3')) //natively flickering blue lights : rain
    {
        for($i=0; $i<25;$i++)
        {
            $led = $led . "LED1 TEAL\nLEDOFF\nLED2 TEAL\nLEDOFF\nLED3 TEAL\nLEDOFF\n";
        }
    }
    elseif(stristr($fca[$ix],'4.mp3')) //1 flickering blue light : snow
    {
        for($i=0; $i<25;$i++)
        {
            $led = $led . "LED1 TEAL\nLEDOFF\n";
        }
    }    
    elseif(stristr($fca[$ix],'5.mp3')) //1 yellow light playing hide & seek with a blue one : storm
    {
        for($i=0; $i<2;$i++)
        {
            $led = $led . "LED1 YELLOW\nWAIT 1000\nLEDOFF\nLED2 TEAL\nWAIT 1000\nLEDOFF\n";
            $led = $led . "LED1 YELLOW\nWAIT 1000\nLEDOFF\nLED3 TEAL\nWAIT 1000\nLEDOFF\n";
            $led = $led . "LED2 YELLOW\nWAIT 1000\nLEDOFF\nLED3 TEAL\nWAIT 1000\nLEDOFF\n";
            $led = $led . "LED3 YELLOW\nWAIT 1000\nLEDOFF\nLED3 TEAL\nWAIT 1000\nLEDOFF\n";
        }
    }    
    
    if($degrees=='C')
    {
      if(file_exists("./$broad/config/weather/$lang/temp/$high.mp3"))
      {
            $fca[] = "./$broad/config/weather/$lang/temp/$high.mp3";
            $fca[] = "./$broad/config/weather/$lang/degree.mp3";
        }
    }
    
    if($degrees=='F')
    {
        //get each # in the forecast since we do not have fharenheit
        for($i=0; $i < strlen($high); $i++)
        {
            $nbr = substr($high,$i,1);
            $fca[] = "./$broad/config/weather/$lang/temp/$nbr.mp3";
        }
    }
    
    //check for folder in hutch
    
    $hutch = "./hutch/$sn";
    
    $file="$hutch/weather.mp3";
    
    if(file_exists($file)) unlink($file);

    $fh = fopen($file, 'w') or die("can't open file");
    
    for($i=0; $i < count($fca); $i++)
    {
        fwrite($fh,file_get_contents($fca[$i]));
    }

    fclose($fh);
    
    if($queue == true)
        out("PLAY $hutch/weather.mp3",$rabbitID,$min,$con);
    else 
    {
        echo "PLAY $hutch/weather.mp3";
    }    

    //write weather led file
    
    $file = "./hutch/$sn/weather.led";
    
    if(file_exists($file)) unlink($file);
    $fh = fopen($file, 'w') or die("can't open $file");
    fwrite($fh,$led);
    fclose($fh);

}

/**********************************************
 * get forecast
 **********************************************/
function getForecast($code,$degrees,$hour)
{
    if(! is_numeric($code) ) return; //invalid code
    
    $degrees = strtolower($degrees);
    $request = "http://weather.yahooapis.com/forecastrss?w=$code&u=$degrees";
    
    //http://weather.yahooapis.com/forecastrss?w=26586223
    //$response = file_get_contents($request);  //disabled per policy
    
    //echo $request;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch, CURLOPT_URL, $request);
    $response = curl_exec($ch);
    //curl_close($ch);
 
     if(strlen($response) < 1) 
     {
         curl_close($ch);
         return;  //no response from service
     }
     
     if(stristr($response,'Maximum Transaction Time Exceeded'))
     {
         //logError("p4.php: Maximum Transaction Time Exceeded. Will retry Yahoo weather.");
         sleep(1);
     
         $response = curl_exec($ch);
         $info = curl_getinfo($ch);
     }

    curl_close($ch);
    
    if(stristr($response,'Maximum Transaction Time Exceeded'))
    {
        logError("p4.php: Retry for $request failed with Maximum Transaction Time Exceeded.");
        return;
    }

    $doc = new DOMDocument();
    $doc->preserveWhiteSpace=false;
    
    set_error_handler('HandleXmlError');
    
    try
    {
        $doc->loadXml($response);
    }
    catch(Exception $e)
    {
        restore_error_handler();
        
        logError("Offending request $request responded $response.  Curl total time in secs: " 
                . $info['total_time'] . ", connect time: " . $info['connect_time']
                );
        
        return;
    }
        
    $nl = $doc->getElementsByTagNameNS('*','forecast');
    
    if($nl->length > 0)
    {
        if(intval($hour) < 18)
            $ix = 0;
        else
            $ix = 1;
        
        $high = trim($nl->item($ix)->getAttribute('high'));
        $text = trim($nl->item($ix)->getAttribute('text'));
    
        $nl = $doc->getElementsByTagNameNS('*','condition');

        if($nl->length > 0)
        {
          $temp = trim($nl->item(0)->getAttribute('temp'));
        }

        $debug=false;
        
        if($debug)
        {
            echo "high = $high<br>";
            echo "text = $text<br>";
        }
        
        return array($text,$high,$temp);
    }
}

/************************************************************
 * Custom error handler for  Extra content at the end of the
 * document in Entity warning upon xml load.
 ************************************************************/
function HandleXmlError($errno, $errstr, $errfile, $errline)
{
    if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0))
    {
        throw new DOMException($errstr);
    }
    else 
        return false;
}

/************************************************************
 * Tell time
 ************************************************************/
function tellTime($hour,$sec,$rabbitID,$sn,$broad,$lang,$min,$con,$wavTimeout,$clockType)
{
    //speak the time. currently only 1 can be sent at a time
    
    $fca = array();
    $fca[] = "./$broad/config/clock/$lang/signature.mp3";
    $dir = "./$broad/config/clock/$lang/$hour/";   
    $time1 = $dir . "1.mp3";
        
    $hutch = "./hutch/$sn";
        
    if($clockType==1) //single clock only
    {
        $file="$hutch/time.mp3";

        if(file_exists($file)) unlink($file);
    
        if($min > 1)  //if it is not top of the hour, play the actual minute
        {
            $dir2 = "./$broad/config/weather/$lang/temp";
            
            $hour2 = intval($hour);
            $min2 = intval($min);
            
            if(file_exists("$dir2/$min2.mp3"))
            {
                if($hour2 > 12) $hour2 = $hour2 - 12;
                $fca[] = "$dir2/$hour2.mp3";
                $fca[] = "$dir2/$min2.mp3";
            }   
            else
            {
                $fca[] = $time1;
            }
        }
        else
        { //play the top of the hour original msg
            $fca[] = $time1;
        }
    
        /*
        $fh = fopen($file, 'w') or die("can't open file $file");
        
        for($i=0; $i < count($fca); $i++)
        {
            fwrite($fh,file_get_contents($fca[$i]));
        }
    
        fclose($fh);
        */
        
        $infiles = '';
        
        for($i=0; $i < count($fca); $i++)
            $infiles .= $fca[$i] . ' ';
            
        $final = "$hutch/time_MP3WRAP.mp3";
        if(file_exists($final)) unlink($final);
        
        $ans = shell_exec("mp3wrap $file $infiles");
        out("PLAY $final",$rabbitID,$min,$con);
        return;
    }
    
    $fca[] = $time1;
    $fca[] = "./$broad/config/clock/$lang/signature.mp3";
    
    //pick a random file to play for #2
    if (glob($dir . "*.mp3") != false)
    {
        $filecount = count(glob($dir . "*.mp3"));
        
        if($filecount > 0)
        {
            $nbr = rand(2,$filecount);
            $file = (string)$nbr . ".mp3";
            $rand = $dir . $file;
        }
                
        if(!file_exists($rand))
        {
            logError("$rand not found");
            return;
        }
        
        $fca[] = $rand;
        
        $file="$hutch/time.mp3";

        if(file_exists($file)) unlink($file);
    
        $fh = fopen($file, 'w') or die("can't open file $file");
        
        for($i=0; $i < count($fca); $i++)
        {
            fwrite($fh,file_get_contents($fca[$i]));
        }
    
        fclose($fh);

        out("PLAY $file",$rabbitID,$min,$con);
        //out("PLAY $file\nNETTIME $wavTimeout",$rabbitID,$min,$con);

    }
    else
        logError("Glob to $dir for $time1 failed.");

        
}

/********************************************************
 * do taichi
 ********************************************************/
function doTaichi($rabbitID,$min,$con)
{
    out("DOTAICHI",$rabbitID,$min,$con);
    return;
}

/********************************************************
 * do random message
 ********************************************************/
function randomMsg($rabbitID,$broad,$lang,$min,$queue,$con)
{
    $dir = "./$broad/config/surprise/$lang/";

    $filecount = count(glob($dir . "*.mp3"));

    if($filecount < 1)
    {
        logError("No files found in $dir.");
        return;
    }
            
    $nbr = rand(1,$filecount);
    $file = (string)$nbr . ".mp3";
    $rand = $dir . $file;
  
    if(file_exists($rand))
    {
        if($queue == true)
        {
            out("PLAY $rand",$rabbitID,$min,$con);
        }
        else
        {
            echo("PLAY $rand");
        }
    }
    else
    {
        logError("$rand not found");
    }
    
    return;
}


/*********************************************
 * Random star trek sound
 *********************************************/
function randomTrek($rabbitID,$sn,$min,$queue,$con)
{
    $folder = "./star_trek/";
    $count = count(glob($folder . "*.mp3"));
    $nbr = rand(1,$count);
    
    $fca = array();
    $fca[] = "$folder$nbr.mp3";
    $hutch = "./hutch/$sn";
    
    $file="$hutch/star_trek.mp3";
    
    if(file_exists($file)) unlink($file);

    $fh = fopen($file, 'w') or logError("star_trek can't open file $file");

    //combine to 1 mp3
    for($i=0; $i < count($fca); $i++)
    {
        fwrite($fh,file_get_contents($fca[$i]));
    }

    fclose($fh);
  
    if(file_exists($file))
    {
      if($queue)
            out("PLAY $file",$rabbitID,$min,$con);
        else
          echo "PLAY $file";
    }
    else
    {
        logError("$file not found");
    }
    
    return;
}


/*********************************************
 * Random arcade sound
 *********************************************/
function randomArcade($rabbitID,$min,$con,$queue)
{
    $folder = "./arcade/";
    $count = count(glob($folder . "*.mp3"));
    $nbr = rand(1,$count);
    
    $file = (string)$nbr . ".mp3";
    $rand = "$folder$file";
  
    if(file_exists($rand))
    {
        if($queue)
            out("PLAY $rand",$rabbitID,$min,$con);
        else
            echo "PLAY $rand";
    }
    else
    {
        logError("$rand not found in arcade");
    }
    
    return;
}


/******************************************************
 * Twitch the ears
 ******************************************************/
function twitchEars($min)
{
    //if($min == '00' || $min == '15' || $min == '30' || $min == '45' || 
      // $min == '10' || $min == '20' || $min == '40' || $min == '50') return;
    
    $seqCnt = rand(1,2);
    $seqRight = rand(0,1);
    $seqLeft  = rand(0,1);
    
    //1 = fwd, 0 = reverse
    echo("LEFTTWITCH $seqLeft\n");
    
    if($seqCnt == 2) echo("RIGHTTWITCH $seqRight\n");
    return;
}

/***************************************************************************************
 * Cheerlights on idle.  
 ***************************************************************************************/
function cheerLights($rabbitID,$sn)
{
    $request='http://api.thingspeak.com/channels/1417/field/1/last.txt';
    $color = goCurl($request);
    $color = strtoupper($color);
    if(strlen($color) < 1) return;
    
    //if($color == 'PURPLE') $color = 'VIOLET';  //note check bc.jsp to see what happened to PURPLE and make sure all colors are there 
    
    echo "LED1 $color\nLED2 $color\nLED3 $color\nLED4 $color\nWAIT 16000\n";
}

/***************************************************************************************
 * Weather lights on idle.  The file must have already been generated by a weather check
 ***************************************************************************************/
function weatherLights($rabbitID,$sn)
{
    $file = "./hutch/$sn/weather.led";
    
    if(! file_exists($file)) return;

    if(filesize($file) < 1) return;
    
    $fh = fopen($file, 'r') or die("can't open $file");
    $data = fread($fh,filesize($file));
    fclose($fh);

    if(strlen($data) > 0)
    {
        echo($data);  //do not log
    }
        
}

/******************************************************
 * PacMan lights when idle
 ******************************************************/
function pacMan($min)
{
    
    //if($min == '00' || $min == '15' || $min == '30' || $min == '45' || 
    //   $min == '10' || $min == '20' || $min == '40' || $min == '50') return;
    
    $colors = array(1 => 'AMBER', 'GREEN', 'RED', 'TEAL','VIOLET', 'YELLOW');
    $sequence = array(1 =>'1','3');
        
    $seq = rand(1,2);
    $l1 = $sequence[$seq];
    
    if($seq == 1) 
        $l3 = '3';
    else
        $l3 = '1';
    
    $nbr = rand(1,6);  //6 max
    $c1 = $colors[$nbr];
    $c2 = $c1; //$colors[$nbr];
    $c3 = $c1; //$colors[$nbr];
        
    $wait=1000;
        
    echo "LED$l1 $c1\nWAIT $wait\nLED2 $c2\nWAIT $wait\nLED$l3 $c3\nWAIT $wait\nLEDOFF\nWAIT $wait\n";
    $wait -= 500;
    echo "LED$l1 $c1\nWAIT $wait\nLED2 $c2\nWAIT $wait\nLED$l3 $c3\nWAIT $wait\nLEDOFF\nWAIT $wait\n";
    $wait -= 250;
    echo "LED$l1 $c1\nWAIT $wait\nLED2 $c2\nWAIT $wait\nLED$l3 $c3\nWAIT $wait\nLEDOFF\nWAIT $wait\n";
    $wait -= 150;
    echo "LED$l1 $c1\nWAIT $wait\nLED2 $c2\nWAIT $wait\nLED$l3 $c3\nWAIT $wait\nLEDOFF\nWAIT $wait\n";
    $wait -= 50;
    echo "LED$l1 $c1\nWAIT $wait\nLED2 $c2\nWAIT $wait\nLED$l3 $c3\nWAIT $wait\nLEDOFF\nWAIT $wait\n";
    $wait = 0;
    echo "LED$l1 $c1\nLED2 $c2\nLED$l3 $c3\nLEDOFF\n";
    
    return;
}

/***********************************************************
 * write output to bunny and update DB with last action
 ***********************************************************/
function out($out,$rabbitID,$min,$con)
{
    queue($out,$rabbitID,$min,$con); //add item to queue
     
    $clean_msg = mysqli_real_escape_string($con, $out);

    $cmd = "call sp_SetLastCommand($rabbitID,'" . $clean_msg . "',@msg)";
    
    $result = mysqli_query($con,$cmd);

    if (!$result) 
    {
        logError('Out function invalid update last Command query: ' . mysqli_error($con));
        return;
    }
    
    $result = mysqli_query($con,"select @msg");

    while($row = mysqli_fetch_row($result))
        $msg = $row[0];
    
    if($msg != 'OK') logError($msg);
}

/****************************************************************
 * write output to bunny and update DB with last action. no queue
 ****************************************************************/
function out2($out,$rabbitID,$con)
{
    echo $out . "\n"; //$string;  //simply echoing will lose linefeed
  
    $clean_msg = mysqli_real_escape_string($con, $out);

    $cmd = "call sp_SetLastCommand($rabbitID,'" . $clean_msg . "',@msg)";

    $result = mysqli_query($con,$cmd);

    if (!$result) 
    {
        logError('P4.php: Out function invalid update last Command query: ' . mysqli_error($con));
        return;
    }
  
    $result = mysqli_query($con,"select @msg");

    while($row = mysqli_fetch_row($result))
        $msg = $row[0];
    
    if($msg != 'OK') logError($msg);
}
    
/***********************************************************
 * log error
 ***********************************************************/
function logError($msg)
{
    $date = date("Y-m-d H:i:s");
    $msg = "[$date] $msg \n";
    $file='../../etc/nabaztag_error.log';
    error_log($msg,3,$file);
}


    
?>

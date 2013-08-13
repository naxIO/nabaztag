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
$serialNbr = $_SESSION['serialNbr'];
?>

<html>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<?php

//error_reporting(E_ALL);  //don't use this in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log','../etc/nabaztag_error.log');
//error_reporting(0);  //don't use this, use ini_set or nothing goes to the log

date_default_timezone_set('America/Chicago'); //for warning

$debug=false;

include './subroutines/clean.php';
include './subroutines/getLanguage.php';
include './subroutines/goCurl.php';
include './subroutines/writeToFile.php';
include './subroutines/logError.php';
include './subroutines/doTTS.php';
include './subroutines/getRSSFeed.php';
include './subroutines/queryWithRetry.php';

//validate the new rabbit parms

$serialNbr   = $_REQUEST['txtSerial'];
$name        = $_REQUEST['txtName'];
$timeZone    = $_REQUEST['ddlTimeZone'];
$weatherCode = $_REQUEST['txtWeatherCode'];
$wakeHour    = $_REQUEST['ddlWake'];
$sleepHour   = $_REQUEST['ddlBedTime'];
$language    = $_REQUEST['ddlLanguage'];
$oldSerNbr   = $_REQUEST['hidRabbitSerNbr'];
$temp        = $_REQUEST['ddlTemp'];
$bottomColor = $_REQUEST['ddlBottomColor'];
$idle        = $_REQUEST['ddlIdle'];
$weekendWakeHour  = $_REQUEST['ddlWeekendWake'];
$buttonAction = $_REQUEST['ddlButton'];
$rss1          = $_REQUEST['txtRSS1'];
$rss2          = $_REQUEST['txtRSS2'];
$rss3          = $_REQUEST['txtRSS3'];
$tts           = $_REQUEST['txtTTS'];
$follow        = $_REQUEST['txtFollow'];

if(isset($_REQUEST['chkClock']))
    $clockType     = $_REQUEST['chkClock'];
else
    $clockType = 'On';
    
if(isset($_REQUEST['email']))
    $email = $_REQUEST['email'];
else
    $email = '';

if(isset($_REQUEST['chkReboot']))
    $reboot = $_REQUEST['chkReboot'];
else
    $reboot = '';
    
$wavTimeout = 10;  //not in use anymore due to mp3 encoding change.  should be removed from proc.

if($debug == true)
{
    echo "ser #    = $serialNbr<br>";
    echo "old ser #= $oldSerNbr<br>";
    echo "name     = $name<br>";
    echo "timeZone = $timeZone<br>";
    echo "weather  = $weatherCode<br>";
    echo "wake     = $wakeHour<br>";
    echo "sleep    = $sleepHour<br>";
    echo "temp     = $temp<br>";
    echo "idle     = $idle<br>";
}

$back=" Click your browser's back button to go back and correct this.";

//validation

if(substr_count($serialNbr, ':') < 5)
{
    echo "'$serialNbr' is not a valid serial number.  Remember to include the colons.  $back";
    return;
};

if(strlen($name) < 3)
{
    echo "'$name' is not a valid name.  It needs to be at least three characters. $back";
    return;
};

$_SESSION['rawSerialNbr'] = $serialNbr;

include '../etc/nabaztag_db.php';
$con = mysqli_connect($host,$user,$pass,$db);
if (!$con) die('Could not connect: ' . mysqli_error($con));

$msg='';
$serialNbr = clean($serialNbr);
$name = clean($name);
$timeZone = clean($timeZone);
$weatherCode = clean($weatherCode);
$email = clean($email);
$language = clean($language);
$oldSerNbr = clean($oldSerNbr);
$temp = clean($temp);
$follow = clean2($follow);
$wavTimeout = clean($wavTimeout);
$clockType = clean($clockType);

if($clockType == 'checked' || $clockType == 'on')
    $clockType=1;
else
    $clockType=0;

if($reboot == 'checked' || $reboot == 'on')
  $reboot=1;
else
    $reboot=0;

$bRSS = false;

if (isset($_POST['btnValidate1']))
{
    $bRSS = true;
    $rss = $rss1;
}

if (isset($_POST['btnValidate2']))
{
    $bRSS = true;
    $rss = $rss2;
}

if (isset($_POST['btnValidate3']))
{
    $bRSS = true;
    $rss = $rss3;
}

if($bRSS == true)
{
    $protocol = strtolower(substr($rss,0,4));

    if($protocol == 'feed')
    {
        $protocol = 'http';
        $rss = str_replace('feed://','http://',$rss);
    }
    
    if($protocol != 'http')
    {
        echo 'The RSS URL must start with HTTP.' . $back;
        return;
    }

  doRSSFeed($rss,$serialNbr,$language);
  
  echo "<P>Click your browser's back button to make changes and save.";
  
  /*
  $hutch = "./vl/hutch/$serialNbr";
  $file="$hutch/rss.mp3";
  
  
    <P>Below you can listen to the RSS feed you selected.  RSS feeds need to have &lt;title&gt; tags within or 
       they will not work.  Click your browser's back button to make changes and save.
    <P>

  <object type="audio/mp3" data="<? echo $file; ?>" width="200" height="50">
        <param name="loop" value="0" />
        <param name="showcontrols" value="true" />
        <param name="showdisplay" value="true" />
        <a href="<? echo $file; ?>">background music</a>
    </object>

  <audio controls="controls" height="50px width="100px">
        <source src="<? echo $file; ?>" type="audio/mpeg" />
        <embed height="50px width="100px" src="<? echo $file;?>" />
    </audio>
  */
  
    mysqli_close($con);
    return;
}

if (isset($_POST['btnRestart']))
{
    $min  = date("i"); //minute 00-59
    $sec  = date("s"); //secs 00-59
    
    if(intval($sec) > 5)
        $min = $min +1;  
    
    if(intval($min) > 59)
        $min = 0;
    
  $cmd = "call sp_PurgeQueue('$serialNbr',@msg)";
  
  $result = queryWithRetry($con,$cmd,$name,"SaveUpdateRabbit queue purge.");
        
    if (!$result) 
    {
        mysqli_close($con);
        return;
    }

    $result = mysqli_query($con,"select @msg");

    while($row = mysqli_fetch_row($result))
        $msg = $row[0];

    mysqli_next_result($con);  //required to avoid sync error

    if($msg != 'OK')
        logError("saveUpdateRabbit purge queue failed with $msg");
    
    //for V2
    $file='restart.msg'; 
  $msg = file_get_contents($file);
  queue($serialNbr,$min,$msg,$con,$language,false);
    
    mysqli_close($con);
    return;
}

if (isset($_POST['btnSpeak']))
{
    $min  = date("i"); //minute 00-59
    $sec  = date("s"); //secs 00-59
    
    if(intval($sec) > 5)
        $min = $min +1;  
    
    if(intval($min) > 59)
        $min = 0;
    
    queue($serialNbr,$min,$tts,$con,$language,true);
    mysqli_close($con);
    return;    
}

if (isset($_POST['btnTestWav']))
{
    $min  = date("i"); //minute 00-59
    $sec  = date("s"); //secs 00-59
    
    if(intval($sec) > 5) $min = $min +1;  //already has something scheduled?  
    if(intval($min) > 59) $min = 0;
    
    queue($serialNbr,$min,"NETTIME $wavTimeout\nPLAY bbc.mp3",$con,$language,false);
    mysqli_close($con);
    return;    
}

if($debug == true)
{
    echo "cleaned values:<P>";
    echo "serialNbr = $serialNbr<br>";
    echo "name = $name<br>";
    echo "timeZone = $timeZone<br>";
    echo "weatherCode = $weatherCode<br>";
    echo "email = $email<br>";
    echo "language = $language<br>";
    echo "idle = $idle<br>";
}

$cmd = "call sp_UpdateRabbit('$oldSerNbr'
                                                         ,'$serialNbr'
                                                         ,'$name'
                                                         ,'$timeZone'
                                                         ,'$weatherCode'
                                                         ,'$email'
                                                         ,'$wakeHour'
                                                         ,'$sleepHour'
                                                         ,'$language'
                                                         ,'$temp'
                                                         ,'$bottomColor'
                                                         ,'$idle'
                                                         ,'$weekendWakeHour'
                                                         ,'$buttonAction'
                                                         ,$reboot
                                                         ,'$follow'
                                                         ,$wavTimeout
                                                         ,$clockType
                                                         ,@msg
                                                         );";
     
if($debug == true) echo "<P>$cmd<P>";

$result = mysqli_query($con,$cmd);
    
//retry transaction
if(!$result)
{
    //usleep(1000000); //1 sec
    usleep(100000);  //100 ms

    $result = mysqli_query($con,$cmd);
}

if (!$result) 
{
    logError('Update proc failed after retry: ' . mysqli_error($con));
    mysqli_close($con);
    return;
}

mysqli_next_result($con);  //required to avoid sync error

if($debug == true)
{
    echo "effected rows = " . mysqli_affected_rows($con) . "<P>";
    echo "info = " . mysqli_info($con) . "<P>";
    echo "status = " . mysqli_stat($con) . "<P>";
}

$result = mysqli_query($con,"select @msg");

while($row = mysqli_fetch_row($result))
{
    $msg = $row[0];
}

if($debug == true)
{
    echo "Update rabbit proc returned $msg for msg";
}

if($msg != "OK") 
{
    echo $msg . $back;
    return;
}

include 'updateSchedule.php';
include 'updateRSS.php';
include 'updateRFID.php';

//mysqli_close($con);

echo "Your rabbit has been updated.<P> <a href=index.php>Home</a>";

exit(0);

/*****************************************************************
 * queue message to rabbit
 *****************************************************************/
function queue($serNbr,$min,$msg,$con,$language,$tts)
{
    if(strlen($msg) < 1) 
    {
        echo "You need to enter some text to send to the rabbit. $back";
        return;
    }
    
    $lang = getLanguage($language);
        
    $msg = mysqli_real_escape_string($con, $msg);

    $hutch = "./hutch/$serNbr";
  
  if($tts == true)
  {
        doTTS($msg,$lang,$serNbr);
        $msg="PLAY $hutch/rss.mp3";
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
      //usleep(1000000); //1 sec
      usleep(100000);  //100 ms
  
      $result = mysqli_query($con,$cmd);
  }
  
    if (!$result) 
    {
        logError('Queue function invalid insert after retry: ' . mysqli_error($con));
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

    echo "The message has been sent.  It may take a minute to reach your rabbit. Click your browser's back button to go back.";
}

/*****************************************************************
 * RSS feed thru google
 *****************************************************************/
function doRSSFeed($url,$sn,$lang) 
{
    $hutch = "./vl/hutch/$sn";
    
    if(! is_dir($hutch))
        mkdir($hutch);
    
    $t = getRSSFeed($url,'title');
    $max=4;
    
    echo "<fieldset><legend><b>RSS results</b></legend>";
    for($i=0; $i < $max; $i++)
    {
        //send to google TTS
        $request = 'http://translate.google.com/translate_tts?tl=en&q=' . urlencode($t[$i]);
        
        echo $t[$i] . "<P>";
        
        //curl and save to mp3
        $response = goCurl($request);
        //if(strlen($response) < 1) return;  //no response from service
        
        $file="$hutch/rss$i.mp3";
        
        writeToFile($file,$response);
    }
    echo "</fieldset>";
    
    //combine to one mp3
    $fca = array();
    for($i=0; $i < $max; $i++)
        $fca[] = "$hutch/rss$i.mp3";
    
    $file="$hutch/rss.mp3";
    if(file_exists($file)) unlink($file);
    $fh = fopen($file, 'w') or die("can't open $file");
    
    for($i=0; $i < count($fca); $i++)
         fwrite($fh,file_get_contents($fca[$i]));
  
    fclose($fh);
    
}    


?>
</html>
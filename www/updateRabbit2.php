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
$serialNbr = $_SESSION['rawSerialNbr'];
?>

<html>
<head>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
</head>
<body background="">
<form Name ="frmUpdateRabbit" Method ="POST" ACTION = "saveUpdateRabbit.php">
<center><h2>Update a Rabbit</h2></center>
<img src="ping_rabbit.jpg" height="150px" style="float:right;"></img>
<P>
<?php
//error_reporting(E_ALL);  //don't use this in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log','../etc/nabaztag_error.log');
//error_reporting(0);  //don't use this, use ini_set or nothing goes to the log

include './subroutines/logError.php';
include '../etc/nabaztag_db.php';

$debug=false;

$con = mysqli_connect($host,$user,$pass,$db);
if (!$con) die('Could not connect: ' . mysqli_error());

$back=" Click your browser's back button to go back and correct this.";

if(! isset($serialNbr))
{
    if(! isset($_REQUEST['txtSerNbr']))
    {
        header("Location: updateRabbit.php");  //go to login
        die();
    }
}

if(strlen($serialNbr) < 12)
{
    $serialNbr = $_REQUEST['txtSerNbr'];
    
    if(substr_count($serialNbr, ':') < 5)
    {
        echo "'$serNbr' is not a valid serial number.  Remember to include the colons.  $back";
        sleep(2);
        return;
    };
    
    if(strlen($serialNbr) < 17)
    {
        echo "'$serialNbr' is not a valid serial number. $back";
        sleep(2);
        return;
    };
}

include './subroutines/clean.php';
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
    $button = $row[13];
    $reboot = $row[15];
    $follow = $row[16];
    $version = $row[17];
    $wavTimeout = $row[18];
    $chkClock = $row[19];  //1 if single clock only, 0 for double clock
}

if(! isset($rabbitID))
{
    echo "Whoops!  We can't find your rabbit.  Did you enter the correct serial number? $back\n";
    $_SESSION['rawSerialNbr'] = '';
    sleep(2);
    return;
}

if(intval($rabbitID) < 1)
{
    echo "Whoops!  We can't find your rabbit.  Did you enter the correct serial number? $back\n";
    $_SESSION['rawSerialNbr'] = '';
    sleep(2);
    return;
}


if(is_null($weekendWakeHour)) $weekendWakeHour = $wakeHour;

mysqli_next_result($con);

$cmd = 'call sp_GetTimeZone();';

$result = mysqli_query($con, $cmd); 
if (!$result) 
{
    logError('updateRabbit2.php: Invalid timezone query: ' . mysqli_error($con));
}

if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.

$cmd = 'call sp_GetLanguage();';
$resultLang = mysqli_query($con, $cmd);

if (!$resultLang) 
{
    logError('updateRabbit2.php: Invalid query: ' . mysqli_error($con));
}

if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.

if($reboot == 1)
    $reboot='checked';

?>

<table cellpading=0 cellspacing=4>
<tr>
    <td>Serial #</td>
  <td><input id=txtSerial name=txtSerial type=text size=30 maxlength=17 value=<?php echo $serialNbr; ?>> <a href="serial.htm">Where do I find this?</a>
</tr>
<tr>
    <td>Rabbit's Name </td>
    <td><input id=txtName name=txtName type=text size=30 maxlength=20 value=<?php echo $name; ?>> (Used for messages and forum) </td> 
</tr>
<tr>
    <td>Time Zone </td>
    <td>
    <select name="ddlTimeZone">
    <?php
        while($row = mysqli_fetch_row($result))
        { ?>
            <option <?php if($time == $row[0]) echo 'selected'; ?> value="<?php echo $row[0] ?>"><?php echo $row[0];?></option>
        <?php }
    ?>
  </select>
    </td>
</tr>
<tr>
    <td>Language</td>
    <td>
    <select id="ddlLanguage" name="ddlLanguage">
    <?php
        while($row = mysqli_fetch_row($resultLang))
         { ?>
        <option <?php if($language == $row[1]) echo 'selected'; ?> value="<?php echo $row[0] ?>"><?php echo $row[1];?></option>
        <?php }
        ?>
  </select>
    </td>
</tr>
<tr>
    <td>Weather Code</td>
    <td><input name=txtWeatherCode type=text size=30 maxlength=20 value=<?php echo $weather; ?>> <a href="weather_setup.htm">Where do I find this?</a></td>
</tr>
<tr>
    <td>Weekday Wake Up Time </td>
    <td><select name="ddlWake">
        <? $hour=$wakeHour; include("./subroutines/time_picker.php"); ?>
        </select>
    </td>
</tr>
<tr>
    <td>Weekend Wake Up Time </td>
    <td><select name="ddlWeekendWake">
        <? $hour=$weekendWakeHour; include("./subroutines/time_picker.php"); ?>
        </select>
    </td>
</tr>
<tr>
    <td>Bed Time </td>
    <td><select name="ddlBedTime">
        <? $hour=$sleepHour; include("./subroutines/time_picker.php"); ?>
        </select>
    </td>
</tr>
<tr>
    <td>Temperature </td>
    <td><select name="ddlTemp">
        <? include("./subroutines/temp_picker.php"); ?>
        </select>
    </td>
</tr>
<tr>
    <td>Bottom LED Color</td>
    <td><select name="ddlBottomColor"> <!--<? if($version == 1) echo 'disabled' ?> > breaks due to inner join -->
                 <? $s_color = $bottomColor; include("./subroutines/color_picker.php");  ?>
        </select>
        
        <? if($version == 1) echo 'V2 only'; ?>
    </td>
</tr>


<tr>
    <td>Idle Behavior</td>
    <td><select name="ddlIdle">
        <? $s_idle = $idle; include("./subroutines/idle_picker.php"); ?>
        </select> 
        <? if($version==1) echo 'Cheerlights for V1; otherwise V2 only'; ?>
    </td>
</tr>

<tr>
    <td>Button Behavior</td>
    <td><select name="ddlButton"> 
        <? $s_button = $button; include("./subroutines/button_picker.php"); ?> 
        </select> 
        <? if($version==1) echo 'V2 only'; ?>
    </td>
</tr>

<tr>
    <td>Single Clock Behavior</td>
    
    <td><input type=checkbox <?php if($chkClock == 1) echo 'checked'; ?> id=chkClock name=chkClock>
    <? if($version==1) echo 'V2 only'; ?>
    </td>
</tr>


</table>

<?php 

mysqli_free_result($resultLang);
mysqli_free_result($result);


/////////////////////////////////////
//get schedules
/////////////////////////////////////
?>

<P>
<b>Choose the following features for your rabbit:</b>
<p>
<table cellpading=0 cellspacing=4>
<tr bgcolor="#DCDCDC")>
  <td>Enabled</td>
    <td>From </td>
    <td>To</td>
    <td>Minute</td>
    <td>Action</td>
</tr>
<tr>
<?php

if($version == 1)
  echo "<h4 style='position:absolute;left:700px;'>Your V1 rabbit is limited<h4>";

$schedCount = 11;

$result = mysqli_query($con,"call sp_GetSchedule($rabbitID)");

if (!$result) 
{
    logError('updateRabbit2.php: Call to schedule proc failed. ' . mysqli_error($con));
    return;
}

mysqli_use_result($con);
    
$i=0;

while($row = mysqli_fetch_row($result))
{
    $i=$i+1;
    
    $s_enabled = $row[0];
    $s_from    = $row[1];
    $s_to      = $row[2];
    $s_min     = $row[3];
    $s_desc    = $row[4];
    $seq       = $row[5];
    $msg       = $row[6];
  
    if($msg != "OK") 
    {
        echo $msg . $back;
        return;
    }
    
    echo "<tr>";
    
    if($s_enabled == 1)
        echo "<td><input type=checkbox checked id=enabled$i name=enabled$i></td>";
    else
        echo "<td><input type=checkbox id=enabled$i name=enabled$i></td>";
    
    $hour=$s_from;
    echo "<td><select id=from$i name=from$i>"; include("./subroutines/time_picker.php"); echo "</select></td>";
    $hour=$s_to;
    echo "<td><select id=to$i name=to$i>"; include("./subroutines/time_picker.php"); echo "</select></td>";
    echo "<td><select id=min$i name=min$i>"; include("./subroutines/minute_picker.php"); echo "</select>    </td>";
    echo "<td><select id=action$i name=action$i>"; include("./subroutines/action_picker.php"); echo "</select></td>";
    echo "</tr>";
    
}

mysqli_free_result($result);

//if($i == 0)
if($i < $schedCount)
{
    for($i=$i+1; $i < $schedCount; $i++)
    {
        echo "<tr>";
        echo "<td><input type=checkbox id=enabled$i name=enabled$i></td>";
        echo "<td><select id=from$i name=from$i>"; include("./subroutines/time_picker.php"); echo "</select></td>";
        echo "<td><select id=to$i name=to$i>"; include("./subroutines/time_picker.php"); echo "</select></td>";
        echo "<td><select id=min$i name=min$i>"; include("./subroutines/minute_picker.php"); echo "</select>    </td>";
        echo "<td><select id=action$i name=action$i>"; include("./subroutines/action_picker.php"); echo "</select></td>";
        echo "</tr>";
    }
}

?>
</table>
<P>
Note you should not have two events at the same minute at the same hour or the rabbit may hyperventilate.  If you want to always tell the time at the top of the hour then you would check enable, select midnight, midnight, Tell the Time, and 00.  The sleep and wake times override this schedule. 
<P>

Still have a question? <a href="sample.jpg">See a sample schedule</a>
<P>

<fieldset><legend>Legend</legend>
<table cellpading=0 cellspacing=4>
<tr>
  <td>Enabled</td>
  <td>-</td>
  <td>Check to enable the feature</td>
</tr>
<tr>
  <td>From</td>
  <td>-</td>
  <td>The hour the rabbit begins to use the feature</td>
</tr>
<tr>
  <td>To</td>
  <td>-</td>
  <td>The hour the rabbit stops using the feature</td>
</tr>
<tr>
  <td>Minute</td>
  <td>-</td>
  <td>The minute the rabbit will start the feature</td>
</tr>
<tr>
  <td>Action</td>
  <td>-</td>
  <td>The feature the rabbit will perform</td>
</tr>
</table>
</fieldset>
<P>

<?
/******************************************
 * Get RSS
 ******************************************/
mysqli_next_result($con);

$result = mysqli_query($con,"call sp_GetRSS($rabbitID)");
if (!$result) die('Call to RSS proc failed. ' . mysqli_error($con));
mysqli_use_result($con);
    
$i=0;

$rss_enabled = array();
$rss_url = array();

while($row = mysqli_fetch_row($result))
{
    $rss_enabled[] = $row[0];
  $rss_url[]     = $row[1];
}

mysqli_next_result($con);

?>
<fieldset><legend><b>RSS Feed (V2 only)</b></legend>
Here you can setup your own RSS feed.  Enter the URL to the feed, click validate to test.

<table cellpading=0 cellspacing=4>
    <tr>
        <td>RSS URL 1</td>
        <td><input type=text id=txtRSS1 name=txtRSS1 maxlength=255 size=100 value=<? if(isset($rss_url[0])) echo $rss_url[0]; ?>></td>
        <td><input type=submit name=btnValidate1 value=Validate></td>
    </tr>
    <tr>
        <td>RSS URL 2</td>
        <td><input type=text id=txtRSS2 name=txtRSS2 maxlength=255 size=100 value=<? if(isset($rss_url[1])) echo $rss_url[1]; ?>></td>
        <td><input type=submit name=btnValidate2 value=Validate></td>
    </tr>
    <tr>
        <td>RSS URL 3</td>
        <td><input type=text id=txtRSS3 name=txtRSS3 maxlength=255 size=100 value=<? if(isset($rss_url[2])) echo $rss_url[2]; ?>></td>
        <td><input type=submit name=btnValidate3 value=Validate></td>
    </tr>

</table>
</fieldset>

<P>
<fieldset><legend><b>Speech (V2 only)</b></legend>
Here you can send a message for your rabbit to speak. Enter the message and click speak to send.  
This message will not be saved.

<table cellpading=0 cellspacing=4>
    <tr>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;Message</td>
        <td><input type=text id=txtTTS name=txtTTS maxlength=100 size=100></td>
        <td><input type=submit name=btnSpeak value=Speak></td>
    </tr>
</table>
</fieldset>

<P>
<fieldset><legend><b>RFID (V2 only)</b></legend>
If your rabbit has sniffed tags, they will appear below.

<table cellpading=0 cellspacing=4>
    <tr bgcolor="#DCDCDC")>
        <td>Tag Number</td>
        <td>Description</td>
        <td>Action</td>
    </tr>

<?php
$schedCount = 11;
$result = mysqli_query($con,"call sp_GetRFID($rabbitID)");

if (!$result) 
{
    logError('Call to RFID proc failed. ' . mysqli_error($con));
    return;
}

mysqli_use_result($con);
    
$i=0;

while($row = mysqli_fetch_row($result))
{
    $i=$i+1;
    
    $tag = $row[0];
    $s_desc = $row[1];
    $tdesc = $row[2];

    echo "<tr>";
    echo "<td><input type=text value=$tag readonly size=30 name=tag$i id=tag$i></td>";
    echo "<td><input type=text name=tdesc$i id=tdesc$i maxlength=30 size=40 value='$tdesc'></td>";
    echo "<td><select id=rfid$i name=rfid$i>"; include("./subroutines/action_picker.php"); echo "</select></td>";
    echo "</tr>";
    
}

mysqli_next_result($con);

?>
</table>
</fieldset>  
<P>

<fieldset><legend><b>Twitter (V2 only)</b></legend>
Have someone or something you'd like to follow?  To follow a person, 
enter "from:user" without the quotes where the user is a person.  You can also enter a subject.  
To hear the tweet, you must schedule a Twitter Follow event. 
Only the most recent tweet is sent to the
rabbit based on the schedule you choose.   


<table cellpading=0 cellspacing=4>
    <tr>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;Follow</td>
        <td><input type=text id=txtFollow name=txtFollow maxlength=100 size=100 value=<?php echo $follow;?>></td>
    </tr>
</table>
</fieldset>
<P>


<fieldset><legend><b>Rabbit restart (V2 only)</b></legend>
If you need to restart your rabbit, click the restart rabbit button and your rabbit will be sent the restart command.
For this to work, your rabbit must be <a href="check_rabbit_entry.php">reaching the server</a>.  
<P>
<input type=submit name=btnRestart value="Restart Rabbit">
&nbsp;&nbsp;&nbsp;Restart my rabbit every night at midnight<input type=checkbox name=chkReboot <?php echo $reboot;?>>

</fieldset>



<? 

mysqli_close($con); 

?>
<P>

<fieldset><legend><b>Save changes</b></legend>
Click the Save to Rabbit Hutch button to save changes to the rabbit.
<P>
<input type=hidden name=hidRabbitSerNbr id=hidRabbitSerNbr value=<?php echo $serNbr; ?>>
<input type=submit name=btnSave value="Save to Rabbit Hutch">
</fieldset>
<P>
<a href="index.php">Home</a>

</form>
</body>
</html>

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
<meta http-equiv="refresh" content="60">
<link rel="stylesheet" type="text/css" href="main.css" />
<?php

$count = $_SESSION['rabbitCount'];
$ip = $_SERVER['REMOTE_ADDR'];
$name = $_GET['name'];

date_default_timezone_set('America/Chicago'); //for warning

if(strlen($name) < 1)
{
	echo 'No name';
	return;
}

include '../etc/nabaztag_db.php';
include './subroutines/queryWithRetry.php';
include './subroutines/logError.php';

$con = mysqli_connect($host,$user,$pass,$db);

if (!$con) 
{
    logError('Peek.php: Could not connect: ' . mysqli_error());
    return;
}

$cmd = "select serialNbr from rabbit where name='$name';";

$result = queryWithRetry($con,$cmd,$name,"Peek name select.");
		
if (!$result) die('Invalid name select query: ' . mysqli_error($con));

while($row = mysqli_fetch_row($result))
{
	$serNbr = $row[0];
}

mysqli_next_result($con);

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
}

if(intval($rabbitID) < 1)
{
	echo "Whoops!  We can't find your rabbit.  Did you enter the correct serial number? $back\n";
	$_SESSION['rawSerialNbr'] = '';
	return;
}

if(is_null($weekendWakeHour)) $weekendWakeHour = $wakeHour;

if($debug==true)
{
	echo "language = $language<br>";
	echo "timezone = $time<br>";
}
mysqli_next_result($con);

$result = mysqli_query($con,'SELECT name from timeZone order by name');
if (!$result) die('Invalid timezone query: ' . mysqli_error($con));

$resultLang = mysqli_query($con,'SELECT name,description from language order by 1');
if (!$result) die('Invalid query: ' . mysqli_error($con));


$serialNbr = '';
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
		<option <?php if($time == $row[0]) echo selected; ?> value="<?php echo $row[0] ?>"><?php echo $row[0] . ' ' . $row[1];?></option>
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
		<option <?php if($language == $row[1]) echo selected; ?> value="<?php echo $row[0] ?>"><?php echo $row[1];?></option>
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
	<td><select name="ddlBottomColor">
	    <? $s_color = $bottomColor; include("./subroutines/color_picker.php"); ?>
	    </select> 
	</td>
</tr>
<tr>
	<td>Idle Behavior</td>
	<td><select name="ddlIdle">
	    <? $s_idle = $idle; include("./subroutines/idle_picker.php"); ?>
	    </select> 
	</td>
</tr>
<tr>
	<td>Button Behavior</td>
	<td><select name="ddlButton">
	    <? $s_button = $button; include("./subroutines/button_picker.php"); ?>
	    </select> 
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

$schedCount = 11;
$result = mysqli_query($con,"call sp_GetSchedule($rabbitID)");
if (!$result) die('Call to schedule proc failed. ' . mysqli_error($con));
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
  
	if($debug == true)
	{
		echo "Schedule proc returned $msg for msg<br>";
	}
	
	if($msg != "OK") 
	{
		echo $msg . $back;
		return;
	}
	
	if($debug==true)
	{
	  echo "$i:<br>";
		echo "s_enab = $s_enab<br>";
		echo "s_from = $s_from<br>";
		echo "s_to   = $s_to<br>";
		echo "s_min  = $s_min<br>";
		echo "s_desc = $s_desc<br>";
		echo "s_msg  = $msg<br>";
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
	echo "<td><select id=min$i name=min$i>"; include("./subroutines/minute_picker.php"); echo "</select>	</td>";
	echo "<td><select id=action$i name=action$i>"; include("./subroutines/action_picker.php"); echo "</select></td>";
	echo "</tr>";
	
}

mysqli_free_result($result);

if($i == 0)
{
	for($i=1; $i < $schedCount; $i++)
	{
		echo "<tr>";
		echo "<td><input type=checkbox id=enabled$i name=enabled$i></td>";
		echo "<td><select id=from$i name=from$i>"; include("./subroutines/time_picker.php"); echo "</select></td>";
		echo "<td><select id=to$i name=to$i>"; include("./subroutines/time_picker.php"); echo "</select></td>";
		echo "<td><select id=min$i name=min$i>"; include("./subroutines/minute_picker.php"); echo "</select>	</td>";
		echo "<td><select id=action$i name=action$i>"; include("./subroutines/action_picker.php"); echo "</select></td>";
		echo "</tr>";
	}
}

 
?>
</table>
<P>
Note you should not have two events at the same minute at the same hour or the rabbit will hyperventilate.  If you want to always tell the time at the top of the hour then you would check enable, select midnight, midnight, Tell the Time, and 00.  The sleep and wake times override this schedule. 
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

if($reboot == 1)
	$reboot='checked';

?>
<fieldset><legend><b>RSS Feed</b></legend>
Here you can setup your own RSS feed.  Enter the URL to the feed, click validate to test.

<table cellpading=0 cellspacing=4>
	<tr>
		<td>RSS URL 1</td>
		<td><input type=text id=txtRSS1 name=txtRSS1 maxlength=255 size=100 value=<? echo $rss_url[0] ?>></td>
		<td><input type=submit name=btnValidate1 value=Validate></td>
	</tr>
	<tr>
		<td>RSS URL 2</td>
		<td><input type=text id=txtRSS2 name=txtRSS2 maxlength=255 size=100 value=<? echo $rss_url[1] ?>></td>
		<td><input type=submit name=btnValidate2 value=Validate></td>
	</tr>
	<tr>
		<td>RSS URL 3</td>
		<td><input type=text id=txtRSS3 name=txtRSS3 maxlength=255 size=100 value=<? echo $rss_url[2] ?>></td>
		<td><input type=submit name=btnValidate3 value=Validate></td>
	</tr>

</table>
</fieldset>
<P>

<fieldset><legend><b>RFID</b></legend>
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
if (!$result) die('Call to RFID proc failed. ' . mysqli_error($con));
mysqli_use_result($con);
	
$i=0;

while($row = mysqli_fetch_row($result))
{
	$i=$i+1;
	
	$tag = $row[0];
	$s_desc = $row[1];
	$tdesc = $row[2];

  echo "<tr>";
	echo "<td><input type=text value=$tag readonly name=tag$i id=tag$i></td>";
	echo "<td><input type=text name=tdesc$i id=tdesc$i maxlength=30 size=40 value=$tdesc></td>";
	echo "<td><select id=rfid$i name=rfid$i>"; include("./subroutines/action_picker.php"); echo "</select></td>";
	echo "</tr>";
	
}

mysqli_next_result($con);
mysqli_close($con); 


?>
</table>
</fieldset>  

<P>
<fieldset><legend><b>Rabbit restart</b></legend>
If you need to restart your rabbit, click the restart rabbit button and your rabbit will be sent the restart command.
For this to work, your rabbit must be <a href="check_rabbit_entry.php">reaching the server</a>.  
<P>
<input type=submit name=btnRestart value="Restart Rabbit">
&nbsp;&nbsp;&nbsp;Restart my rabbit every night at midnight<input type=checkbox name=chkReboot <?php echo $reboot;?>>

</fieldset>
<P>
Memory usage = 
<?php
echo memory_get_usage();
?>

<P>

<a href="index.php">Home</a>

</form>
</body>
</html>

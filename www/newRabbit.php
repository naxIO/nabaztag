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
$_SESSION['newRabbit']=1;
?>
<html>
<body background="">
<Form Name ="frmNewRabbit" Method ="POST" ACTION = "saveNewRabbit.php">
<center><h2>Set up a New Rabbit</h2></center>
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
$con = mysqli_connect($host,$user,$pass,$db);

if (!$con) 
{
    logError('newRabbit.php: Could not connect: ' . mysqli_error($con));
    echo 'An error has occurred. The head rabbit has been placed on notice.';
    return;
}

$cmd = 'call sp_GetTimeZone();';

$result = mysqli_query($con, $cmd); 

if (!$result) 
{ 
    logError('newRabbit.php: Invalid time zone query: ' . mysqli_error($con));
    echo 'An error has occurred. The head rabbit has been placed on notice.';
    return;
}

if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.

$cmd = 'call sp_GetLanguage();';

$resultLang = mysqli_query($con, $cmd);  

if (!$resultLang) 
{
    logError('newRabbit.php: Invalid language query: ' . mysqli_error($con));
    echo 'An error has occurred. The head rabbit has been placed on notice.';
    return;
}

?>

<table cellpading=0 cellspacing=4>
<tr>
	<td>Serial #</td>
  <td><input id=txtSerial name=txtSerial type=text size=30 maxlength=17> <a href="serial.htm">Where do I find this?</a>
</tr>
<tr>
	<td>Rabbit's Name </td>
	<td><input id=txtName name=txtName type=text size=30 maxlength=20> </td> 
</tr>
<tr>
	<td>Time Zone </td>
	<td>
	<select name="ddlTimeZone">
	<?php
		while($row = mysqli_fetch_row($result))
		 { ?>
		<option value="<?php echo $row[0] ?>"><?php echo $row[0];?></option>
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
		<option value="<?php echo $row[0] ?>"><?php echo $row[1];?></option>
		<?php }
		?>
  </select>
	</td>
</tr>
<tr>
	<td>Weather Code</td>
	<td><input name=txtWeatherCode type=text size=30 maxlength=20> <a href="weather_setup.htm">Where do I find this?</a></td>
</tr>
<tr>
	<td>Wake Up Time </td>
	<td><select name="ddlWake">
	    <? readfile("time_picker.htm"); ?>
	    </select>
	</td>
</tr>
<tr>
	<td>Bed Time </td>
	<td><select name="ddlBedTime">
	    <? readfile("time_picker.htm"); ?>
	    </select>
	</td>
</tr>
<tr>
	<td>Temperature </td>
	<td><select name="ddlTemp">
	    <? readfile("temp_picker.htm"); ?>
	    </select>
	</td>
</tr>
</table>
<P>
<b>Choose the following features for your rabbit:</b>
<p>
<table cellpading=0 cellspacing=4>
<tr bgcolor="#DCDCDC">
  <td>Enabled</td>
	<td>From </td>
	<td>To</td>
	<td>Minute</td>
	<td>Action</td>
</tr>

<?php 
for($i = 1; $i < 11; $i++)
{  
	echo '<tr>';
  echo "<td><input type=checkbox id=enabled$i name=enabled$i>";
	echo "<td><select name=from$i>"; readfile("time_picker.htm"); echo '</select></td>';
	echo "<td><select name=to$i>"; readfile("time_picker.htm");  echo'</select></td>';
	echo "<td><select name=min$i>"; readfile("minute_picker.htm"); echo '</select>	</td>';
	echo "<td><select name=action$i>"; include("./subroutines/action_picker.php"); echo '</select></td>';
	echo '</tr>';
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

<P>
<input type=submit value="Save to Rabbit Hutch">
</form>
</body>
</html>

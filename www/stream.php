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
<center><h2>Stream .mp3 to rabbit</h2></center>
<img src="ping_rabbit.jpg" height="100px" style="float:right;"></img>
<P>

<center>Note .mp3 format is the only format supported.</center>
<P>
<?php
//error_reporting(E_ALL);  //don't use this in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log','../etc/nabaztag_error.log');
//error_reporting(0);  //don't use this, use ini_set or nothing goes to the log

$serialNbr   = $_REQUEST['txtSerial'];
$url         = $_REQUEST['url'];

include '../etc/nabaztag_db.php';

$con = mysqli_connect($host,$user,$pass,$db);
if (!$con) die('Could not connect: ' . mysqli_error());

$back=" Click your browser's back button to go back and correct this.";

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
}

if(intval($rabbitID) < 1)
{
	echo "Whoops!  We can't find your rabbit.  Did you enter the correct serial number? $back\n";
	$_SESSION['rawSerialNbr'] = '';
	return;
}

mysqli_next_result($con);
mysqli_close($con);

?>

<table cellpadding=0 cellspacing=5 width=80%>
<tr>
  <td>To</td>
	<td colspan=2>
		<input type=text id=txtName name=txtName disabled readonly value=<?php echo $name; ?>>
	</td>
</tr>
<tr>
	<td>URL</td>
  <td colspan=2><textarea name=txtURL id=txtURL rows=2 cols=80 maxlength=255></textarea><td>
</tr>
<tr>
  <td>&nbsp;</td>
  <td>
      <input type=submit value="Send to Rabbit" name=btnSend id=btnSend>
  </td>
</tr>
</table>
<input type=hidden name=hidSerialNbr id=hidSerialNbr value=<?php echo $serNbr; ?>>
<P>
</div>
</form>
<P>
Note the URL needs to be a URL to a .mp3 and it needs to be http protocol.  You can pull the URL out of a .m3u and send it like so:
<br>
<pre>
http://amazonm-272.vo.llnwd.net/s/d3/100222/100222865/202685272_S64.mp3?marketplace=1&e=1318033691&h=ca9fae29ef270b5d9b6fdb3bb54751a9
</pre>
<p>
<a href="index.php">Home</a>


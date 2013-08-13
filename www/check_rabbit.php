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
//error_reporting(E_ALL);
date_default_timezone_set('America/Chicago'); //for warning

echo '<html>';
echo '<div id=standalone>';
echo '<link rel="stylesheet" type="text/css" href="main.css" />';

//begin serial # check
$serialNbr = $_SESSION['rawSerialNbr'];
include './subroutines/clean.php';

$back=" Click your browser's back button to go back and correct this.";

if(strlen($serialNbr) < 17)  //not found in session
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

$_SESSION['rawSerialNbr'] = $serialNbr;
$serNbr = clean($serialNbr);
//end serial # check

include '../etc/nabaztag_db.php';
$con = mysqli_connect($host,$user,$pass,$db);
if (!$con) die('Could not connect: ' . mysqli_error());

$result = mysqli_query($con,"call sp_CheckRabbit('$serNbr')");
if (!$result) die('Invalid rabbit check query: ' . mysqli_error($con));

echo '<center><h2>Last Connect Time</h2><P> ';
echo '<table cellpadding=7 cellspacing=0 border=1>';
echo '<tr><th>Name</th><th>Last Connect</th></tr>';

while($row = mysqli_fetch_row($result))
{
	$name = $row[0];
	$last = $row[1];
	$time = $row[2];

	$_time = strtotime($time);	
	$_last = strtotime($last);
	
	if(($_time) && ($_last))
	{
		if($_time - $_last > 60)
			$bgcolor='#C86464'; //rgb(200,100,100)';
		else
		  $bgcolor='#0AC80A'; //'rgb(10,200,10)';
	}
	
	echo "<tr bgcolor=$bgcolor><td>$name</td>";
	echo "<td>$last</td></tr>";
}



echo '</table>';
echo "<P>Local server time is $time </center>";
mysqli_next_result($con);
mysqli_close($con); 

echo '<P>';
echo 'If you see your rabbit\'s name above then you know your rabbit is registered. ';
echo 'The time is in local server time which will probably not be your time zone. ';
echo 'If your rabbit is connecting, you should be able to refresh the page after a minute or so ';
echo 'and the time should be updated. If you don\'t see any updates, you should first push the ';
echo 'button on your rabbit and check again.  If that doesn\'t get your rabbit connected then ';
echo 'you should power cycle the rabbit. If your last connect time is all zeros, that means ';
echo 'your rabbit has never reached the server and you probably have a configuration or DNS issue. ';
//echo 'If you need help, you can always post to the <a href="forumEntry.php">forum</a>.';

?>
</div>
</html>

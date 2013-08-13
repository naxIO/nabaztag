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
date_default_timezone_set('America/Chicago'); //for warning
include './subroutines/logError.php';
?>
<html>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<META HTTP-EQUIV="EXPIRES" CONTENT="0">
<title>Nabaztag Lives</title>

<link rel="stylesheet" type="text/css" href="main.css" />

<div id="imgLeft">
	<img src="nabaztag.jpg" height="100px"></img>
</div>
	
<div id="title">
	<h2>NabaztagLives</h2>
</div>
	
<div id="centerCol">
	You're running NabaztagLives where you can bring your Nabaztag rabbit back to life. 
	<br>
	If this	is your first visit, click <a href="newRabbit.php">Setup New Rabbit</a>.    
</div>

<div id="cork_map">
	<a href="newRabbit.php" id="link1" class="postit"></a>
	<a href="updateRabbit.php" id="link2" class="postit"></a>
	<a href="./setup/serial.htm" id="link3" class="postit"></a>
	<a href="check_rabbit_entry.php" id="link4" class="postit"></a>
	<a href="issues.htm" id="link5" class="postit"></a>
	<a href="apilog.php" id="link6" class="postit"></a>
	<a href="errors.php" id="link7" class="postit"></a>
	<a href="rabbits.php" id="link8" class="postit"></a>
	
	<div id="imgRight">
		Registered Rabbits:
		<?php
			ini_set('display_errors', 0);
			ini_set('log_errors', 1);
			ini_set('error_log','../etc/nabaztag_error.log');
		
			//error_reporting(0);  //don't use this, use ini_set or nothing goes to the log
			
			include '../etc/nabaztag_db.php';
		
			$con = mysqli_connect($host,$user,$pass,$db);
		
			if (!$con) logError("index.php: $msg");
			
			$result = mysqli_query($con,"call sp_GetCount()");
		
			if (!$result) logError("index.php: $msg");
			
			while($row = mysqli_fetch_row($result))
			{
				$count = $row[0];
			}	
			
			echo "<h2>$count</h2>";
			
			mysqli_next_result($con);
		?>
	</div>
	<div id="spacer"></div>
	<div id="latest">
		Welcome our newest rabbit 
		<?php
			ini_set('display_errors', 0);
			ini_set('log_errors', 1);
			ini_set('error_log','../etc/nabaztag_error.log');
			
			$result = mysqli_query($con,"call sp_GetLatestRabbit()");
	
			if (!$result) logError("index.php: $msg");
	
			while($row = mysqli_fetch_row($result))
			{
				$latest = $row[0];
			}	
			
			echo $latest . '!';
			
			mysqli_next_result($con);
		?>

	</div>
</div>

<div id="IP" class="centerBoard">
	Point your rabbit to 
	<?php 
	if(strlen($_SERVER['SERVER_ADDR']) < 10) 
        echo gethostbyname(php_uname('n')); 
    else
        echo $_SERVER['SERVER_ADDR'];
    ?>    
</div>

<?php

    $file = "locate.jsp";
    
    if(! file_exists($file)) 
    {
        echo 'Warning - Locator records do not exist.';
        return;
    }
    
    $fh = fopen($file, 'r');
    
    if(! $fh)
    {
        echo 'Warning - cannot read locator records.';
        logError("index.php: Can't open $file");
        return;
    }    
    
    $data = fread($fh,filesize($file));
    fclose($fh);

    $IP = $_SERVER['SERVER_ADDR'];
    
    if(strlen($IP) < 8)
    {
        echo 'Warning - unable to determine IP.';
        return;
    }
    
    if(stristr($data,"ping $IP") == FALSE)
    {
        echo 'Warning - locator records are invalid.';
        return;
    }
    
    if(stristr($data,"broad $IP") == FALSE)
    {
        echo 'Warning - locator records are invalid.';
        return;
    }
    
?>
	
</html>
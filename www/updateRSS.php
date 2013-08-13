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
//purge the old RSS
if($debug==true)
{
	echo "serialNbr = $serialNbr<br>";
}

include '../etc/nabaztag_db.php';
$con = mysqli_connect($host,$user,$pass,$db);
if (!$con) die('Could not connect: ' . mysqli_error($con));

$result = mysqli_query($con,"call sp_PurgeRSS('$serialNbr', @msg)");
																			
if (!$result) die('RSS purge proc failed. ' . mysqli_error($con));

$result = mysqli_query($con,"select @msg");

while($row = mysqli_fetch_row($result))
{
	$msg = $row[0];
}

if($debug == true)
{
	echo "RSS purge proc returned $msg for msg";
}

if($msg != "OK") 
{
	echo $msg . $back;
	mysqli_close($con);
	return;
}
  
//add the RSS
for($i=1;$i < 4; $i++)
{
  //$enabled = clean($_REQUEST["enabled$i"]);
	$url     = clean2($_REQUEST["txtRSS$i"]);
  $enabled =1;
  
  if($debug==true)
  {
		echo "$i<br>";
		echo "enabled = $enabled<br>";
		echo "from    = $from<br>";
		echo "to      = $to<br>";
		echo "min     = $min<br>";
		echo "action  = $action<p>";
		echo "rabbitID= $rabbitID<P>";
	}
	
	if($enabled == "on")  
		$enable=1;
	else
		$enable=0;
	
	if(strlen($url) > 0)
	{
		$result = mysqli_query($con,"call sp_NewRSS('$serialNbr'
																					,'$url'
																					,$enable
																					,@msg)");
																					
		if (!$result) die('Invalid new RSS query: ' . mysqli_error($con));
	
		$result = mysqli_query($con,"select @msg");
	
		while($row = mysqli_fetch_row($result))
		{
			$msg = $row[0];
		}
		
		if($debug == true)
		{
			echo "RSS proc returned $msg for msg";
		}
		
		if($msg != "OK") 
		{
			echo $msg . $back;
		}
  }
}

mysqli_close($con);

?>
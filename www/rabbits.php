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

date_default_timezone_set('America/Chicago'); //for warning

$count = $_SESSION['rabbitCount'];

$ip = $_SERVER['REMOTE_ADDR'];

include '../etc/nabaztag_db.php';
$con = mysqli_connect($host,$user,$pass,$db);
if (!$con) die('Could not connect: ' . mysqli_error());

$cmd = 'call sp_GetRabbits();';

$result = mysqli_query($con,$cmd);

if (!$result) die('Invalid rabbit query: ' . mysqli_error($con));

echo '<center><table cellpadding=7 cellspacing=0 border=1>';
echo '<tr>';
echo '<th>Name</th>';
echo '<th>Last Connect Time </th>';
echo '<th>Last Command </th>';
echo '<th>Queue </th>';
echo '</tr>';

$i=0;
$sleep=0;

while($row = mysqli_fetch_row($result))
{
    $name = $row[0];
    $last = $row[1];
    $cmd = $row[3];
    $time = $row[2];
    $queue = $row[4];
  
    $_time = strtotime($time);    
    $_last = strtotime($last);
    
    if(($_time) && ($_last))
    {
        if($_time - $_last > 60)
            $bgcolor='#C86464'; //rgb(200,100,100)';
        else
        {
          $i=$i+1;
          if($cmd == 'SLEEP') $sleep = $sleep+1;
          $bgcolor='#0AC80A'; //'rgb(10,200,10)';
        }
    }
    

    echo "<tr bgcolor=$bgcolor><td><a href='peek.php?name=$name'>$name</a></td>";
    echo "<td>$last</td>";
    echo "<td>$cmd</td>";
    echo "<td>$queue</td></tr>";
}    
echo '</table>';

mysqli_next_result($con);

$cmd = 'call sp_GetStats();';

$result = mysqli_query($con,$cmd);
if (!$result) die('Invalid rabbit query2: ' . mysqli_error($con));

while($row = mysqli_fetch_row($result))
{
    $up = $row[0];
}

echo "<P>Local server time is $time";
echo "<br>$i active rabbits, $sleep sleeping";
echo "<br>MySQL uptime hours = " . round($up / 60 /60,2) . ", uptime days = " . round($up / 60 /60 /24,2);
echo "<br>Your IP is $ip";
echo "<br>Peak PHP memory usage is " . number_format(memory_get_peak_usage(TRUE)) . " bytes";

mysqli_next_result($con);
mysqli_close($con); 

echo "&nbsp;<P>&nbsp;<P>";

if($i < $count)
{
    echo '<script language="text/javascript">';
    echo "var sound = document.createElement('audio');";
    echo "sound.setAttribute('src', 'pac_dies.wav');";
    echo "sound.play();";
    echo '</script>';
}

if($i > $count)
{
    echo '<script language="text/javascript">';
    echo "var sound = document.createElement('audio');";
    echo "sound.setAttribute('src', 'defender.wav');";
    echo "sound.play();";
    echo '</script>';
}

$_SESSION['rabbitCount'] = $i;

?>

<a href="index.php">Home</a>
</html>
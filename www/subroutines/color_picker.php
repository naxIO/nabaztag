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
include '../etc/nabaztag_db.php';

$con2 = mysqli_connect($host,$user,$pass,$db);
if (!$con2) die('Could not connect: ' . mysqli_error($con2));

$resultAct = mysqli_query($con2,"SELECT name from color where name not in ('White','Blue') order by 1;");
if (!$resultAct) die('Color picker invalid query. ' . mysqli_error());

mysqli_use_result($con2);

while($rowAct = mysqli_fetch_row($resultAct))
 {      
   ?>
    <option <?php if($s_color == $rowAct[0]) echo 'selected'; ?> value="<?php echo $rowAct[0];  ?>"><?php echo $rowAct[0];?>
    </option>
<?php }

mysqli_free_result($resultAct);
mysqli_close($con2);
?>


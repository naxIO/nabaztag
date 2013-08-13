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
$rawSerialNbr = $_SESSION['rawSerialNbr'];
if(strlen($rawSerialNbr) > 16)
	header( 'Location: updateRabbit2.php' ) ;

?>
<html>
<h2><center>Update a Rabbit</center></h2>
<P>
<Form Name ="frmUpdateRabbit" Method ="POST" ACTION = "updateRabbit2.php">
Enter your rabbit's serial number: 
<input type=text name="txtSerNbr" id="txtSerNbr"> <a href="serial.htm">Where do I find this?</a>
<P>
<input type=submit value="Search Rabbit Hutch">
</form>
</html>

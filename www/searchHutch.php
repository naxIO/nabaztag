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
$_SESSION['newRabbit']=0;
?>
<html>
<body background="">
<Form Name ="frmSearchHutch" Method ="POST" ACTION = "updateRabbit2.php">
<?php
include '../etc/nabaztag_db.php';
$con = mysql_connect($host,$user,$pass);
if (!$con) die('Could not connect: ' . mysql_error());

mysql_select_db('nabaztag_nabaztag'); 

//search for the rabbit we were given

$back=" Click your browser's back button to go back and correct this.";

$serNbr = $_REQUEST['txtSerNbr'];

if(substr_count($serialNbr, ':') < 5)
{
	echo "'$serNbr' is not a valid serial number.  Remember to include the colons.  $back";
	return;
};

if(strlen($serNbr) < 17)
{
  echo "'$serNbr' is not a valid serial number. $back";
  return;
};

$result = mysql_query("SELECT id from rabbit where serialNbr = '$serNbr'");
if (!$result) die('Invalid query: ' . mysql_error());

while($row = mysql_fetch_row($result))
{
	$msg = $row[0];
	$rabbitID = $row[1];
}

?>

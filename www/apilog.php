<html>
<head>
<meta http-equiv="refresh" content="60">
</head>
<form method="POST" action="apilog.php">
<input type=submit name=btnClear id=btnClear value="Clear API Log">
<P>
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
$ip = $_SERVER['REMOTE_ADDR'];

$file='../etc/api_calls.log';

if(isset($_POST['btnClear']))
{
	unlink($file);
	touch($file);
	echo 'Log cleared';
	return;
}

$data = file_get_contents($file);
$data = str_replace("\n",'<p>',$data);
echo $data;

?>
</form>
</html>
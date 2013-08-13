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
function goodSerial($serialNbr)
{
	$back=" Click your browser's back button to go back and correct this.";
	
	if(strlen($serialNbr) < 17)  //not found in session
	{
		$serialNbr = $_REQUEST['txtSerNbr'];
		
		if(substr_count($serialNbr, ':') < 5)  //5 colons
		{
			echo "'$serNbr' is not a valid serial number.  Remember to include the colons.  $back";
			return false;
		};
		
		if(strlen($serialNbr) < 17)
		{
			echo "'$serialNbr' is not a valid serial number. $back";
			return false;
		};
	}
	
	return true;
}

?>
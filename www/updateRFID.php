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
//update RFID tag assignment to function

include '../etc/nabaztag_db.php';
$con = mysqli_connect($host,$user,$pass,$db);
if (!$con) logError('Could not connect: ' . mysqli_error($con));
  
//add the RSS
for($i=1;$i < 10; $i++)
{
    if(isset($_REQUEST["tag$i"]))
    {
        $tag = clean2($_REQUEST["tag$i"]);
        $function = clean2($_REQUEST["rfid$i"]);
        $desc = clean2($_REQUEST["tdesc$i"]);
 
        if(strlen($tag) > 0)
        {
            $result = mysqli_query($con,"call sp_SaveRFID('$serialNbr'
                                                         ,'$tag'
                                                         ,'$function'
                                                         ,'$desc'
                                                         ,@msg)");
                                                                                    
            if (!$result) logError('Invalid RFID update query: ' . mysqli_error($con));
    
            $result = mysqli_query($con,"select @msg");
    
            while($row = mysqli_fetch_row($result))
                $msg = $row[0];
        
            if($msg != "OK") 
            {
                //echo $msg . $back;
                logError("Error in updateRFID.php: $msg");
            }
        }
    }
}

mysqli_close($con);

?>
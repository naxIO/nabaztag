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
//purge the old schedules
if($debug==true)
{
    echo "serialNbr = $serialNbr<br>";
}

$result = mysqli_query($con,"call sp_PurgeSchedule('$serialNbr', @msg)");
                                                                            
if (!$result) die('Purge proc failed. ' . mysqli_error($con));

$result = mysqli_query($con,"select @msg");

while($row = mysqli_fetch_row($result))
{
    $msg = $row[0];
}

if($debug == true)
{
    echo "Purge proc returned $msg for msg";
}

if($msg != "OK") 
{
    echo $msg . $back;
    return;
}
  
//add the schedules
for($i=1;$i < 11; $i++)
{
    if(isset($_REQUEST["enabled$i"]))
    {
        $enabled = clean($_REQUEST["enabled$i"]);
        $from    = clean($_REQUEST["from$i"]);
        $to      = clean($_REQUEST["to$i"]);
        $min     = clean($_REQUEST["min$i"]);
        $action  = clean($_REQUEST["action$i"]);
    
        if($enabled == "on")  
            $enable=1;
        else
            $enable=0;
    
        $result = mysqli_query($con,"call sp_NewSchedule('$serialNbr'
                                    ,$enable
                                    ,'$from'
                                    ,'$to'
                                    ,'$min'
                                    ,'$action'
                                    ,$i
                                    ,@msg)");
                            
        if (!$result) 
        {
            logError('Invalid new schedule query: ' . mysqli_error($con));
            return;
        }
        
        $result = mysqli_query($con,"select @msg");

        while($row = mysqli_fetch_row($result))
        {
            $msg = $row[0];
        }
    
        if($debug == true)
        {
            echo "proc returned $msg for msg";
        }
    
        if($msg != "OK") 
        {
            echo $msg . $back;
            return;
        }
    
        mysqli_next_result($con);  //required to avoid sync error
    }      
}
?>
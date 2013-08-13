<html>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
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
//error_reporting(E_ALL);  //don't use this in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log','../etc/nabaztag_error.log');
//error_reporting(0);  //don't use this, use ini_set or nothing goes to the log

$debug=false;
date_default_timezone_set('America/Chicago'); //for warning

include './subroutines/clean.php';

//validate the new rabbit parms

$serialNbr   = $_REQUEST['txtSerial'];
$name        = $_REQUEST['txtName'];
$timeZone    = $_REQUEST['ddlTimeZone'];
$weatherCode = $_REQUEST['txtWeatherCode'];
$wakeHour    = $_REQUEST['ddlWake'];
$sleepHour   = $_REQUEST['ddlBedTime'];
$language    = $_REQUEST['ddlLanguage'];
$temp        = $_REQUEST['ddlTemp'];

if(isset($_REQUEST['email'])) 
    $email = $_REQUEST['email'];
else
    $email = '';
    
if($debug == true)
{
    echo "ser #    = $serialNbr<br>";
    echo "name     = $name<br>";
    echo "timeZone = $timeZone<br>";
    echo "weather  = $weatherCode<br>";
    echo "wake     = $wakeHour<br>";
    echo "sleep    = $sleepHour<br>";
    echo "temp     = $temp<P>";
}

$back=" Click your browser's back button to go back and correct this.";

//validation

if(substr_count($serialNbr, ':') < 5)
{
    echo "'$serialNbr' is not a valid serial number.  Remember to include the colons. $back";
    return;
};

if(strlen($name) < 3)
{
    echo "'$name' is not a valid name.  It needs to be at least three characters. $back";
    return;
};

include '../etc/nabaztag_db.php';
$con = mysqli_connect($host,$user,$pass,$db);

if (!$con)
{
    logError("Error in saveNewRabbit, connect to DB, user $name. " . mysqli_error($con));
    return;
}

$msg='';
$rabbitID=-1;

$serialNbr = clean($serialNbr);
$name = clean($name);
$timeZone = clean($timeZone);
$weatherCode = clean($weatherCode);
$email = clean($email);
$language = clean($language);
$temp = clean($temp);

if($debug == true)
{
    echo "cleaned values:<P>";
    echo "serialNbr = $serialNbr<br>";
    echo "name = $name<br>";
    echo "timeZone = $timeZone<br>";
    echo "weatherCode = $weatherCode<br>";
    echo "email = $email<br>";
    echo "language = $language<br>";
}

$result = mysqli_query($con, "call sp_newRabbit('$serialNbr'
                                                ,'$name'
                                                ,'$timeZone'
                                                ,'$weatherCode'
                                                ,'$email'
                                                ,'$wakeHour'
                                                ,'$sleepHour'
                                                ,'$language'
                                                ,'$temp'
                                                ,@msg
                                                ,@rabbitID)");
                                        
if (!$result)
{
    logError("Error in saveNewRabbit, call to new rabbit proc, user $name. " . mysqli_error($con));
    echo 'The operation failed.  The head rabbit has been placed on notice.';
    return;
}

$result = mysqli_query($con, "select @msg, @rabbitID");

while($row = mysqli_fetch_row($result))
{
    $msg = $row[0];
    $rabbitID = $row[1];
}

if($debug == true)
{
    echo "New rabbit proc returned $msg for msg";
    echo "New rabbit proc returned $rabbitID for ID";
}

if($msg != "OK") 
{
    echo $msg . $back;
    return;
}

//purge the old schedules
$result = mysqli_query($con, "call sp_PurgeSchedule('$serialNbr', @msg)");
                                                                            
if (!$result)
{
    logError("Error in saveNewRabbit, user $name. " . mysqli_error($con));
    echo 'The operation failed.  The head rabbit has been placed on notice.';
    return;
}

$result = mysqli_query($con, "select @msg");

while($row = mysqli_fetch_row($result))
{
    $msg = $row[0];
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
        $enabled = clean($_REQUEST["enabled$i"]);
    else
        $enabled = 'Off';
        
    $from    = clean($_REQUEST["from$i"]);
    $to      = clean($_REQUEST["to$i"]);
    $min     = clean($_REQUEST["min$i"]);
    $action  = clean($_REQUEST["action$i"]);
  
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
    
    $result = mysqli_query($con, "call sp_NewSchedule('$serialNbr'
                                                      ,$enable
                                                      ,'$from'
                                                      ,'$to'
                                                      ,'$min'
                                                      ,'$action'
                                                      ,$i
                                                      ,@msg)");
                                        
    if (!$result)
    {
        logError("Error in saveNewRabbit, call to new schedule proc, user $name. " . mysqli_error($con));
        echo 'The operation failed.  The head rabbit has been placed on notice.';
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
        logError("Error in saveNewRabbit, user $name. $msg");
        return;
    }
  
}

echo "Your rabbit has been saved.<P> <a href=index.php>Home</a>";

exit(0);

/************************************************************
 * log error.  You must create file and permission in advance.
 ************************************************************/
function logError($msg)
{
    $date = date("Y-m-d H:i:s");
    $msg = "[$date] $msg \n";
    $file='../etc/nabaztag_error.log';
    error_log($msg,3,$file);
}


?>
</html>
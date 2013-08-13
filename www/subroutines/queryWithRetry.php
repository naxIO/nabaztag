<?php
/********************************************************
 * Query with retry will retry the query and return true
 * upon success.  
 * Parms: con - connection to DB. must be opened already.
 *        cmd - command to execute
 *        rabbit - can be name or ID of rabbit.
 *        function - string containing the caller's name
 ********************************************************/
function queryWithRetry($con,$cmd,$rabbit,$caller)
{
    $result = false;
    $retry  = 0;
    $max    = 3;
    $time1  = date("H:i:s");
  
    if(mysqli_more_results($con)) mysqli_next_result($con);  //Commands out of sync; you can't run this command now error.

    $result = mysqli_query($con,$cmd);
    
    if(!$result)
    {
        $ping = mysqli_ping($con);  //reconnect
        
        if(!$ping)
        {
            include '../../etc/nabaztag_db.php';
            $con = mysqli_connect($host,$user,$pass,$db);
        
            while(!$con && $retry < $max) 
            {
                usleep(1000000);  //1 sec
                $con = mysqli_connect($host,$user,$pass,$db);
                $retry++;
            }
            
            if (!$con) 
            {
                logError("queryWithRetry failed to connect to DB executing $cmd for $rabbit in $caller: " . mysqli_connect_errno() . mysqli_connect_error());
                return $result;
            }
            
            logError("queryWithRetry successful restablish to DB for $rabbit.");
        }

        $retry=0;
        
        while(!$result && $retry < $max)
        {
            usleep(1000000);  //1 sec
            $result = mysqli_query($con,$cmd);
            $retry++;
        }
    
        if (!$result)
        {
            $time2 = date("H:i:s");
            logError("queryWithRetry failed after retry executing $cmd for $rabbit in $caller.  Began at $time1 and ended at $time2." . mysqli_error($con));
        }
    }
    
    return $result;
}    

?>
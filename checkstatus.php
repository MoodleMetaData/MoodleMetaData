<html>
<head>
<title>Node status for load balancer</title>
</head>
<body>

<?php // check status page for load balancer
    /* This is a custom script needed for status check for a loadbalancer 
     * or external monitoring
     * It performs the following checks:
     * 1. By executing it shows that web server is up and it can process php files
     * 2. It check database connectivity and ability to execute a simple select
     *    'select 1 from dual;'
     * 3. It checks the existence and permissions of /moodledata
     */
    
    require_once('config.php');
    global $DB;
    
    $res = ""; // output buffer
    
    // check the database
    
    $res = $res . "Status of " . $_SERVER["SERVER_NAME"];
    $res = $res . " ( " . $_SERVER["SERVER_ADDR"] . " ) <br/>";
    
    $res = $res . "Node Name:" . gethostname();

    $res = $res .  "  <br/>";
    $dircheck = $CFG->dataroot . "/filedir" ; 
    if (file_exists( $dircheck ))
    {
        if (is_writable( $dircheck ) )
        {
            $res = $res . "Moodledata check: OK <br/>";            
        } else {
            $res = $res . "Moodledata check: FAILED (exists, but not writeable)<br/>";
        }
    } else {
        $res = $res . "Moodledata check: FAILED<br/>";
        
    }
    
    // DB CHECK HERE ACTIVE / ERRROR?
    try {
        $val = $DB->get_record_sql('SELECT 1');
        $res = $res . "DB Check: OK <br/>"  ;
    } catch (Exception $ex) {
        $res = $res . "DB Check: FAILED <br/>"  ;
        
    }   
    
    
    $arr = sys_getloadavg();
    
    $res = $res . "Load Average: "; 
    $res = $res . number_format($arr[0],2) ." ". number_format($arr[1],2) ." ". number_format($arr[2],2);
    
    echo $res;
    ?>
</body>
</html>


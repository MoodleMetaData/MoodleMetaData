<?php
global $PAGE, $CFG, $DB;
        require_once('../../config.php');
        $PAGE ->set_title(get_string('pluginname','local_xiaoranmoodlemetadata'));
        echo "this is just a test";
        $sql = "select column_name from Information_schema.columns where Table_name like 'mdl_courseinfo'";  
	$sqlq = "use moodledb";
	DB->execute($sqlq, array $parms=null)

//        $user = $DB->get_record_sql('SELECT * FROM {mdl_courseinfo} WHERE courseid = ?', array(1111));
  //      echo $user;

        $columsname = $DB->get_records_sql($sql, array $params=null, $limitfrom=0, $limitnum=0);
	echo json_encode($columsname);
?>

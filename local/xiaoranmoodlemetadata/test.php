<?php
global $PAGE, $CFG, $DB;
	require_once $CFG->dirroot.'/lib/formslib.php';
	require_once $CFG->dirroot.'/lib/datalib.php';
        require_once('../../config.php');
       // $PAGE ->set_title(get_string('pluginname','local_xiaoranmoodlemetadata'));
        //echo "this is just a test";
	$sqlmy = "SELECT * FROM {mdl_coursesession}");
	//$table = "mdl_coursesession";
 	if($result=$DB->get_records_sql($sqlmy)){
	 	echo '<table border="1"><tr>
		<th>sessionid</th>
		<th>sessiondate</th>
		<th>sessiontopic</th></tr>';
		foreach($result as $item){
			echo '<tr><td>'.implode("</td><td>",
			(array)($item)).'</td?</tr?';
		}
		echo '</table>';
	}
	//echo $numberq;
?>


<?php

// Moodle settings and security based on capability
define("MOODLE_INTERNAL", TRUE);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

global $DB, $CFG;

echo '<html>';
echo '<head>';
echo <<<JSCRIPT

<script type="text/javascript">
<!--
function confirm_delete() {
	var answer = confirm("Are you want to delete all selected records?")
	if (answer){
		return true;
	}
	else{
        return false;
	}
}
//-->
</script>

JSCRIPT;

echo <<<CSS
<style>
table {
border: #000044 solid thin;
}
table th {
border: #000044 solid thin;
}
table td {
border: #000044 solid thin;
}
</style>
CSS;


if(empty($_GET['courseid'])){
    echo 'Missing course "courseid"';
    exit();
}
if(!empty($_GET['module'])) {
   $module = $_GET['module'];
    if($record = $DB->get_record('modules',array('id' =>$module))) {
        $modulename = $record->name;
        $optional = '&module='.$module;
    }
    else {
        $optional = '';
    }
}
else {
    $optional = '';
}

echo '</head><body>';

//$processed_rec = array();
//process_records($processed_rec, $records);

if (isset($_POST['id']) && isset($_POST['action'])) {
    switch($_POST['action']){
        case 'delete':
            $where = "id=". $_POST['id'];

            if (!$DB->delete_records_select("course_modules", $where)) {
                echo "<div>Error - Failed to delete records from the database.</div>";
                exit;
            }
            else {
                echo "<div>Delete Successful!</div>";
            }
            break;
    }
}
$a_fields = array('id','course','module','instance','section','idnumber','added','score','groupmode','groupingid');
// Query to show the bugged courses
if(!empty($module)) {
    $sql = 'select ' . implode(',',$a_fields) . ' from {course_modules} gc where gc.course='. $_REQUEST['courseid'].' and gc.module='.$module.' and gc.instance NOT IN (select e.id from {'.$modulename.'} e where e.course='.$_REQUEST['courseid'].')';
}
else {
    $sql = 'select ' . implode(',',$a_fields) . ' from {course_modules} gc where gc.course='. $_REQUEST['courseid'];
}

$records = $DB->get_records_sql($sql, null);

echo form_for_records($records,$a_fields, $optional);

echo '</body></html>';


function form_for_records($a_records, $a_headers, $optional){
//    var_dump($a_records);
    $headers_row = '<tr>';
    foreach($a_headers as $h){
        $headers_row .= "<th>$h</th>";
    }
    $headers_row .= '</tr>';

    $body = '';
    foreach($a_records as $a_rec){
        $body .= '<tr>';
        $body .= "<td><a href='course_modules_edit.php?id={$a_rec->id}'>edit</a><br/><input name='id' value='". $a_rec->id ."' type='radio' />". $a_rec->id ."</td>";
        foreach($a_rec as $name=>$rec){
            if($name == 'id'){
                continue;
            }
            $body .= "<td>$rec</td>";
        }
        $body .= '</tr>';
    }

    $form = <<<FORM
<form method='post' action='course_modules_view.php?courseid=${_REQUEST['courseid']}$optional'>
    <table>
    $headers_row
    $body
    </table>
    <input type='submit' name='action' value='delete' onclick='return confirm_delete()' />
</form>
FORM;

    return $form;


}
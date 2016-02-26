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
	return do_confirm("Are you want to delete all selected records?");
}
function confirm_update() {
    return do_confirm("Are you sure you want to update record?");
}
function do_confirm(msg) {
	var answer = confirm(msg)
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


if(empty($_REQUEST['id'])){
    echo 'Missing grade category "id"';
    exit();
}
$a_fields = array('id','courseid','parent','depth','path','fullname','aggregation','aggregateonlygraded','aggregateoutcomes','aggregatesubcats');
echo '</head><body>';

if (isset($_POST['id']) && isset($_POST['action'])) {
    switch($_POST['action']){
        case 'update':
            $record = new StdClass();
            $record->id = $_POST['id'];
            $record->courseid = $_POST['courseid'];
            $record->parent = (empty($_POST['parent']) && $_POST['parent'] == '')? null:$_POST['parent'];  //can be null
            $record->depth = $_POST['depth'];
            $record->path = $_POST['path'];
            $record->fullname = $_POST['fullname'];
            $record->aggregation = $_POST['aggregation'];
            $record->aggregateonlygraded = $_POST['aggregateonlygraded'];
            $record->aggregateoutcomes = $_POST['aggregateoutcomes'];
            $record->aggregatesubcats = $_POST['aggregatesubcats'];

            if (!$DB->update_record("grade_categories", $record)) {
                echo "<div>Error - Failed to update records from the database.</div>";
                exit;
            }
            else {
                echo "<div>Update Successful!</div>";
            }
            break;
    }
}

// Query to show the bugged courses
$sql = 'select ' . implode(',',$a_fields) . ' from {grade_categories} gi where id='. $_REQUEST['id'];
$record = $DB->get_record_sql($sql, null);

echo form_for_records(array($record),$a_fields);

echo '</body></html>';


function form_for_records($a_records, $a_headers){
//    var_dump($a_records);
    $headers_row = '<tr>';
    foreach($a_headers as $h){
        $headers_row .= "<th>$h</th>";
    }
    $headers_row .= '</tr>';

    $body = '';
    foreach($a_records as $a_rec){
        $body .= '<tr>';
        $body .= "<td><input name='id' value='". $a_rec->id ."' type='hidden' />". $a_rec->id ."</td>";
        foreach($a_rec as $name=>$rec){
            if($name == 'id'){
                continue;
            }
            $body .= "<td><input type='text' name='$name' value='$rec'/></td>";
        }
        $body .= '</tr>';
    }

    $form = <<<FORM
<form method='post' action='course_grade_categories_edit.php?id={$_REQUEST['id']}'>
    <table>
    $headers_row
    $body
    </table>
    <input type='submit' name='action' value='update' onclick='return confirm_update()' />
</form>
FORM;

    return $form;


}
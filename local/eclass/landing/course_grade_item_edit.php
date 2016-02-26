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
    echo 'Missing grade item "id"';
    exit();
}
$a_fields = array('id','courseid','categoryid','itemname','itemtype','itemmodule','iteminstance','itemnumber','iteminfo','idnumber','gradetype','aggregationcoef');
echo '</head><body>';

if (isset($_POST['id']) && isset($_POST['action'])) {
    switch($_POST['action']){
        case 'update':
            $record = new StdClass();
            $record->id = $_POST['id'];
            $record->courseid = $_POST['courseid'];
            $record->categoryid = (empty($_POST['categoryid']) && $_POST['categoryid'] == '')? null:$_POST['categoryid'];
            $record->itemname = $_POST['itemname'];
            $record->itemtype = $_POST['itemtype'];
            $record->itemmodule = $_POST['itemmodule'];
            $record->iteminstance = (empty($_POST['iteminstance']) && $_POST['iteminstance'] == '')? null:$_POST['iteminstance'];
            $record->itemnumber = (empty($_POST['itemnumber']) && $_POST['itemnumber'] == '')? null: $_POST['itemnumber'];
            $record->iteminfo = $_POST['iteminfo'];
            $record->idnumber = $_POST['idnumber'];
            $record->gradetype = $_POST['gradetype'];
            $record->aggregationcoef = $_POST['aggregationcoef'];

            if (!$DB->update_record("grade_items", $record)) {
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
$sql = 'select ' . implode(',',$a_fields) . ' from {grade_items} gi where id='. $_REQUEST['id'];
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
<form method='post' action='course_grade_item_edit.php?id={$_REQUEST['id']}'>
    <table>
    $headers_row
    $body
    </table>
    <input type='submit' name='action' value='update' onclick='return confirm_update()' />
</form>
FORM;

    return $form;


}
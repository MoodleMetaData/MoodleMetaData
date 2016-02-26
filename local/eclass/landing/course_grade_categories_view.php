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

echo '</head><body>';
echo "<p><a href='course_grade_item_view.php?courseid={$_GET['courseid']}'>Go To Grade Item View</a></p>";




//$processed_rec = array();
//process_records($processed_rec, $records);

if (isset($_POST['id']) && isset($_POST['action'])) {
    switch($_POST['action']){
        case 'delete':
            $where = "id=". $_POST['id'];

            if (!$DB->delete_records_select("grade_categories", $where)) {
                echo "<div>Error - Failed to delete records from the database.</div>";
                exit;
            }
            else {
                echo "<div>Delete Successful!</div>";
            }
            break;
    }
}
$a_fields = array('id','courseid','parent','depth','path','fullname','aggregation','aggregateonlygraded','aggregateoutcomes','aggregatesubcats');
// Query to show the bugged courses
$sql = 'select ' . implode(',',$a_fields) . ' from {grade_categories} gc where courseid='. $_REQUEST['courseid'];
$records = $DB->get_records_sql($sql, null);

echo form_for_records($records,$a_fields);

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
        $body .= "<td><a href='course_grade_categories_edit.php?id={$a_rec->id}'>edit</a><br/><input name='id' value='". $a_rec->id ."' type='radio' />". $a_rec->id ."</td>";
        foreach($a_rec as $name=>$rec){
            if($name == 'id'){
                continue;
            }
            $body .= "<td>$rec</td>";
        }
        $body .= '</tr>';
    }

    $form = <<<FORM
<form method='post' action='course_grade_categories_view.php?courseid=${_REQUEST['courseid']}'>
    <table>
    $headers_row
    $body
    </table>
    <input type='submit' name='action' value='delete' onclick='return confirm_delete()' />
</form>
FORM;

    return $form;


}
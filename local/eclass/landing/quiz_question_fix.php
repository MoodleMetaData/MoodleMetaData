<?php
/**
 * Created by IntelliJ IDEA.
 * User: ggibeau
 * Date: 2012-10-16
 * Time: 10:59 AM
 * To change this template use File | Settings | File Templates.
 */

// Moodle settings and security based on capability
define("MOODLE_INTERNAL", TRUE);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

global $DB, $CFG;
echo '<html>';

/**
 * make_unique_id_code
 *
 * @todo Finish documenting this function
 *
 * @uses $_SERVER
 * @param string $extra Extra string to append to the end of the code
 * @return string
 */
function ctl_make_unique_id_code($hostname='unknownhost', $extra='') {

    if($hostname == 'unkownhoat') {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $hostname = $_SERVER['HTTP_HOST'];
        } else if (!empty($_ENV['HTTP_HOST'])) {
            $hostname = $_ENV['HTTP_HOST'];
        } else if (!empty($_SERVER['SERVER_NAME'])) {
            $hostname = $_SERVER['SERVER_NAME'];
        } else if (!empty($_ENV['SERVER_NAME'])) {
            $hostname = $_ENV['SERVER_NAME'];
        }
    }

    $date = gmdate("ymdHis");

    $random =  random_string(6);

    if ($extra) {
        return $hostname .'+'. $date .'+'. $random .'+'. $extra;
    } else {
        return $hostname .'+'. $date .'+'. $random;
    }
}
echo 'Processing questions:...';
// Retrieve Result Set of question records that have equivalent stamp and version and fix them
$sql = "select * from {question} where stamp=? and stamp=version";
$rs = $DB->get_recordset_sql($sql,array('192.168.1.106+101026203313+AnSOkP'));

$count = 0;      // count successful updates
$failCount = 0;  // count failures

foreach ($rs as $record) {
    // Lets fix these!
    $record->stamp = ctl_make_unique_id_code('move2moo.com');
    $record->version = ctl_make_unique_id_code('move2moo.com');

    try {
        if($DB->update_record('question',$record)) {
            $count++;
        }
    } catch (dml_exception $e) {
        $failCount++;
    }
}

$sql = "select * from {question} where stamp=?";
$rs = $DB->get_recordset_sql($sql,array('192.168.1.106+101026203313+AnSOkP'));

foreach ($rs as $record) {
    // Lets fix these!
    $record->stamp = ctl_make_unique_id_code('move2moo.com');
    //$record->version = ctl_make_unique_id_code('move2moo.com');

    try {
        if($DB->update_record('question',$record)) {
            $count++;
        }
    } catch (dml_exception $e) {
        $failCount++;
    }
}

echo 'Done<br>';
echo 'Processing question categories:...';
// Retrieve Result Set of question category records that have stamp and fix them
$sql = "select * from {question_categories} where stamp=?";
$rs = $DB->get_recordset_sql($sql,array('192.168.1.106+101005034942+Oj5NrP'));

$cat_count = 0;
$failCatCount = 0;

foreach ($rs as $record) {
    // Lets fix these!
    $record->stamp = ctl_make_unique_id_code('move2moo.com');

    try {
        if($DB->update_record('question_categories',$record)) {
            $cat_count++;
        }
    } catch (dml_exception $e) {
        $failCatCount++;
    }
}
echo 'Done<br>';
echo 'Results:<br>';

echo '<p>Successful question updates: '.$count.'<br>';
echo 'Failed  question updates: '.$failCount.'</p>';

echo '<p>Successful question category updates: '.$cat_count.'<br>';
echo 'Failed question category updates: '.$failCatCount.'</p>';

echo '</html>';



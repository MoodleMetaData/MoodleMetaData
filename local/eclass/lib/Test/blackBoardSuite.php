<?php
/**
 * Created by IntelliJ IDEA.
 * User: tdjones
 * Date: 11-04-14
 * Time: 11:25 AM
 * To change this template use File | Settings | File Templates.
 *
 */
if (!defined('MOODLE_INTERNAL')) {
    define('MOODLE_INTERNAL', TRUE);
}
if (!defined("CLI_SCRIPT")) {
    define('CLI_SCRIPT', true);
}
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
include_once($CFG->dirroot . '/local/eclass/lib/Test/blackBoardTest.php');
//include_once('eclassCacheTest.php');


class BlackBoardSuite {
    public static function suite() {

        $suite = new PHPUnit_Framework_TestSuite('My Suite');
        $suite->addTestSuite('blackBoardTest');

        return $suite;
    }
}

?>
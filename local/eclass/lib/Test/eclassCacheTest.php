<?php
/**
 * Created by IntelliJ IDEA.
 * User: tdjones
 * Date: 11-04-13
 * Time: 5:16 PM
 * To change this template use File | Settings | File Templates.
 */

if (!defined('MOODLE_INTERNAL')) {
    define('MOODLE_INTERNAL', TRUE);
}
if (!defined("CLI_SCRIPT")) {
    define('CLI_SCRIPT', true);
}

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');

require_once($CFG->dirroot . "/local/eclass/lib/eclassCache.php");


?>
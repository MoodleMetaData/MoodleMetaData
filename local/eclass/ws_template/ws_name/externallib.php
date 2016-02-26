<?php

/**
 * External calendar API
 *
 * @package    moodlecore
 * @subpackage webservice
 * @copyright  2009 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * This file will define the web service class and external function definition.  For every function you define you
 * will need to have 3 functions.  One for the parameters, one for the function itself and the last on is for the return
 * values.
 *
 *
 */

require_once("$CFG->libdir/externallib.php");

/*
 * Naming convention for web services:
 *
 * eclass_nameofclass_external
 *
 * eclass and external must be present to conform to the convention used in moodle.
 *
 */
class eclass_template_external extends external_api {
    /**
     * This is the parameter function for the external function get_response
     */
    public static function get_response_parameters() {
        return new external_function_parameters(
            array(
                'start' => new external_value(PARAM_INT, 'Int value switch'),
                'end' => new external_value(PARAM_INT, 'Int value switch'),
            )
        );
    }

    /*
     * This is the function definition for the external function get_response
     */
    public static function get_response($start, $end) {
        global $CFG, $DB, $USER;
        $response = 'something';

        // This will validate the parameters
        $params = self::validate_parameters(self::get_response_parameters(), array('start'=>$start, 'end'=>$end));

        // DO SOMETHING

        return $response;
    }

    /*
     * This function defines the return values of the external function
     */
    public static function get_response_returns() {
        // This will return a string response (See wiki for howto define more complex return values).
        return new external_value(PARAM_TEXT, 'Response text');
    }
}
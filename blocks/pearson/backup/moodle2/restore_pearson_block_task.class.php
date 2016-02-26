<?php

/**
 * @package 
 * @subpackage 
 * @copyright 
 * @license   
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/blocks/pearson/backup/moodle2/restore_pearson_stepslib.php'); // We have structure steps

/**
 */
class restore_pearson_block_task extends restore_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(new restore_pearson_block_structure_step('pearson_structure', 'pearson.xml'));
    }

    public function get_fileareas() {
        return array(); // No associated fileareas
    }

    public function get_configdata_encoded_attributes() {
        return array(); // No special handling of configdata
    }

    static public function define_decode_contents() {
        return array();
    }

    static public function define_decode_rules() {
        return array();
    }
}

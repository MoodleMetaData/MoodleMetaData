<?php

/**
 * This file contains all the restore steps that will be used
 * by the restore_pearson_activity_task
 *
 * @package    
 * @subpackage 
 * @copyright 
 * @author     
 * @license    
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Structure step to restore one pearson activity
 */
class restore_pearson_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('pearson', '/activity/pearson');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_pearson($data) {
    }

    protected function after_execute() {
    }
}

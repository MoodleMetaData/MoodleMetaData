<?php

/**
 * @package 
 * @subpackage 
 * @copyright 
 * @license   
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Define all the backup steps that wll be used by the backup_pearson_block_task
 */

/**
 * Define the complete forum structure for backup, with file and id annotations
 */
class backup_pearson_block_structure_step extends backup_block_structure_step {

    protected function define_structure() {
        global $DB;

        // Get the block
        $block = $DB->get_record('block_instances', array('id' => $this->task->get_blockid()));
        // Extract configdata
        $config = unserialize(base64_decode($block->configdata));

        // Define each element separated

        $pearson = new backup_nested_element('pearson', array('id'), null);

        // Define sources

        $pearson->set_source_array(array((object)array('id' => $this->task->get_blockid())));

        // Annotations (none)

        // Return the root element (pearson), wrapped into standard block structure
        return $this->prepare_block_structure($pearson);
    }
}

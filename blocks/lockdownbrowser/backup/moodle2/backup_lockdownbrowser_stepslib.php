<?php

///////////////////////////////////////////////////////////////////////////////
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2013 Respondus, Inc.  All Rights Reserved.

class backup_lockdownbrowser_block_structure_step extends backup_block_structure_step {

    protected function define_structure() {
        
		$lockdownbrowser = new backup_nested_element(
		  "lockdownbrowser", array("id"), array(
		  "course", "name", "intro", "introformat", "timecreated", "timemodified"
		  ));

		$settings = new backup_nested_element(
		  "settings", array("id"), array(
		  "course", "quizid", "attempts", "reviews", "password", "monitor"
		  ));
		
        $lockdownbrowser->add_child($settings);

        $lockdownbrowser->set_source_table(
		  "block_lockdownbrowser", array("id" => backup::VAR_BLOCKID));
        $lockdownbrowser->set_source_table(
		  "block_lockdownbrowser_sett", array("course" => backup::VAR_COURSEID));

		$settings->annotate_ids("quiz", "quizid");

        return $this->prepare_block_structure($lockdownbrowser);
    }
}

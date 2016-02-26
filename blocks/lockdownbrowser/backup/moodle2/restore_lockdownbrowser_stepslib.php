<?php

///////////////////////////////////////////////////////////////////////////////
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2013 Respondus, Inc.  All Rights Reserved.

class restore_lockdownbrowser_block_structure_step extends restore_structure_step {

    protected function define_structure() {

        $paths = array();

        $paths[] = new restore_path_element("lockdownbrowser", "/block/lockdownbrowser");
        $paths[] = new restore_path_element("settings", "/block/lockdownbrowser/settings");

        return $paths;
    }

	public function process_lockdownbrowser($data) {

        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->course = $this->get_courseid();

//        $data->timecreated = $this->apply_date_offset($data->timecreated);
//        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record("block_lockdownbrowser", $data);
		
		$this->set_mapping("block_lockdownbrowser", $oldid, $newitemid);        
	}

	public function process_settings($data) {

        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record("block_lockdownbrowser_sett", $data);
		
		$this->set_mapping("block_lockdownbrowser_sett", $oldid, $newitemid);        
	}
}

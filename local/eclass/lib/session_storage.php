<?php

/*
 * Common Library for dealing with eclass specific information storage.
 *
 *
 *
 *
 */


defined('MOODLE_INTERNAL') || die;
require_once('eclassCache.php');

define("INSTANCES_DATAKEY", "instances");

/**
 * Lookup list of instances associated with this user
 * @param username value of username in moodle, currently this is ccid
 *
 * @return array list of instance records associated with user. array( stdClass, ...) stdClass->id, stdClass->url, stdClass->token, stdClass->sourceid
 */
function eclass_getUserInstances($username) {
    global $DB, $CFG;
    //using default cache name
    $eCache = new EclassCache();
    $data = $eCache->getData(INSTANCES_DATAKEY);
    if ($data != ECLASS_CACHE_EXPIRED) {
        return $data;
    }

    //otherwise do a query
    $dbman = $DB->get_manager();
    //check if our tables exist
    if(!$dbman->table_exists("eclass_instances") || !$dbman->table_exists("eclass_user_instances")){
        return array();
    }
    $records = $DB->get_records_sql("select i.* from {eclass_instances} AS i JOIN {eclass_user_instances} AS ui ON (i.id = ui.instance_id) where ui.userid = :userid", array('userid' => $username));
    $eCache->setData(INSTANCES_DATAKEY, 5, $records);
    $instances = Instance::wrapRecords($records);

    debugging("User Instances Found: " . print_r($records, true),DEBUG_DEVELOPER);

    return $instances;
}

class Instance {
    private $record;

    public function __construct($record) {
        $this->record = $record;
    }

    public function __get($attr) {
        global $CFG;
        return $this->record->$attr;
    }

    public function is_ws_instance() {
        return !isset($this->record->dbname) && !isset($this->record->dbprefix);
    }

    public function is_db_instance() {
        return !$this->is_ws_instance();
    }

    /**
     * @static
     * @param  array $a_records records to wrap, if null ignored
     * @return array array will always be instantiated
     */
    public static function wrapRecords($a_records) {
        $a_instances = array();
        foreach ($a_records as $key => $record) {
            $a_instances[$key] = new Instance($record);
        }
        return $a_instances;
    }
}


?>
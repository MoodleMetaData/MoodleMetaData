<?php
/**
 * Created by IntelliJ IDEA.
 * User: tdjones
 * Date: 11-04-14
 * Time: 10:48 AM
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

class eclassCacheEventTest extends PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function testCreateObjectFromValidRecord(){
        $record = $this->createValidEventRecord();
        $cache_ob = new eclassCacheEvent($record);
        $record = $cache_ob->getRecord();
        $this->assertTrue($cache_ob->validate_record(eclassCacheEvent::$properties,$record));
    }

    /**
     * @test
     */
    public function testCreateObjectFromInvalidRecord(){
        $record = $this->createInvalidEventRecord();
        try{
            $cache_ob = new eclassCacheEvent($record);
        }
        catch(Exception $expected){
            return;
        }
        $this->fail("Expected exception not raised.");


    }

    public function testCreateBlankCacheEvent(){
        $cache_ob = new eclassCacheEvent();
        $rec = $cache_ob->getRecord();
        $this->assertTrue(isset($rec));
        $this->assertEquals(count($rec), 1, print_r($cache_ob->getRecord(),true));
    }


    private static function createValidEventRecord() {
        $properties = array("id" => md5(rand() * 100),
                            "name" => "thename",
                            "description" => '<div class="no-overflow"><p>fewfewfewfew</p></div>',
                            "format" => 1,
                            "courseid" => 2,
                            "groupid" => 0,
                            "userid" => 1,
                            "repeatid" => 1,
                            "modulename" => "assignment",
                            "instance" => 1,
                            "eventtype" => "due",
                            "timestart" => 1303331700,
                            "timeduration" => 0,
                            "visible" => 1,
                            "uuid" => null,
                            "sequence" => 1,
                            "timemodified" => 1302726997,
                            "eclass_instanceid" => 1);
        $record = new StdClass();
        self::addPropertiesToRecord($record, $properties);
        return $record;

    }

    private static function createInvalidEventRecord() {
        $properties = array("id" => md5(rand() * 100),
                            "name" => null,
                            "description" => '<div class="no-overflow"><p>fewfewfewfew</p></div>',
                            "format" => 1,
                            "courseid" => 2,
                            "groupid" => 0,
                            "userid" => 1,
                            "repeatid" => 1,
                            "modulename" => "assignment",
                            "instance" => 1,
                            "eventtype" => "due",
                            "timestart" => null,
                            "timeduration" => 0,
                            "visible" => 1,
                            "uuid" => null,
                            "sequence" => 1,
                            "timemodified" => 1302726997,
                            "eclass_instanceid" => null);
        $record = new StdClass();
        self::addPropertiesToRecord($record, $properties);
        return $record;

    }

    private static function addPropertiesToRecord(&$record, $a_properties) {
        foreach ($a_properties as $property => $value) {
            $record->$property = $value;
        }
    }
}

?>
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

require_once($CFG->dirroot . "/local/eclass/lib/blackBoard.php");

class blackBoardTest extends PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function testgetMyBlackboardPage(){

        $bb = new BlackBoard('testuser');
        $a_html = $bb->getMyWebtCt();

        $this->assertNotEmpty($a_html);
    }

    /**
     * @test
     */
//    public function testCreateObjectFromInvalidRecord(){
//        $record = $this->createInvalidEventRecord();
//        try{
//            $cache_ob = new eclassCacheEvent($record);
//        }
//        catch(Exception $expected){
//            return;
//        }
//        $this->fail("Expected exception not raised.");
//
//
//    }
//
//    public function testCreateBlankCacheEvent(){
//        $cache_ob = new eclassCacheEvent();
//        $rec = $cache_ob->getRecord();
//        $this->assertTrue(isset($rec));
//        $this->assertEquals(count($rec), 1, print_r($cache_ob->getRecord(),true));
//    }



}

?>
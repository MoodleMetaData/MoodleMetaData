<?php
//define('CLI_SCRIPT', true);
define('MOODLE_INTERNAL', TRUE);
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
//require_once($CFG->libdir . '/clilib.php'); // cli only functions
require_once 'Zend/XmlRpc/Client.php';

$token = "f66a86d5d979164a66bac6545e08ca1a";
$url = "http://localhost/moodlectl/moodlecentral";

$serverurl = $CFG->centralroot . "/webservice/xmlrpc/server.php" . '?wstoken=' . $CFG->central_token;



$xmlrpcclient = new Zend_XmlRpc_Client($serverurl);

//make the web service call
$function = 'eclass_test_one';


$someObject = array(1,2,3,5,7);


$serializedObject = serialize($someObject);

$unserializedobject = unserialize($serializedObject);


var_dump($unserializedobject);

$CFG->logger->INFO($serializedObject);

$params = array('id' => 1,'key' => "key", 'data' => $serializedObject, 'username' => "ggibeau");

try {

    $value = $xmlrpcclient->call($function, $params);
    echo $value;
} catch (Exception $e) {
    var_dump($e);
}
<?php
/**
 * Created by IntelliJ IDEA.
 * User: ggibeau
 * Date: 11-03-24
 * Time: 10:46 AM
 * To change this template use File | Settings | File Templates.
 */
 
require_once ('../logger/Logger.php');

class MyApp {
   private $logger;

   public function __construct() {
        $this->logger = Logger::getLogger('WS');
        $this->logger->debug('Hello!');
        $this->logger->error('Uh oh');
   }

   public function doSomething() {
     $this->logger->info("Entering application.");
     $bar = new Bar();
     $bar->doIt();
     echo "<p>done</p>";
     $this->logger->info("Exiting application.");
   }
 }


// Set up a simple configuration that logs on the console.
Logger::configure('myconfiguration.properties');
echo "<p>start</p>";
$myapp = new MyApp();
$myapp->doSomething();
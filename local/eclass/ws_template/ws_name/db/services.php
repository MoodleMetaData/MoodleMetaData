<?php

 /**
  * This is the file that defines the locations of the externallib and allows moodle to
  * locate the new web service functions. You can also define capabilities for the services.
  *
  * Another option is the ability to defines default services to avoid having to manually create them
  * using the moodle gui.
  *
  *
  */

$functions = array(
    // === web service related functions ===

    'eclass_test_response' => array(
        'classname'   => 'eclass_template_external',
        'methodname'  => 'get_response',
        'classpath'   => 'local/eclass/externallib.php',
        'description' => 'Get calendar events.',
        'type'        => 'write',
        'capabilities'=> 'moodle/calendar:manageentries',
    ),
    // More functions will need more of the above
);

// The following is only needed if you wish to define the default service which uses the above functions.  You can also
// create this manually using the moodle web services gui.

$services = array(
    // === Service definition ===

    'servicename' => array(
        'functions' => array ('functionname'),
        'requiredcapability' => 'some/capability:specified',
        'restrictedusers' => 1,
        'enabled' => 0, // Used only when installing the services

    )
);
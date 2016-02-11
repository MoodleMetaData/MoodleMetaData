<?php
	$capabilities = array(	    
	    'local/xrplugin:add' => array(		
		'captype' => 'write',
		'contextlevel' => CONTEXT_SYSTEM,
		'archetypes' => array( // The roles that you want to allow
		    'teacher' => CAP_ALLOW,
		    'manager' => CAP_ALLOW
		),
	    ),
	);

?>

<?php

	// Add the univeristy administrator button
    if(is_siteadmin()) {
    	$ADMIN->add('root', new admin_externalpage('metadata_admin', get_string('admin_pluginname', 'local_metadata'),
    	$CFG->wwwroot.'/local/metadata/uniview_policy.php', 'local/metadata:admin_view'));
    }


?>

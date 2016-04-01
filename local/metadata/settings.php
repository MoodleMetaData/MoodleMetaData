<?php

    //$ADMIN->add('root', new admin_category('local_metadata', get_string('menuoption', 'local_metadata')));

    $ADMIN->add('cat', new admin_externalpage('metadata_manage', get_string('manage_pluginname', 'local_metadata'),
    $CFG->wwwroot.'/local/metadata/admview_knowledge.php', 'local/metadata:admin_view'));
    
    if(is_siteadmin()) {
    	$ADMIN->add('root', new admin_externalpage('metadata_admin', get_string('admin_pluginname', 'local_metadata'),
    	$CFG->wwwroot.'/local/metadata/uniview_policy.php', 'local/metadata:admin_view'));
    }


?>

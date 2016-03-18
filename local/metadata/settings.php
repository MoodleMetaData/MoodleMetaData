<?php

    //$ADMIN->add('root', new admin_category('local_metadata', get_string('menuoption', 'local_metadata')));

    $ADMIN->add('root', new admin_externalpage('metadata_manage', get_string('manage_pluginname', 'local_metadata'),
    $CFG->wwwroot.'/local/metadata/manage_psla_form.php', 'local/metadata:admin_view'));
    
    $ADMIN->add('root', new admin_externalpage('metadata_admin', get_string('admin_pluginname', 'local_metadata'),
    $CFG->wwwroot.'/local/metadata/universityview.php', 'local/metadata:admin_view'));


?>

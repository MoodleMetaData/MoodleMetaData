<?php

    $ADMIN->add('root', new admin_category('local_metadata', get_string('menuoption', 'local_metadata')));

    $ADMIN->add('local_metadata', new admin_externalpage('metadata', get_string('ins_pluginname', 'local_metadata'),
    $CFG->wwwroot.'/local/metadata/insview.php', 'local/metadata:ins_view'));

    $ADMIN->add('local_metadata', new admin_externalpage('metadata', get_string('manage_pluginname', 'local_metadata'),
    $CFG->wwwroot.'/local/metadata/manageview.php', 'local/metadata:admin_view'));


?>

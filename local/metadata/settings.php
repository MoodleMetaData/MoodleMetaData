<?php

    $ADMIN->add('root', new admin_category('local_metadata', get_string('menuoption', 'local_metadata')));

    $ADMIN->add('local_metadata', new admin_externalpage('metadata', get_string('pluginname', 'local_metadata'),
    $CFG->wwwroot.'/local/metadata/view.php', 'local/metadata:view'));

?>

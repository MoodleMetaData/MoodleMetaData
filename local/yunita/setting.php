<?php
    $ADMIN->add('root', new admin_category('local_yunita', get_string('menuoption', 'local_yunita')));
    $ADMIN->add('local_yunita', new admin_externalpage('yunita', get_string('pluginname', 'local_yunita'),
    $CFG->wwwroot.'/local/yunita/sample.php', 'local/yunita:add'));
?>
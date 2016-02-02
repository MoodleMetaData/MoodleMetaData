<?php
	$ADMIN->add('root', new admin_category('local_myplugin', get_string('menuoption', 'local_myplugin')));
	$ADMIN->add('local_myplugin', new admin_externalpage('myplugin', get_string('pluginname', 'local_myplugin'),
    $CFG->wwwroot.'/local/myplugin/view.php', 'local/myplugin:add'));
?>
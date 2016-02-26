<?php
define("MOODLE_INTERNAL", TRUE);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot .'/lib/modinfolib.php');
//Require Login to get course list

require_login();
require_capability('moodle/site:config', context_system::instance());

if(isset($_GET['courseid'])) {
    rebuild_course_cache($courseid=$_GET['courseid']);
    $body = <<<BODY
    The mod_info cache for course with id:${$_GET['courseid']} has been rebuilt
BODY;

    template($body);

}
else {
    $form = <<<FORM
<h2>Rebuild the mod_info course cache:</h2>
<form>
    <input type='text' name='courseid' />
    <input type='submit'>
</form>
FORM;
    template($form);
}

function template($body,$head = '') {
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Clear ModInfo</title>
        <?php echo $head ?>
    </head>
    <body>
        <?php echo $body ?>
    </body>
</html>


<?php
}





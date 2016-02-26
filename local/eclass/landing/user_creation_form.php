<?php
/**
 * Created by IntelliJ IDEA.
 * User: tdjones
 * Date: 11-05-11
 * Time: 9:15 AM
 * To change this template use File | Settings | File Templates.
 */


define("MOODLE_INTERNAL", TRUE);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
//Require Login to get course list
try {
    require_login(NULL, false, NULL, false, false);
        $context = get_context_instance(CONTEXT_SYSTEM);
    require_capability('moodle/user:create', $context);
}
    //if an exception is thrown then user was not logged in
catch (Exception $e) {
    echo "Not Authorized";
    return;
}
?>
<html>
<head>
    <title>Create Users</title>
</head>
<body>
<h1>Upload Users vis CSV</h1>
<form method='post' enctype="multipart/form-data" action="user_creation.php">
    <input type="file" name="users">
    <input type="submit">
</form>
</body>
</html>
<?php

///////////////////////////////////////////////////////////////////////////////
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2013 Respondus, Inc.  All Rights Reserved.

// production flags
// - should all default to FALSE
// - set to TRUE only for exceptional environments
$LOCKDOWNBROWSER_IGNORE_HTTPS_LOGIN = FALSE; // set TRUE to ignore $CFG->loginhttps

// debug-only flags
// - should always be FALSE for production environments
$LOCKDOWNBROWSER_DISABLE_CALLBACKS = FALSE; // set TRUE to skip login security callbacks
$LOCKDOWNBROWSER_MONITOR_ENABLE_LOG = FALSE; // set TRUE to enable logging to temp file

// local options
define ("LOCKDOWNBROWSER_MONITOR_REDEEMURL",
  "https://smc-service-cloud.respondus2.com/MONServer/lms/redeemtoken.do");
define ("LOCKDOWNBROWSER_MONITOR_LOG", "ldb_monitor.log");

// Moodle options
define("NO_DEBUG_DISPLAY", true);

$lockdownbrowser_moodlecfg_file =
  dirname(dirname(dirname(__FILE__))) . "/config.php";
if (is_readable($lockdownbrowser_moodlecfg_file))
    require_once($lockdownbrowser_moodlecfg_file);
else
	lockdownbrowser_MonitorServiceError(2001, "Moodle config.php not found");

$lockdownbrowser_gradelib_file = "$CFG->libdir/gradelib.php";
if (is_readable($lockdownbrowser_gradelib_file))
    require_once($lockdownbrowser_gradelib_file);
else
	lockdownbrowser_MonitorServiceError(2030, "Moodle gradelib.php not found");

$lockdownbrowser_locklib_file =
  "$CFG->dirroot/blocks/lockdownbrowser/locklib.php";
if (is_readable($lockdownbrowser_locklib_file))
    require_once($lockdownbrowser_locklib_file);
else
	lockdownbrowser_MonitorServiceError(2033, "locklib.php not found");

if (!empty($CFG->maintenance_enabled)
  || file_exists($CFG->dataroot . "/" . SITEID . "/maintenance.html")) {
	lockdownbrowser_MonitorServiceError(2002, "The Moodle site is currently undergoing maintenance");
}

raise_memory_limit(MEMORY_EXTRA);

set_exception_handler("lockdownbrowser_MonitorExceptionHandler");

lockdownbrowser_MonitorServiceRequest();

exit;

function lockdownbrowser_MonitorServiceError($code = "", $message = "")
{
	if (empty($code)) {
		$code = "2000";
		$message = "Unspecified error";
	}

	$body  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	$body .= "<service_error>\r\n";

	$body .= "\t<code>";
	$body .= utf8_encode(htmlspecialchars(trim($code)));
	$body .= "</code>\r\n";

	if (empty($message)) {
		$body .= "\t<message />\r\n";
	}
	else {
		$body .= "\t<message>";
		$body .= utf8_encode(htmlspecialchars(trim($message)));
		$body .= "</message>\r\n";
	}

	$body .= "</service_error>\r\n";

	lockdownbrowser_MonitorServiceResponse("text/xml", $body, TRUE);
}

function lockdownbrowser_MonitorServiceStatus($code = "", $message = "")
{
	if (empty($code)) {
		$code = "1000";
		$message = "Unspecified status";
	}

	$body  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	$body .= "<service_status>\r\n";

	$body .= "\t<code>";
	$body .= utf8_encode(htmlspecialchars(trim($code)));
	$body .= "</code>\r\n";

	if (empty($message)) {
		$body .= "\t<message />\r\n";
	}
	else {
		$body .= "\t<message>";
		$body .= utf8_encode(htmlspecialchars(trim($message)));
		$body .= "</message>\r\n";
	}

	$body .= "</service_status>\r\n";

	lockdownbrowser_MonitorServiceResponse("text/xml", $body, TRUE);
}

function lockdownbrowser_MonitorServiceResponse($content_type, $body, $encrypt)
{
	lockdownbrowser_MonitorLog("service response: " . $body);

	header("Cache-Control: private, must-revalidate");
	header("Expires: -1");
	header("Pragma: no-cache");

	if ($encrypt === TRUE) {
		$encrypted = lockdownbrowser_MonitorBase64Encrypt($body, TRUE);
		if (is_null($encrypted)) {
			header("Content-Type: $content_type");
			echo $body;
		}
		else {
			header("Content-Type: text/html"); // needed for IE client
			$url_encoded = urlencode($encrypted);
			echo $url_encoded;
		}
	}
	else {
		header("Content-Type: $content_type");
		echo $body;
	}

	exit;
}

function lockdownbrowser_MonitorCourseListResponse($courses)
{
	$body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";

	if (empty($courses)) {
		$body .= "<courseList />\r\n";
		lockdownbrowser_MonitorServiceResponse("text/xml", $body, TRUE);
	}

	$body .= "<courseList>\r\n";

	foreach ($courses as $c) {
		$body .= "\t<course>\r\n";

		$body .= "\t\t<courseRefId>";
		$body .= utf8_encode(htmlspecialchars(trim($c->id)));
		$body .= "</courseRefId>\r\n";

		$body .= "\t\t<courseId>";
		$body .= utf8_encode(htmlspecialchars(trim($c->shortname)));
		$body .= "</courseId>\r\n";

		$body .= "\t\t<courseDescription>";
		$body .= utf8_encode(htmlspecialchars(trim($c->fullname)));
		$body .= "</courseDescription>\r\n";

		$body .= "\t</course>\r\n";
	}

	$body .= "</courseList>\r\n";

	lockdownbrowser_MonitorServiceResponse("text/xml", $body, TRUE);
}

function lockdownbrowser_MonitorFloatCompare($f1, $f2, $precision)
{
	if (function_exists("bccomp")) {
		return bccomp($f1, $f2, $precision);
	}

	if ($precision < 0)
		$precision = 0;

	$epsilon = 1 / pow(10, $precision);
	$diff = ($f1 - $f2);

	if (abs($diff) < $epsilon)
		return 0;
	else if ($diff < 0)
		return -1;
	else
		return 1;
}

function lockdownbrowser_MonitorTempPath()
{
	global $CFG;

	if (lockdownbrowser_MonitorFloatCompare(
	  $CFG->version, 2011120500.00, 2) >= 0) {
		// Moodle 2.2.0+
		if (isset($CFG->tempdir))
			$path = "$CFG->tempdir";
		else
			$path = "$CFG->dataroot/temp";
	}
	else {
		// Moodle 2.0.x - 2.1.x
		$path = "$CFG->dataroot/temp";
	}

	return $path;
}

function lockdownbrowser_MonitorLog($msg)
{
	global $LOCKDOWNBROWSER_MONITOR_ENABLE_LOG;

	if ($LOCKDOWNBROWSER_MONITOR_ENABLE_LOG) {
		$entry = date("m-d-Y H:i:s") . " - " . $msg . "\r\n";
		$path = lockdownbrowser_MonitorTempPath()
		  . "/" . LOCKDOWNBROWSER_MONITOR_LOG;
		$handle = fopen($path, "ab");
		if ($handle !== FALSE) {
			fwrite($handle, $entry, strlen($entry));
			fclose($handle);
		}
	}
}

function lockdownbrowser_MonitorExceptionHandler($ex)
{
	abort_all_db_transactions();

	$info = get_exception_info($ex);

	$msg = "\r\n-- Exception occurred --"
	  . "\r\nmessage: $info->message"
	  . "\r\nerrorcode: $info->errorcode"
	  . "\r\nbacktrace: $info->backtrace"
	  . "\r\nlink: $info->link"
	  . "\r\nmoreinfourl: $info->moreinfourl"
	  . "\r\na: $info->a"
	  . "\r\ndebuginfo: $info->debuginfo\r\n";

	lockdownbrowser_MonitorLog($msg);
	lockdownbrowser_MonitorLog("\r\nstacktrace: ".$ex->getTraceAsString());

	lockdownbrowser_MonitorServiceError(2003, "A Moodle or PHP server exception occurred: $info->errorcode");
}

function lockdownbrowser_MonitorRequestParameters()
{
	$parameters = array();
	$request_method = $_SERVER["REQUEST_METHOD"];

	if ($request_method == "GET") {

		if (!isset($_GET["rp"])) { // direct access only for existence check
			lockdownbrowser_MonitorServiceError(2012, "No request parameters found");
		}

		$cleaned = optional_param("rp", FALSE, PARAM_ALPHANUMEXT);
		if ($cleaned == "ping") { // unencrypted presence check
			lockdownbrowser_MonitorServiceResponse("text/plain", "OK", FALSE);
		}

		$cleaned = optional_param("rp", FALSE, PARAM_NOTAGS); // cannot use PARAM_BASE64
		if ($cleaned === FALSE) {
			lockdownbrowser_MonitorServiceError(2012, "No request parameters found");
		}
	}
	else if ($request_method == "POST") {

		if (isset($_POST["rp"])) { // direct access only for existence check

			$cleaned = optional_param("rp", FALSE, PARAM_ALPHANUMEXT);
			if ($cleaned == "ping") { // unencrypted presence check
				lockdownbrowser_MonitorServiceResponse("text/plain", "OK", FALSE);
			}

			$cleaned = optional_param("rp", FALSE, PARAM_NOTAGS); // cannot use PARAM_BASE64
			if ($cleaned === FALSE) {
				lockdownbrowser_MonitorServiceError(2012, "No request parameters found");
			}
		}
		else { // direct access only for length check and url-decoding

			$body = file_get_contents("php://input");
			if (strlen($body) == 0) {
				lockdownbrowser_MonitorServiceError(2012, "No request parameters found");
			}

			$decoded = urldecode($body);

			$cleaned = clean_param($decoded, FALSE, PARAM_ALPHANUMEXT);
			if ($cleaned == "ping") { // unencrypted presence check
				lockdownbrowser_MonitorServiceResponse("text/plain", "OK", FALSE);
			}

			$cleaned = clean_param($decoded, PARAM_NOTAGS); // cannot use PARAM_BASE64
			if ($cleaned === FALSE) {
				lockdownbrowser_MonitorServiceError(2012, "No request parameters found");
			}
		}
	}
	else {
		lockdownbrowser_MonitorServiceError(2017, "Unsupported request method: $request_method");
	}

	// parse encrypted parameters
	$decrypted = lockdownbrowser_MonitorBase64Decrypt($cleaned, FALSE);
	lockdownbrowser_MonitorLog("service request: " . $decrypted);
	$nvPairs = explode("&", $decrypted);
	foreach ($nvPairs as $pair) {
		$parts = explode("=", $pair);
		$name = urldecode($parts[0]);
		if (count($parts) == 2)
			$value = urldecode($parts[1]);
		else
			$value = "";
		$parameters[$name] = $value;
	}

	// check mac
	$pos = strpos($decrypted, "&mac=");
	if ($pos === FALSE
	  || !isset($parameters["mac"])
	  || strlen($parameters["mac"]) == 0) {
		lockdownbrowser_MonitorServiceError(2011, "MAC not found in request");
	}
	$sign = substr($decrypted, 0, $pos);
	$mac = lockdownbrowser_MonitorGenerateMac($sign);
	if (strcmp($mac, $parameters["mac"]) != 0) {
		lockdownbrowser_MonitorServiceError(2010, "Invalid MAC in request");
	}

	return $parameters;
}

function lockdownbrowser_MonitorGenerateMac($input)
{
	$secret = lockdownbrowser_MonitorSharedSecret(FALSE);

	$charArray = preg_split('//', $input, -1, PREG_SPLIT_NO_EMPTY);

	$strDataValue = 0;
	foreach ($charArray as $char) {
		$strDataValue += ord($char);
	}

	return md5($strDataValue . $secret);
}

function lockdownbrowser_MonitorBase64Encrypt($input, $silent)
{
	if (!extension_loaded("mcrypt")) {
		if ($silent === FALSE)
			lockdownbrowser_MonitorServiceError(2008, "The mcrypt library is not loaded");
		else
			return null;
	}

	$secret = lockdownbrowser_MonitorSharedSecret($silent);
	if (is_null($secret)) {
		return null;
	}

	$encrypted = mcrypt_encrypt(MCRYPT_BLOWFISH, $secret, $input, MCRYPT_MODE_ECB);
	$b64_encoded = base64_encode($encrypted);

	return $b64_encoded;
}

function lockdownbrowser_MonitorBase64Decrypt($input, $silent)
{
	$b64_decoded = base64_decode($input, TRUE);

	if ($b64_decoded === FALSE) {
		if ($silent === FALSE)
			lockdownbrowser_MonitorServiceError(2007, "Invalid base64 encoding of input data");
		else
			return null;
	}
	if (!extension_loaded("mcrypt")) {
		if ($silent === FALSE)
			lockdownbrowser_MonitorServiceError(2008, "The mcrypt library is not loaded");
		else
			return null;
	}

	$secret = lockdownbrowser_MonitorSharedSecret($silent);
	if (is_null($secret)) {
		return null;
	}

	$decrypted = mcrypt_decrypt(MCRYPT_BLOWFISH, $secret, $b64_decoded, MCRYPT_MODE_ECB);
	return trim($decrypted);
}

function lockdownbrowser_MonitorSharedSecret($silent)
{
	global $CFG;

	if (!isset($CFG->block_lockdownbrowser_LDB_SERVERSECRET)
	  || strlen($CFG->block_lockdownbrowser_LDB_SERVERSECRET) == 0
	  ) {
		if ($silent === FALSE)
			lockdownbrowser_MonitorServiceError(2009, "Shared secret not found in settings");
		else
			return null;
	}

	$secret = $CFG->block_lockdownbrowser_LDB_SERVERSECRET;

	return $secret;
}

function lockdownbrowser_MonitorRedeemToken($parameters)
{
	global $CFG;

	if (!isset($parameters["token"]) || strlen($parameters["token"]) == 0) {
		lockdownbrowser_MonitorServiceError(2018, "Login token not found in request");
	}
	$token = $parameters["token"];

	if (!isset($CFG->block_lockdownbrowser_LDB_SERVERID)
	  || strlen($CFG->block_lockdownbrowser_LDB_SERVERID) == 0
	  ) {
		lockdownbrowser_MonitorServiceError(2019, "Institution ID not found in settings");
	}
	$institution_id = $CFG->block_lockdownbrowser_LDB_SERVERID;

	if (!isset($CFG->block_lockdownbrowser_LDB_SERVERNAME)
	  || strlen($CFG->block_lockdownbrowser_LDB_SERVERNAME) == 0
	  ) {
		lockdownbrowser_MonitorServiceError(2037, "Server name not found in settings");
	}
	$server_name = $CFG->block_lockdownbrowser_LDB_SERVERNAME;

	$redeem_time = time();
	$redeem_mac = lockdownbrowser_MonitorGenerateMac(
	  urldecode($institution_id) . urldecode($server_name) . $token . $redeem_time
	  );

	// we assume https, so no additional encryption is used

	$url = LOCKDOWNBROWSER_MONITOR_REDEEMURL
	  . "?institutionId=" . $institution_id // assume url-encoded
	  . "&serverName=" . $server_name // assume url-encoded
	  . "&token=" . urlencode($token)
	  . "&time=" . urlencode($redeem_time)
	  . "&mac=" . urlencode($redeem_mac);

	if (!extension_loaded("curl")) {
		lockdownbrowser_MonitorServiceError(2020, "The curl library is not loaded");
	}

	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE );
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($result == false || ($info["http_code"] != 200 )) {
		lockdownbrowser_MonitorServiceError(2021, "Could not redeem login token");
    }

	$receipt_mac = lockdownbrowser_MonitorGenerateMac(
	  $token . urldecode($server_name) . urldecode($institution_id) . $redeem_time
	  );
    if ($result != $receipt_mac) {
		lockdownbrowser_MonitorServiceError(2022, "Received invalid token receipt");
	}
}

function lockdownbrowser_MonitorActionLogin($parameters)
{
	global $CFG;
	global $LOCKDOWNBROWSER_IGNORE_HTTPS_LOGIN;
	global $LOCKDOWNBROWSER_DISABLE_CALLBACKS;

	if (isloggedin()) {
		lockdownbrowser_MonitorServiceError(2015, "Session is already logged in");
	}

	if (!$LOCKDOWNBROWSER_DISABLE_CALLBACKS) {
		lockdownbrowser_MonitorRedeemToken($parameters);
	}

	if (!$LOCKDOWNBROWSER_IGNORE_HTTPS_LOGIN) {
		if ($CFG->loginhttps && !$CFG->sslproxy) {
			if (!isset($_SERVER["HTTPS"])
			  || empty($_SERVER["HTTPS"])
			  || strcasecmp($_SERVER["HTTPS"], "off") == 0) {
				lockdownbrowser_MonitorServiceError(2016, "HTTPS is required");
			}
		}
	}

	if (!isset($CFG->block_lockdownbrowser_MONITOR_USERNAME)
	  || strlen($CFG->block_lockdownbrowser_MONITOR_USERNAME) == 0
	  || !isset($CFG->block_lockdownbrowser_MONITOR_PASSWORD)
	  || strlen($CFG->block_lockdownbrowser_MONITOR_PASSWORD) == 0
	  ) {
		lockdownbrowser_MonitorServiceError(2014, "Login info not found in settings");
	}

	$user = authenticate_user_login(
	  $CFG->block_lockdownbrowser_MONITOR_USERNAME,
	  $CFG->block_lockdownbrowser_MONITOR_PASSWORD
	  );
	if ($user)
		complete_user_login($user);

	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2013, "Login attempt failed");
	}

	lockdownbrowser_MonitorServiceStatus(1002, "Login succeeded");
}

function lockdownbrowser_MonitorActionUserLogin($parameters)
{
	global $CFG;
	global $LOCKDOWNBROWSER_IGNORE_HTTPS_LOGIN;

	if (isloggedin()) {
		lockdownbrowser_MonitorServiceError(2015, "Session is already logged in");
	}
	if (!isset($parameters["username"]) || strlen($parameters["username"]) == 0) {
		lockdownbrowser_MonitorServiceError(2031, "No username was specified");
	}
	if (!isset($parameters["password"]) || strlen($parameters["password"]) == 0) {
		lockdownbrowser_MonitorServiceError(2032, "No password was specified");
	}

	$username = $parameters["username"];
	$password = $parameters["password"];

	if (!$LOCKDOWNBROWSER_IGNORE_HTTPS_LOGIN) {
		if ($CFG->loginhttps && !$CFG->sslproxy) {
			if (!isset($_SERVER["HTTPS"])
			  || empty($_SERVER["HTTPS"])
			  || strcasecmp($_SERVER["HTTPS"], "off") == 0) {
				lockdownbrowser_MonitorServiceError(2016, "HTTPS is required");
			}
		}
	}

	$user = authenticate_user_login($username, $password);
	if ($user)
		complete_user_login($user);

	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2013, "Login attempt failed");
	}

	lockdownbrowser_MonitorServiceStatus(1002, "Login succeeded");
}

function lockdownbrowser_MonitorActionLogout($parameters)
{
	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2004, "Must be logged in to perform the requested action");
	}

	require_logout();

	lockdownbrowser_MonitorServiceStatus(1001, "Logout succeeded");
}

function lockdownbrowser_MonitorActionCourseList($parameters)
{
	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2004, "Must be logged in to perform the requested action");
	}
	if (!is_siteadmin()) {
		lockdownbrowser_MonitorServiceError(2024, "Must be logged in as admin to perform the requested action");
	}

	$courses = get_courses();
	if ($courses === FALSE)
		$courses = array();

    if (array_key_exists(SITEID, $courses))
        unset($courses[SITEID]);

	lockdownbrowser_MonitorCourseListResponse($courses);
}

function lockdownbrowser_MonitorActionChangeSettings($parameters)
{
	global $DB;

	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2004, "Must be logged in to perform the requested action");
	}
	if (!is_siteadmin()) {
		lockdownbrowser_MonitorServiceError(2024, "Must be logged in as admin to perform the requested action");
	}
	if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
		lockdownbrowser_MonitorServiceError(2025, "No courseRefId parameter was specified");
	}
	if (!isset($parameters["examId"]) || strlen($parameters["examId"]) == 0) {
		lockdownbrowser_MonitorServiceError(2026, "No examId parameter was specified");
	}
	if (!isset($parameters["enableLDB"]) || strlen($parameters["enableLDB"]) == 0) {
		lockdownbrowser_MonitorServiceError(2040, "No enableLDB parameter was specified");
	}
	if (!isset($parameters["enableMonitor"]) || strlen($parameters["enableMonitor"]) == 0) {
		lockdownbrowser_MonitorServiceError(2041, "No enableMonitor parameter was specified");
	}
	if (!isset($parameters["exitPassword"])) {
		lockdownbrowser_MonitorServiceError(2042, "No exitPassword parameter was specified");
	}
	if (!isset($parameters["xdata"])) {
		lockdownbrowser_MonitorServiceError(2043, "No xdata parameter was specified");
	}

	$course_id = intval($parameters["courseRefId"]);
	$exam_id = intval($parameters["examId"]);

	$enable_ldb = $parameters["enableLDB"];
	if ($enable_ldb == "0" || strcasecmp($enable_ldb, "false") == 0)
		$enable_ldb = FALSE;
	else
		$enable_ldb = TRUE;

	$enable_monitor = $parameters["enableMonitor"];
	if ($enable_monitor == "0" || strcasecmp($enable_monitor, "false") == 0)
		$enable_monitor = FALSE;
	else
		$enable_monitor = TRUE;

	$exit_password = $parameters["exitPassword"];
	$xdata = $parameters["xdata"];

	if ($enable_monitor)
		$monitor = $xdata;
	else
		$monitor = "";

	$course_module = $DB->get_record("course_modules", array("id" => $exam_id));
	if ($course_module === FALSE) {
		lockdownbrowser_MonitorServiceError(2027, "The specified examId is invalid: $exam_id");
	}

	$modrec = $DB->get_record("modules", array("id" => $course_module->module));
	if ($modrec === FALSE) {
		lockdownbrowser_MonitorServiceError(2034, "Could not find the specified quiz (module error)");
	}

	$quiz = $DB->get_record($modrec->name, array("id" => $course_module->instance));
	if ($quiz === FALSE) {
		lockdownbrowser_MonitorServiceError(2035, "Could not find the specified quiz (instance error)");
	}

	// Moodle browser security
	//   popup (0=none, 1=full screen pop-up with some JavaScript security)
	// Moodle 2.2.0+ (quiz module 2011100600+)
	//   browsersecurity ('-', 'securewindow', 'safebrowser')
	// if this setting is not disabled, it will interfere with the LDB integration
	if ($enable_ldb) {
		$quiz->popup = 0;
		$quiz->browsersecurity = "-";
	}

	$ldb_decoration = get_string("requires_ldb", "block_lockdownbrowser");
	$monitor_decoration = get_string("requires_webcam", "block_lockdownbrowser");

	// must be in this order, since the first decoration usually contains the second
	$quiz->name = str_replace($monitor_decoration, "", $quiz->name);
	$quiz->name = str_replace($ldb_decoration, "", $quiz->name);

	if ($enable_ldb) {
		if ($enable_monitor)
			$quiz->name .= $monitor_decoration;
		else
			$quiz->name .= $ldb_decoration;
	}

	$settings = lockdownbrowser_get_quiz_options($quiz->id);

	if ($settings === FALSE) {

		if ($enable_ldb) {
			$ok = lockdownbrowser_set_settings($quiz->id, 0, 0, $exit_password, $monitor);
			if (!$ok) {
				lockdownbrowser_MonitorServiceError(2036, "Quiz settings changes failed (block error)");
			}
		}
	}
	else { // settings found

		if ($enable_ldb) {
			$settings->password = $exit_password;
			$settings->monitor = $monitor;
			$ok = lockdownbrowser_set_quiz_options($quiz->id, $settings);
			if (!$ok) {
				lockdownbrowser_MonitorServiceError(2036, "Quiz settings changes failed (block error)");
			}
		}
		else {
			lockdownbrowser_delete_options($quiz->id);
		}
	}

	$ok = $DB->update_record($modrec->name, $quiz);
	if (!$ok) {
		lockdownbrowser_MonitorServiceError(2036, "Quiz settings changes failed (module error)");
	}

	rebuild_course_cache($course_id);
	lockdownbrowser_MonitorServiceStatus(1003, "Quiz settings changes succeeded");
}

function lockdownbrowser_MonitorActionExamRoster($parameters)
{
	global $DB;

	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2004, "Must be logged in to perform the requested action");
	}
	if (!is_siteadmin()) {
		lockdownbrowser_MonitorServiceError(2024, "Must be logged in as admin to perform the requested action");
	}
	if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
		lockdownbrowser_MonitorServiceError(2025, "No courseRefId parameter was specified");
	}
	if (!isset($parameters["examId"]) || strlen($parameters["examId"]) == 0) {
		lockdownbrowser_MonitorServiceError(2026, "No examId parameter was specified");
	}

	$course_id = intval($parameters["courseRefId"]);
	$exam_id = intval($parameters["examId"]);

	$course_module = $DB->get_record("course_modules", array("id" => $exam_id));
	if ($course_module === FALSE) {
		lockdownbrowser_MonitorServiceError(2027, "The specified examId is invalid: $exam_id");
	}
	$quiz_id = $course_module->instance;

	$context = context_course::instance($course_id);
	if ($context === FALSE) {
		lockdownbrowser_MonitorServiceError(2028, "The specified courseRefId is invalid: $course_id");
	}

	$roles = $DB->get_records("role", array("archetype" => "student"));
	if ($roles === FALSE || count($roles) == 0) {
		lockdownbrowser_MonitorServiceError(2029, "The role archetype 'student' was not found");
	}

	$students = array();
	foreach ($roles as $role) {
		$users = get_role_users($role->id, $context);
		if ($users !== FALSE && count($users) > 0)
			$students = array_merge($students, $users);
	}

	$body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";

	if ($students === FALSE || count($students) == 0) {
		$body .= "<studentList />\r\n";
		lockdownbrowser_MonitorServiceResponse("text/xml", $body, TRUE);
	}

	$body .= "<studentList>\r\n";

	foreach ($students as $s) {
		$body .= "\t<student>\r\n";

		$body .= "\t\t<userName>";
		$body .= utf8_encode(htmlspecialchars(trim($s->username)));
		$body .= "</userName>\r\n";

		$body .= "\t\t<firstName>";
		$body .= utf8_encode(htmlspecialchars(trim($s->firstname)));
		$body .= "</firstName>\r\n";

		$body .= "\t\t<lastName>";
		$body .= utf8_encode(htmlspecialchars(trim($s->lastname)));
		$body .= "</lastName>\r\n";

		$grade_info = grade_get_grades(
		  $course_id, "mod", "quiz", $quiz_id, $s->id
		  );
		if (!empty($grade_info)
		  && !empty($grade_info->items)
		  && !empty($grade_info->items[0]->grades)
		  && !empty($grade_info->items[0]->grades[$s->id])
		  && !empty($grade_info->items[0]->grades[$s->id]->grade)
		  ) {
			$grade = $grade_info->items[0]->grades[$s->id]->str_grade;
			$body .= "\t\t<grade>";
			$body .= utf8_encode(htmlspecialchars(trim($grade)));
			$body .= "</grade>\r\n";
		}

		$body .= "\t</student>\r\n";
	}

	$body .= "</studentList>\r\n";

	lockdownbrowser_MonitorServiceResponse("text/xml", $body, TRUE);
}

function lockdownbrowser_MonitorActionUserInfo2($parameters)
{
	global $USER;

	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2004, "Must be logged in to perform the requested action");
	}

	$body = $USER->username . "\$%\$"
	  . $USER->lastname . "\$%\$"
	  . $USER->firstname;

	lockdownbrowser_MonitorServiceResponse("text/plain", $body, TRUE);
}

function lockdownbrowser_MonitorActionUserCourseList($parameters)
{
	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2004, "Must be logged in to perform the requested action");
	}

	$courses = enrol_get_my_courses();
	if ($courses === FALSE)
		$courses = array();

    if (array_key_exists(SITEID, $courses))
        unset($courses[SITEID]);

	lockdownbrowser_MonitorCourseListResponse($courses);
}

function lockdownbrowser_MonitorActionExamInfo2($parameters)
{
	global $DB;

	// login not required

	if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
		lockdownbrowser_MonitorServiceError(2025, "No courseRefId parameter was specified");
	}
	if (!isset($parameters["examId"]) || strlen($parameters["examId"]) == 0) {
		lockdownbrowser_MonitorServiceError(2026, "No examId parameter was specified");
	}

	$course_id = intval($parameters["courseRefId"]);
	$exam_id = intval($parameters["examId"]);

	$course_module = $DB->get_record("course_modules", array("id" => $exam_id));
	if ($course_module === FALSE) {
		lockdownbrowser_MonitorServiceError(2027, "The specified examId is invalid: $exam_id");
	}

	$modrec = $DB->get_record("modules", array("id" => $course_module->module));
	if ($modrec === FALSE) {
		lockdownbrowser_MonitorServiceError(2034, "Could not find the specified quiz (module error)");
	}

	$quiz = $DB->get_record($modrec->name, array("id" => $course_module->instance));
	if ($quiz === FALSE) {
		lockdownbrowser_MonitorServiceError(2035, "Could not find the specified quiz (instance error)");
	}

	$settings = lockdownbrowser_get_quiz_options($quiz->id);

	if ($settings === FALSE
	  || !isset($settings->password)
	  || is_null($settings->password)
	  || strlen($settings->password) == 0
	  ) {
		$exit_pass_exists = "N";
		$exit_password = "";
	}
	else {
		$exit_pass_exists = "Y";
		$exit_password = $settings->password;
	}

	$body = "NONE\$:\$N\$:\$"
	  . $exit_pass_exists
	  . "\$:\$"
	  . $exit_password
	  . "\$:\$N\$:\$\$:\$"
	  . $quiz->name;

	lockdownbrowser_MonitorServiceResponse("text/plain", $body, TRUE);
}

function lockdownbrowser_MonitorActionExamSync($parameters)
{
	global $DB;

	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2004, "Must be logged in to perform the requested action");
	}
	if (!is_siteadmin()) {
		lockdownbrowser_MonitorServiceError(2024, "Must be logged in as admin to perform the requested action");
	}
	if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
		lockdownbrowser_MonitorServiceError(2025, "No courseRefId parameter was specified");
	}

	$course_id = intval($parameters["courseRefId"]);

	$coursemodules = get_coursemodules_in_course("quiz", $course_id);
	if ($coursemodules === FALSE)
		$coursemodules = array();

	$body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";

	if (empty($coursemodules)) {
		$body .= "<assessmentList />\r\n";
		lockdownbrowser_MonitorServiceResponse("text/xml", $body, TRUE);
	}

	$body .= "<assessmentList>\r\n";

	foreach ($coursemodules as $cm) {

		$modrec = $DB->get_record("modules", array("id" => $cm->module));
		if ($modrec === FALSE)
			continue;

		$quiz = $DB->get_record($modrec->name, array("id" => $cm->instance));
		if ($quiz === FALSE)
			continue;

		$body .= "\t<assessment>\r\n";

		$body .= "\t\t<id>";
		$body .= utf8_encode(htmlspecialchars(trim($cm->id)));
		$body .= "</id>\r\n";

		$body .= "\t\t<title>";
		$body .= utf8_encode(htmlspecialchars(trim($cm->name)));
		$body .= "</title>\r\n";

		$settings = lockdownbrowser_get_quiz_options($cm->instance);

		if ($settings !== FALSE)
			$body .= "\t\t<ldbEnabled>true</ldbEnabled>\r\n";
		else
			$body .= "\t\t<ldbEnabled>false</ldbEnabled>\r\n";

		if ($settings !== FALSE
		  && isset($settings->password)
		  && !is_null($settings->password)
		  && strlen($settings->password) > 0
		  ) {
			$body .= "\t\t<exitPassword>";
			$body .= utf8_encode(htmlspecialchars($settings->password));
			$body .= "</exitPassword>\r\n";
		}

		if ($settings !== FALSE
		  && isset($settings->monitor)
		  && !is_null($settings->monitor)
		  && strlen($settings->monitor) > 0
		  ) {
			$body .= "\t\t<monitorEnabled>true</monitorEnabled>\r\n";
			$body .= "\t\t<extendedData>";
			$body .= utf8_encode(htmlspecialchars($settings->monitor));
			$body .= "</extendedData>\r\n";
		}
		else {
			$body .= "\t\t<monitorEnabled>false</monitorEnabled>\r\n";
		}

		// Moodle browser security
		//   popup (0=none, 1=full screen pop-up with some JavaScript security)
		// Moodle 2.2.0+ (quiz module 2011100600+)
		//   browsersecurity ('-', 'securewindow', 'safebrowser')
		// if this setting is not disabled, it will interfere with the LDB integration
		if (isset($quiz->browsersecurity)) {
			if ($quiz->browsersecurity != "-")
				$launch_in_new_window = TRUE;
			else
				$launch_in_new_window = FALSE;
		}
		else {
			if ($quiz->popup != 0)
				$launch_in_new_window = TRUE;
			else
				$launch_in_new_window = FALSE;
		}

		if ($launch_in_new_window)
			$body .= "\t\t<launchInNewWindow>true</launchInNewWindow>\r\n";
		else
			$body .= "\t\t<launchInNewWindow>false</launchInNewWindow>\r\n";

		if ($settings !== FALSE && $launch_in_new_window)
			$body .= "\t\t<ok>false</ok>\r\n";
		else
			$body .= "\t\t<ok>true</ok>\r\n";

		$body .= "\t</assessment>\r\n";
	}

	$body .= "</assessmentList>\r\n";

	lockdownbrowser_MonitorServiceResponse("text/xml", $body, TRUE);
}

function lockdownbrowser_MonitorActionVersionInfo($parameters)
{
	global $CFG;

	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2004, "Must be logged in to perform the requested action");
	}

	$moodle_release = $CFG->release;
	$moodle_version = $CFG->version;

	$version_file = "$CFG->dirroot/blocks/lockdownbrowser/version.php";
	if (is_readable($version_file))
		include($version_file);
	else
		lockdownbrowser_MonitorServiceError(2038, "Block version file not found");

	if (!isset($plugin->version))
		lockdownbrowser_MonitorServiceError(2039, "Block version info missing");

	$block_version = $plugin->version;

	$body = $moodle_release . "\$%\$" . $moodle_version . "\$%\$" . $block_version;

	lockdownbrowser_MonitorServiceResponse("text/plain", $body, TRUE);
}

function lockdownbrowser_MonitorActionUserCourseRole($parameters)
{
	global $DB;

	if (!isloggedin()) {
		lockdownbrowser_MonitorServiceError(2004, "Must be logged in to perform the requested action");
	}
	if (!is_siteadmin()) {
		lockdownbrowser_MonitorServiceError(2024, "Must be logged in as admin to perform the requested action");
	}
	if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
		lockdownbrowser_MonitorServiceError(2025, "No courseRefId parameter was specified");
	}
	if (!isset($parameters["userId"]) || strlen($parameters["userId"]) == 0) {
		lockdownbrowser_MonitorServiceError(2044, "No userId parameter was specified");
	}

	$course_id = intval($parameters["courseRefId"]);
	$username = $parameters["userId"]; // actually user login name

	$context = context_course::instance($course_id);
	if ($context === FALSE) {
		lockdownbrowser_MonitorServiceError(2028, "The specified courseRefId is invalid: $course_id");
	}

	$body = "";

	if (strlen($body) == 0) { // check managers
		$managers = array();
		$roles = $DB->get_records("role", array("archetype" => "manager"));
		if ($roles === FALSE || count($roles) == 0) {
			lockdownbrowser_MonitorServiceError(2045, "The role archetype 'manager' was not found");
		}
		foreach ($roles as $role) {
			$users = get_role_users($role->id, $context);
			if ($users !== FALSE && count($users) > 0)
				$managers = array_merge($managers, $users);
		}
		if (count($managers) > 0) {
			foreach ($managers as $m) {
				if (strcasecmp($username, $m->username) == 0) {
					$body = "ADMIN";
					break;
				}
			}
		}
	}

	if (strlen($body) == 0) { // check editing teachers
		$editingteachers = array();
		$roles = $DB->get_records("role", array("archetype" => "editingteacher"));
		if ($roles === FALSE || count($roles) == 0) {
			lockdownbrowser_MonitorServiceError(2047, "The role archetype 'editingteacher' was not found");
		}
		foreach ($roles as $role) {
			$users = get_role_users($role->id, $context);
			if ($users !== FALSE && count($users) > 0)
				$editingteachers = array_merge($editingteachers, $users);
		}
		if (count($editingteachers) > 0) {
			foreach ($editingteachers as $et) {
				if (strcasecmp($username, $et->username) == 0) {
					$body = "INSTRUCTOR";
					break;
				}
			}
		}
	}

	if (strlen($body) == 0) { // check non-editing teachers
		$teachers = array();
		$roles = $DB->get_records("role", array("archetype" => "teacher"));
		if ($roles === FALSE || count($roles) == 0) {
			lockdownbrowser_MonitorServiceError(2048, "The role archetype 'teacher' was not found");
		}
		foreach ($roles as $role) {
			$users = get_role_users($role->id, $context);
			if ($users !== FALSE && count($users) > 0)
				$teachers = array_merge($teachers, $users);
		}
		if (count($teachers) > 0) {
			foreach ($teachers as $t) {
				if (strcasecmp($username, $t->username) == 0) {
					$body = "STUDENT";
					break;
				}
			}
		}
	}

	if (strlen($body) == 0) { // check students
		$students = array();
		$roles = $DB->get_records("role", array("archetype" => "student"));
		if ($roles === FALSE || count($roles) == 0) {
			lockdownbrowser_MonitorServiceError(2029, "The role archetype 'student' was not found");
		}
		foreach ($roles as $role) {
			$users = get_role_users($role->id, $context);
			if ($users !== FALSE && count($users) > 0)
				$students = array_merge($students, $users);
		}
		if (count($students) > 0) {
			foreach ($students as $s) {
				if (strcasecmp($username, $s->username) == 0) {
					$body = "STUDENT";
					break;
				}
			}
		}
	}

	if (strlen($body) == 0) {
		lockdownbrowser_MonitorServiceError(2049, "The specified userId does not have at least STUDENT access to the specified course.");
	}

	lockdownbrowser_MonitorServiceResponse("text/plain", $body, TRUE);
}

function lockdownbrowser_MonitorServiceRequest()
{
	$parameters = lockdownbrowser_MonitorRequestParameters();

	if (!isset($parameters["action"]) || strlen($parameters["action"]) == 0) {
		lockdownbrowser_MonitorServiceError(2005, "No service action was specified");
	}
	$action = $parameters["action"];

	if ($action == "login")
		lockdownbrowser_MonitorActionLogin($parameters);
	else if ($action == "userlogin")
		lockdownbrowser_MonitorActionUserLogin($parameters);
	else if ($action == "logout")
		lockdownbrowser_MonitorActionLogout($parameters);
	else if ($action == "courselist")
		lockdownbrowser_MonitorActionCourseList($parameters);
	else if ($action == "changesettings")
		lockdownbrowser_MonitorActionChangeSettings($parameters);
	else if ($action == "examroster")
		lockdownbrowser_MonitorActionExamRoster($parameters);
	else if ($action == "userinfo2")
		lockdownbrowser_MonitorActionUserInfo2($parameters);
	else if ($action == "usercourselist")
		lockdownbrowser_MonitorActionUserCourseList($parameters);
	else if ($action == "examinfo2")
		lockdownbrowser_MonitorActionExamInfo2($parameters);
	else if ($action == "examsync")
		lockdownbrowser_MonitorActionExamSync($parameters);
	else if ($action == "versioninfo")
		lockdownbrowser_MonitorActionVersionInfo($parameters);
	else if ($action == "usercourserole")
		lockdownbrowser_MonitorActionUserCourseRole($parameters);
	else
		lockdownbrowser_MonitorServiceError(2006, "Unrecognized service action: $action");
}


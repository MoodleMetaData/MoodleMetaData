<?php

///////////////////////////////////////////////////////////////////////////////
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2013 Respondus, Inc.  All Rights Reserved.

if (!isset($CFG)) {
    require_once("../../config.php");
}

require_once("$CFG->dirroot/blocks/lockdownbrowser/locklib.php");

if(! isset($CFG->block_lockdownbrowser_LDB_SERVERNAME) )
	$CFG->block_lockdownbrowser_LDB_SERVERNAME = LDB_SERVERNAME;
if(! isset($CFG->block_lockdownbrowser_LDB_SERVERID) )
	$CFG->block_lockdownbrowser_LDB_SERVERID = LDB_SERVERID;
if(! isset($CFG->block_lockdownbrowser_LDB_SERVERSECRET) )
	$CFG->block_lockdownbrowser_LDB_SERVERSECRET = LDB_SERVERSECRET;
if(! isset($CFG->block_lockdownbrowser_LDB_SERVERTYPE) )
	$CFG->block_lockdownbrowser_LDB_SERVERTYPE = 0;
if(! isset($CFG->block_lockdownbrowser_LDB_DOWNLOAD) )
	$CFG->block_lockdownbrowser_LDB_DOWNLOAD = '';

if(! isset($CFG->block_lockdownbrowser_MONITOR_USERNAME) )
	$CFG->block_lockdownbrowser_MONITOR_USERNAME = '';
if(! isset($CFG->block_lockdownbrowser_MONITOR_PASSWORD) )
	$CFG->block_lockdownbrowser_MONITOR_PASSWORD = '';

$settings->add(
  new admin_setting_heading(
    "lockdown_blockdescheader",
	get_string("blockdescheader", 'block_lockdownbrowser'),
	get_string("blockdescription", 'block_lockdownbrowser')
  )
);

$lockdownbrowser_version_file = "$CFG->dirroot/blocks/lockdownbrowser/version.php";
$lockdownbrowser_version = "(error: version not found)";
if (is_readable($lockdownbrowser_version_file)) {
	$lockdownbrowser_contents = file_get_contents($lockdownbrowser_version_file);
	if ($lockdownbrowser_contents !== FALSE) {
		$lockdownbrowser_parts = explode("=", $lockdownbrowser_contents);
		if (count($lockdownbrowser_parts) > 0) {
			$lockdownbrowser_parts = explode(";", $lockdownbrowser_parts[1]);
			$lockdownbrowser_version = trim($lockdownbrowser_parts[0]);
		}
	}
}
$settings->add(
  new admin_setting_heading(
	"lockdown_blockversionheader",
	get_string("blockversionheader", 'block_lockdownbrowser'),
	$lockdownbrowser_version
  )
);

$settings->add(
  new admin_setting_heading(
    "lockdown_adminsettingsheader",
	get_string("adminsettingsheader", 'block_lockdownbrowser'),
	get_string("adminsettingsheaderinfo", 'block_lockdownbrowser')
  )
);

$settings->add(
new admin_setting_configtext(
"block_lockdownbrowser_LDB_SERVERNAME",
get_string("servername", 'block_lockdownbrowser'),
get_string("servernameinfo", 'block_lockdownbrowser'),
$CFG->block_lockdownbrowser_LDB_SERVERNAME,
PARAM_TEXT
)
);
$settings->add(
new admin_setting_configtext(
"block_lockdownbrowser_LDB_SERVERID",
get_string("serverid", 'block_lockdownbrowser'),
get_string("serveridinfo", 'block_lockdownbrowser'),
$CFG->block_lockdownbrowser_LDB_SERVERID,
PARAM_TEXT
)
);
$settings->add(
new admin_setting_configtext(
"block_lockdownbrowser_LDB_SERVERSECRET",
get_string("serversecret", 'block_lockdownbrowser'),
get_string("serversecretinfo", 'block_lockdownbrowser'),
$CFG->block_lockdownbrowser_LDB_SERVERSECRET,
PARAM_TEXT
)
);
$settings->add(
new admin_setting_configtext(
"block_lockdownbrowser_LDB_SERVERTYPE",
get_string("servertype", 'block_lockdownbrowser'),
get_string("servertypeinfo", 'block_lockdownbrowser'),
$CFG->block_lockdownbrowser_LDB_SERVERTYPE,
PARAM_TEXT
)
);
$settings->add(
new admin_setting_configtext(
"block_lockdownbrowser_LDB_DOWNLOAD",
get_string("downloadurl", 'block_lockdownbrowser'),
get_string("downloadinfo", 'block_lockdownbrowser'),
$CFG->block_lockdownbrowser_LDB_DOWNLOAD,
PARAM_TEXT
)
);

$settings->add(
  new admin_setting_heading(
	"lockdown_authenticationsettingsheader",
	get_string("authenticationsettingsheader", "block_lockdownbrowser"),
	get_string("authenticationsettingsheaderinfo", "block_lockdownbrowser")
  )
);
$settings->add(
  new admin_setting_configtext(
	"block_lockdownbrowser_MONITOR_USERNAME",
	get_string("username", "block_lockdownbrowser"),
	get_string("usernameinfo", "block_lockdownbrowser"),
	$CFG->block_lockdownbrowser_MONITOR_USERNAME,
	PARAM_TEXT
  )
);
$settings->add(
  new admin_setting_configpasswordunmask(
	"block_lockdownbrowser_MONITOR_PASSWORD",
	get_string("password", "block_lockdownbrowser"),
	get_string("passwordinfo", "block_lockdownbrowser"),
	$CFG->block_lockdownbrowser_MONITOR_PASSWORD,
	PARAM_TEXT
  )
);


// status string

// eClass change CTL-548
// $CFG->sessioncookie is not set during a clean test install
$sessioncookie = isset($CFG->sessioncookie) ? $CFG->sessioncookie : '';
$ist = "";
if ( ! isset( $_COOKIE[ $CFG->block_lockdownbrowser_LDB_SESSION_COOKIE.$sessioncookie] ) ) {
	$ist .= "<div style='font-size: 125%; color:red; text-align: center; padding: 30px'>Warning: Moodle session cookie check failed.</div>";
}
if(! isset( $CFG->customscripts ) ) {
	$ist .= "<div style='font-size: 125%; color:red; text-align: center; padding: 30px'>Warning: ".'$CFG->customscripts'." is not set.</div>";
}
else if ( ! file_exists( "$CFG->customscripts/mod/quiz/attempt.php" ) || ! file_exists( "$CFG->customscripts/mod/quiz/view.php" ) ||
	! file_exists( "$CFG->customscripts/mod/quiz/review.php" ) ) {
	$ist .= "<div style='font-size: 125%; color:red; text-align: center; padding: 30px'>Warning: ".'$CFG->customscripts'." is set ($CFG->customscripts), but the lockdownbrowser scripts were not found.</div>";
}

$ist .= "<div style='text-align: center'>".get_string('tokens_free','block_lockdownbrowser').": ";
$tf = lockdownbrowser_tokens_free();
if ($tf && ($tf>0)) {
	$ist .= "$tf";
} else {
	$ist .= "0 (is mcrypt enabled?)";
}
$ist .= "<br>".get_string('test_server','block_lockdownbrowser').": <a href='$CFG->wwwroot/blocks/lockdownbrowser/tokentest.php' target='_blank'>/blocks/lockdownbrowser/tokentest.php</a>";
$ist .= "</div>";

$settings->add(
  new admin_setting_heading(
	"lockdown_adminstatus",
	get_string("adminstatus", 'block_lockdownbrowser'),
	$ist
  )
);


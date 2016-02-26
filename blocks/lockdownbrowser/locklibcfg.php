<?php

// FILE LOCATION:
// Block: blocks > lockdownbrowser > locklibcfg.php
// Module: mod > lockodwn > locklibcfg.php

///////////////////////////////////////////////////////////////////////////////
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2013 Respondus, Inc.  All Rights Reserved.


// ----- never edit these
define('LDB_SERVERNAME', 'eClass+Powered+by+Moodle');
define('LDB_SERVERID', '799714568'); 
define('LDB_SERVERSECRET', 'iamthemoodler'); 
define('LDB_SERVERTYPE', '0');
define('LDB_DOWNLOAD', 'http://www.respondus.com/lockdown/information.pl?ID=799714568');
// to remove link: define('LDB_DOWNLOAD', '');

// ----- edit these only if your server is nonstandard
// editing these will break the module!
define('LDB_TSERVER_1', 'http://moots1.respondus2.com');
define('LDB_TSERVER_2', 'http://moots2.respondus2.com');
define('LDB_TSERVER_ENDPOINT', '/SMServer/moodlews/token.html');
define('LDB_TSERVER_AKEY', 'myn3r41z');
define('LDB_TSERVER_BKEY', '1uti3n1yc0p3n3');
define('LDB_TSERVER_FORM1', 'v=a&i=%s&t=');
define('LDB_TSERVER_FORM2', '&a=%s&myn=%s&s=%s');
define('LDB_TSERVER_SET', 500);
define('LDB_TSERVER_REC', 84);
define('LDB_TSERVER_T1L', 48);
define('LDB_TSERVER_T2L', 32);
define('LDB_TSERVER_T2P', 51);
define('LDB_TOKEN1_COOKIE', 'mldbt1c');
define('LDB_TOKEN2_COOKIE', 'mldbc2t');
define('LDB_EPASS_COOKIE', 'mldbxc');
define('LDB_ID_COOKIE', 'mldbz');
define('LDB_SESSION_COOKIE', 'MoodleSession');
// if your server needs proxy credentials to make HTTP POST requests
define('LDB_PROXY_DEFINED', '0');  // '1'=yes

if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_1) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_1 = LDB_TSERVER_1;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_2) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_2 = LDB_TSERVER_2;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_ENDPOINT) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_ENDPOINT = LDB_TSERVER_ENDPOINT;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_AKEY) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_AKEY = LDB_TSERVER_AKEY;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_BKEY) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_BKEY = LDB_TSERVER_BKEY;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_FORM1) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_FORM1 = LDB_TSERVER_FORM1;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_FORM2) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_FORM2 = LDB_TSERVER_FORM2;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_SET) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_SET = LDB_TSERVER_SET;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_REC) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_REC = LDB_TSERVER_REC;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_T1L) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_T1L = LDB_TSERVER_T1L;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_T2L) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_T2L = LDB_TSERVER_T2L;
if(! isset($CFG->block_lockdownbrowser_LDB_TSERVER_T2P) )
	$CFG->block_lockdownbrowser_LDB_TSERVER_T2P = LDB_TSERVER_T2P;
if(! isset($CFG->block_lockdownbrowser_LDB_TOKEN1_COOKIE) )
	$CFG->block_lockdownbrowser_LDB_TOKEN1_COOKIE = LDB_TOKEN1_COOKIE;
if(! isset($CFG->block_lockdownbrowser_LDB_TOKEN2_COOKIE) )
	$CFG->block_lockdownbrowser_LDB_TOKEN2_COOKIE = LDB_TOKEN2_COOKIE;
if(! isset($CFG->block_lockdownbrowser_LDB_EPASS_COOKIE) )
	$CFG->block_lockdownbrowser_LDB_EPASS_COOKIE = LDB_EPASS_COOKIE;
if(! isset($CFG->block_lockdownbrowser_LDB_ID_COOKIE) )
	$CFG->block_lockdownbrowser_LDB_ID_COOKIE = LDB_ID_COOKIE;
	
if(! isset($CFG->block_lockdownbrowser_LDB_SESSION_COOKIE) )
	$CFG->block_lockdownbrowser_LDB_SESSION_COOKIE = 'MoodleSession';
if(! isset($CFG->block_lockdownbrowser_LDB_PROXY_DEFINED) )
	$CFG->block_lockdownbrowser_LDB_PROXY_DEFINED = LDB_PROXY_DEFINED;



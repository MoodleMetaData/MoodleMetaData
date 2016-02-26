<?php

///////////////////////////////////////////////////////////////////////////////
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2013 Respondus, Inc.  All Rights Reserved.

if ( file_exists( "$CFG->dirroot/blocks/lockdownbrowser/locklib.php" )) {

	require_once( "$CFG->dirroot/blocks/lockdownbrowser/locklib.php" );
	lockdownbrowser_check_for_lock();
}


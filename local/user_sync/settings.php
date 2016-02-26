<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_user_sync', 'user sync');
    $ADMIN->add ('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox('enable_user_sync_post',
            'Enable POST to Admin Tool for user sync script.',
            'User sync script will not post completion status to Admin Tool by default.',
            false));
    $settings->add(new admin_setting_configtext('user_sync_post_token',
            'Admin Tool POST token',
            'Copy $ENROLSYNC_TOKEN_KEY from mcp.srv.ualberta.ca.',
            ''));
}

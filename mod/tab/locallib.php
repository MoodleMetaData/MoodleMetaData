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

/**
 * Private page module utility functions
 *
 * @package    mod
 * @subpackage page
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/tab/lib.php");


/**
 * File browsing support class
 */
class tab_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}

function tab_get_editor_options($context) {
    global $CFG;
    return array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1,
        'context' => $context, 'noclean' => 1, 'trusttext' => 0);
}

function process_urls($string) {
    global $CFG, $PAGE;
    preg_match_all("/<a href=.*?<\/a>/", $string, $matches);
    foreach ($matches[0] as $mtch) {
        $mtchbits = explode('"', $mtch);
        $string = str_replace($mtch,  "{$mtchbits[1]}", $string);
    }
    $string = str_replace('<div class="text_to_html">', '', $string);
    $string = str_replace('</div>', '', $string);
    $string = str_replace('<p>', '', $string);
    $string = str_replace('</p>', '', $string);

    return $string;
}

/**
 * Returns general link or file embedding html.
 * @param string $fullurl
 * @param string $title
 * @param string $clicktoopen
 * @return string html
 */
function tab_embed_general($fullurl, $title, $clicktoopen, $mimetype) {
    global $CFG, $PAGE;

    $iframe = false;
    $forcelink = false;
    // IE can not embed stuff properly if stored on different server.
    // That is why we use iframe instead, unfortunately this tag does not validate.
    // In xhtml strict mode.
    if ($mimetype === 'text/html' and core_useragent::check_browser_version('MSIE', 5)) {
        debugging("Detected IE", DEBUG_DEVELOPER);
        if (preg_match('(^https?://[^/]*)', $fullurl, $matches)) {
            debugging("Detected IE w/ http://", DEBUG_DEVELOPER);
            // Make sure we aren't redirecting to a moodle page.
            if (strpos($CFG->wwwroot, $matches[0]) !== 0) {
                $forcelink = true;
            } else { // If it is a moodle then embed as iframe.
                $iframe = true;
            }
        }
    }
    $idsuffix = md5($fullurl);
    // We force the link because IE doesn't support embedding web pages.
    if ($forcelink) {
        $clicktoopen = get_string('embed_fail_msg_ie', 'tab') .
            "<a href='$fullurl' target='_blank'>". get_string('embed_fail_link_text', 'tab') . '</a>';
        $code = <<<EOT
<div class="resourcecontent resourcegeneral">
        $clicktoopen
</div>
EOT;
    } else if ($iframe) {
        $code = <<<EOT
<div class="resourcecontent resourcegeneral">
  <iframe id="resourceobject_$idsuffix" src="$fullurl">
    $clicktoopen
  </iframe>
</div>
EOT;
    } else {
        $code = <<<EOT
<div class="resourcecontent resourcegeneral">
  <object id="resourceobject_$idsuffix" data="$fullurl" type="$mimetype">
    <param name="src" value="$fullurl" />
    $clicktoopen
  </object>
</div>
EOT;
    }

    $PAGE->requires->js_init_call('M.mod_tab.init_maximised_embed', array("resourceobject_$idsuffix"), true);

    return $code;
}

/**
 * @param $courseid ID of the course where we retrieve the list of tab menu names.
 * @return array of tab menu names.
 */
function get_tab_menu_names_in_course($courseid) {
    global $DB;

    $sql = <<<SQL
SELECT DISTINCT t.menuname as menuname
FROM {tab} t
INNER JOIN {course_modules} cm ON t.id = cm.instance
WHERE cm.course=$courseid
ORDER BY t.menuname ASC
SQL;

    $mapcb = function($val) {
        return $val->menuname;
    };

    return array_map($mapcb, array_values($DB->get_records_sql($sql)));
}

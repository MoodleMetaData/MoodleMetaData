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
 * Syncing task for sped course completion.
 *
 * @package   block_spedcompletion
 * @copyright 2015 Trevor Jones
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_spedcompletion\task;

class sync_sped_completion extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sync_sped_completion', 'block_spedcompletion');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $DB;

        // We are going to measure execution times.
        $starttime = microtime();

        // And we have one initial $status.
        $status = true;
        $coursespedversionmap = array();
        $spedblocks = $DB->get_records('block_instances',
            array('blockname' => 'spedcompletion'),
            '',
            'parentcontextid, configdata');

        // Build a map (course id to sped version).
        foreach ($spedblocks as $block) {
            $coursecontext = $DB->get_record('context', array('id' => $block->parentcontextid));
            $config = unserialize(base64_decode($block->configdata));
            $coursespedversionmap[$coursecontext->instanceid] = isset($config->sped_version) ? $config->sped_version : 0;
        }
        if (empty($coursespedversionmap)) {
            return true;
        }

        list($incoursesql, $incourseparams) = $DB->get_in_or_equal(array_keys($coursespedversionmap));
        // Fetch all completions that are not processed yet.
        $ccompletions = $DB->get_recordset_sql("SELECT * FROM {course_completions} c WHERE (c.timecompleted IS NOT NULL
        AND c.timecompleted > 0) AND c.course {$incoursesql} AND NOT EXISTS (SELECT *
        FROM {spedcompletion} s WHERE s.userid = c.userid AND s.course = c.course) ORDER BY c.course", $incourseparams);

        $counter = 0;

        $spedservice = $this->newspedservice('0', get_config('', 'sped_completion_presharedkey'),
            get_config('', 'sped_completion_webservice'));

        foreach ($ccompletions as $ccompleted) {
            $user = $DB->get_record('user', array('id' => $ccompleted->userid), $fields = '*',
                $strictness = IGNORE_MISSING);
            if ($user !== null) {
                $spedservice->set_version($coursespedversionmap[$ccompleted->course]);
                // Posts to the url set in the blocks settings.
                if ($spedservice->post_update($user->idnumber)) {
                    $record = new \stdClass();
                    $record->userid = $ccompleted->userid;
                    $record->course = $ccompleted->course;
                    $record->timemodified = time();
                    $DB->insert_record('spedcompletion', $record);
                    $counter++;
                } else {
                    // Use 'Failed' here so we don't trigger a generic error in cron monitor.
                    mtrace("Failed updating sped service for ccid '{$user->idnumber}': " . $spedservice->get_message());
                }
            } else {
                mtrace('ERROR: cannot find user: ' . $ccompleted->userid);
                $status = false;
            }
        }
        $ccompletions->close();

        // Show times.
        mtrace($counter . ' sped completions synced (took ' . microtime_diff($starttime, microtime()) . ' seconds)');

        // And return $status.
        return $status;
    }
    protected function newspedservice($version, $key, $url) {
        global $CFG;
        require_once("{$CFG->dirroot}/blocks/spedcompletion/lib/sped_service.php");
        return new \block_spedcompletion\Sped($version, $key, $url);
    }
}
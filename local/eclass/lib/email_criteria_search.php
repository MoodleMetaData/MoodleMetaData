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
 * Email searching library. Queries emails based on criteria
 * added to filter by.
 *
 * @package    local
 * @category   eclass/lib
 * @author     Anthony Radziszewski radzisze@ualberta.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class email_search {

    protected $param = array();
    protected $termcodes = array();
    protected $categoryids = array();
    protected $roleids = array();
    protected $courseids = array();
    protected $lastaccess = 0;

    public function addterm($termcode) {
        if (!preg_match("/^[0-9]{4}$/", $termcode)) {
            throw new Exception('Term Codes must be in a comma separated list of 4 digit term codes.');
        } else {
            array_push($this->termcodes, $termcode);
        }
    }

    public function addcategory($catid) {
        if (!preg_match('/^\d+$/', $catid)) {
            throw new Exception('Category IDs must be in a comma separated list of valid category ID numbers.');
        } else {
            array_push($this->categoryids, $catid);
        }
    }

    public function addrole($roleid) {
        if (!preg_match('/^\d+$/', $roleid)) {
            throw new Exception('Role IDs must be in a comma separated list of valid role ID numbers.');
        } else {
            array_push($this->roleids, $roleid);
        }
    }

    public function addcourse($courseid) {
        if (!preg_match('/^\d+$/', $courseid)) {
            throw new Exception('Course IDs must be in a comma separated list of valid course ID numbers.');
        } else {
            array_push($this->courseids, $courseid);
        }
    }

    public function setlastaccess($accesstimestamp) {
        if (!preg_match('/^\d+$/', $accesstimestamp)) {
            throw new Exception('Invalid unix timestamp entered in lastaccess parameter');
        } else {
            $this->lastaccess = $accesstimestamp;
        }
    }

    public function getemails() {
        global $DB;

        $sql = 'select distinct u.email from {role_assignments} ra '.
            'JOIN {context} cxt ON(ra.contextid=cxt.id) '.
            'JOIN {user} u ON(ra.userid = u.id) '.
            'JOIN {course} c ON(cxt.instanceid=c.id) ';

        // Add category where statement if filtering by category ID's.
        if (!empty($this->categoryids[0])) {
            // Query for category id's.
            $querycategories = "c.category = ?";
            array_push($this->param, $this->categoryids[0]);
            for ($i = 1; $i < count($this->categoryids); $i++) {
                $querycategories .= " or " . "c.category = ?";
                array_push($this->param, $this->categoryids[$i]);
            }
            $sql .= 'where ('. $querycategories .') ';
        }

        // Add role where statement if filtering by role ID's.
        if (!empty($this->roleids[0])) {
            // Query for role id's.
            $queryroles = "ra.roleid = ?";
            array_push($this->param, $this->roleids[0]);
            for ($i = 1; $i < count($this->roleids); $i++) {
                $queryroles .= " or ra.roleid = ?";
                array_push($this->param, $this->roleids[$i]);
            }
            if (empty($this->categoryids[0])) {
                $sql .= 'where (' . $queryroles . ') ';
            } else {
                $sql .= 'AND (' . $queryroles . ') ';
            }
        }

        // Add course where statement if filtering by course ID's.
        if (!empty($this->courseids[0])) {
            // Query for course id's.
            $querycourses = "c.id = ?";
            array_push($this->param, $this->courseids[0]);
            for ($i = 1; $i < count($this->courseids); $i++) {
                $querycourses .= " or c.id = ?";
                array_push($this->param, $this->courseids[$i]);
            }
            if (empty($this->roleids[0]) && empty($this->categoryids[0])) {
                $sql .= 'where (' . $querycourses . ') ';
            } else {
                $sql .= 'AND (' . $querycourses . ') ';
            }
        }

        if ($this->lastaccess !== 0) {
            // Query for date last accessed.
            $queryaccessed = "u.lastaccess >= ?";
            array_push($this->param, $this->lastaccess);
            if (empty($this->courseids[0]) && empty($this->roleids[0]) && empty($this->categoryids[0])) {
                $sql .= 'where (' . $queryaccessed . ') ';
            } else {
                $sql .= 'AND (' . $queryaccessed . ') ';
            }
        }

        // Add termcode where statement if filtering by termcodes.
        if (!empty($this->termcodes[0])) {
            // Query for term codes.
            $i = 0;
            $queryterms = $DB->sql_like("co.idnumber", "'{$this->termcodes[$i]}._____'");
            for ($i = 1; $i < count($this->termcodes); $i++) {
                $queryterms .= "or " . $DB->sql_like("co.idnumber", "'{$this->termcodes[$i]}._____'");
            }
            if (empty($this->courseids[0]) && empty($this->roleids[0]) && empty($this->categoryids[0]) && $this->lastaccess == 0) {
                $sql .= 'where c.id IN (select e.courseid from {enrol} e, {cohort} co where e.customint1 = co.id ' .
                    'AND (' . $queryterms . ')) ';
            }
            $sql .= 'AND c.id IN (select e.courseid from {enrol} e, {cohort} co where e.customint1 = co.id ' .
                'AND (' . $queryterms . ')) ';
        }

        $sql .= ' ORDER BY u.email';

        $emails = $DB->get_recordset_sql($sql, $this->param);
        return($emails);
    }
}
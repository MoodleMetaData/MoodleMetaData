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
 * @class invalid_iclicker_id
 * @brief Exception thrown when error occurs.
 */
class invalid_iclicker_id extends \Exception {
}

/**
 * @class iclicker_registration_users
 * @brief Meant to encapsulate the operations on the iclickerregistration_users table.
 */
class iclicker_registration_users {
    public static $iclickeridpattern = "/^(?:\\d|[A-Z]){8}$/";
    // Since iclicker_id is not allowed in moodle php convention but a must for javascript.
    public static $iclickeridproperty = "iclicker_id";
    private static $instance = null;

    /**
     * This is a singleton (won't makes sense to have multiple instance of something without a state).
     */
    private function __construct() {
    }

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new iclicker_registration_users();
        }
        return self::$instance;
    }

    /**
     * @param {string} $iclickerid iclicker id to check for duplicates in a course.
     * @param {string} $courseid course where we check all the user's iclickers.
     * @return true if duplicate in a course.
     */
    public function is_iclicker_id_duplicate_in_course($iclickerid, $idnumber, $courseid) {
        global $DB;

        // Algorithm:
        // 1. JOIN users in course to the iclickerregistration_users.
        // 2. Filter out row with null iclicker_id.
        // 3. if the result is not empty, we return true.
        $sql = "SELECT COUNT(iu.*) AS result_count
            FROM {user} u
            JOIN {iclickerregistration_users} iu ON u.idnumber=iu.idnumber

            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON e.id = ue.enrolid

            WHERE u.idnumber <> '' AND
                  u.idnumber <> '$idnumber' AND
                  iu.iclickerid IS NOT NULL AND
                  e.courseid = $courseid AND
                  iu.iclickerid = '$iclickerid'";

        return $DB->get_record_sql($sql)->result_count > 0;
    }

    /**
     * @param $courseid ID of the course to check for duplicate.
     * @return int Number of duplicate iclicker id in a course.
     * @throws dml_missing_record_exception
     * @throws dml_multiple_records_exception
     */
    public function get_iclicker_id_duplicate_count_in_course($courseid) {
        global $DB;

        $sql = "SELECT COUNT(*) AS result_count
            FROM {user} u2
            LEFT JOIN {iclickerregistration_users} iu2 ON u2.idnumber = iu2.idnumber
            JOIN {user_enrolments} ue2 ON ue2.userid = u2.id
            JOIN {enrol} e2 ON e2.id = ue2.enrolid
            WHERE e2.courseid = '$courseid' AND
                  iu2.iclickerid IN (SELECT iu.iclickerid
                                     FROM {user} u
                                     JOIN {iclickerregistration_users} iu ON u.idnumber = iu.idnumber
                                     JOIN {user_enrolments} ue ON ue.userid = u.id
                                     JOIN {enrol} e ON e.id = ue.enrolid
                                     WHERE u.idnumber <> '' AND
                                           u.idnumber <> u2.idnumber AND
                                           e.courseid = e2.courseid AND
                                           iu.iclickerid = iu2.iclickerid)";

        return (int) $DB->get_record_sql($sql)->result_count;
    }

    /**
     * Since this checks for duplicate iclicker id globally, this is only for
     * admins.
     *
     * @return int Number of duplicate iclicker id in the moodle site.
     * @throws dml_missing_record_exception
     * @throws dml_multiple_records_exception
     */
    public function get_iclicker_id_duplicate_count() {
        global $DB;
        $sql = "SELECT COUNT(*) AS result_count
            FROM {user} u2
            LEFT JOIN {iclickerregistration_users} iu2 ON u2.idnumber = iu2.idnumber
            JOIN {user_enrolments} ue2 ON ue2.userid = u2.id
            JOIN {enrol} e2 ON e2.id = ue2.enrolid
            WHERE iu2.iclickerid IN (SELECT iu.iclickerid
                                     FROM {user} u
                                     JOIN {iclickerregistration_users} iu ON u.idnumber = iu.idnumber
                                     JOIN {user_enrolments} ue ON ue.userid = u.id
                                     JOIN {enrol} e ON e.id = ue.enrolid
                                     WHERE u.idnumber <> '' AND
                                           u.idnumber <> u2.idnumber AND
                                           e.courseid = e2.courseid AND
                                           iu.iclickerid = iu2.iclickerid)";

        return (int) $DB->get_record_sql($sql)->result_count;
    }

    /**
     * @param $idnumber ccid/idnumber of the student.
     * @return mixed {id, idnumber, iclicker_id, lastname, firstname, timemodified}
     * @throws dml_missing_record_exception
     * @throws dml_multiple_records_exception
     */
    public function get_user_left_join_iclickers($idnumber) {
        global $DB;
        $sql = "SELECT u.id AS id, u.idnumber AS idnumber, iu.iclickerid AS iclicker_id, u.lastname, u.firstname
            FROM {user} u
            LEFT JOIN {iclickerregistration_users} iu ON u.idnumber = iu.idnumber
            WHERE u.idnumber = '$idnumber' AND u.idnumber <> ''";
        return $DB->get_record_sql($sql);
    }

    /**
     * Note: This is bad software engineering right here. I have tried implementing this
     *       in a more modular fashion, e.g.
     *
     *       users = get_all_users_left_join_iclickers();
     *       users = hide_unregistered(users);
     *       users = sort(users);
     *       (... etc)
     *
     *       The following is simply too SLOW! By placing them all in one query,
     *       crap load of db optimizations happens in the background, making it
     *       order of 100 faster! The only software engineering approach here if
     *       you integrate a sql query builder.
     *
     * @param array $args Associated array of arguments. This makes the parameters more "agile", since I found these
     *              to constantly change. Parameters:
     *        courseid: Only the users of a course.
     *        timestart: Experimental. Don't user this.
     *        query: Filter rows that satisfies "LIKE %%XX%%", XX is one of the following: iclickerid, name, or ccid/idnumber.
     *        orderby: idnumber, iclickerid, name.
     *        ascending: true/false. If true, result will be in ascending based on "orderby". Otherwise, descending.
     * @return array
     */
    public function get_all_users_left_join_iclickers(array $args = array()) {
        global $DB;

        $stdfields = 'id, idnumber, iclicker_id, lastname, firstname, timemodified, duplicate_count';

        $iclickerregistration = (isset($args['hideunregistered']) && $args['hideunregistered']) ?
            'JOIN {iclickerregistration_users} iu ON u.idnumber = iu.idnumber' :
            'LEFT JOIN {iclickerregistration_users} iu ON u.idnumber = iu.idnumber';

        $sql = "SELECT u.id AS id, u.idnumber AS idnumber, iu.iclickerid AS iclicker_id, u.lastname, u.firstname, e.courseid,
                       iu.timemodified
                FROM {user} u
                $iclickerregistration
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                WHERE u.idnumber <> ''";

        // Do the cheap WHERE clause(s) early so we less to deal with later.
        // Heavy WHERE clause(s) like LIKE %XX% are saved later after the joins.
        $filterbycourse = isset($args['courseid']) && $args['courseid'];
        if ($filterbycourse) {
            $courseid = $args['courseid'];
            $sql = "$sql AND e.courseid=$courseid";
        }
        $timestart = isset($args['timestart']) && $args['timestart'];
        if ($timestart) {
            $timestart = $args['timestart'];
            $sql = "$sql AND iu.timemodified >= $timestart";
        }

        /*
         * In summary (I start describing from the inner most sql, outward, excluding the ($sql).
         * 1. ($sql) are just user left join iclickerregistration_users, with some WHERE clauses.
         * 2. For each user in (1), we get the duplicate iclicker count of each.
         * 3. (2) Only takes the duplicate count of those with iclickers. Thus the output will only have
         *    users with iclickers. To solve this, we take (1) and have them all have duplicate_count 0.
         * 4. To recap, (3) have all users with duplicate count 0, (2) have all users with iclicker id and
         *    their duplicate count. We then take the union of these. The rows now contain all users, but we
         *    now have the problem of duplicates.
         * 5. Since we just want to know if dupicate count > 0,we take the row with maximum duplicate count.
         *    This solves the duplicate user problem of (4).
         */
        $sql = "(SELECT iq3.id, iq3.idnumber, iq3.iclicker_id, iq3.lastname, iq3.firstname, iq3.timemodified,
                        MAX(iq3.duplicate_count) AS duplicate_count
                FROM (SELECT iq1.id, iq1.idnumber, iq1.iclicker_id, iq1.lastname, iq1.firstname, iq1.timemodified,
                             COUNT(iq1.iclicker_id) AS duplicate_count
                      FROM ($sql) iq1, ($sql) iq2
                      WHERE iq1.courseid = iq2.courseid AND
                            iq1.idnumber <> iq2.idnumber AND
                            iq1.iclicker_id IS NOT NULL AND
                            iq1.iclicker_id = iq2.iclicker_id
                      GROUP BY iq1.id, iq1.idnumber, iq1.iclicker_id, iq1.lastname, iq1.firstname, iq1.courseid,
                               iq1.timemodified) iq3
                    GROUP BY iq3.id, iq3.idnumber, iq3.iclicker_id, iq3.lastname, iq3.firstname, iq3.timemodified)

                UNION

                (SELECT iq4.id, iq4.idnumber, iq4.iclicker_id, iq4.lastname, iq4.firstname, iq4.timemodified, 0 AS duplicate_count
                FROM ($sql) iq4)";
        $sql = "SELECT iq5.id, iq5.idnumber, iq5.iclicker_id, iq5.lastname, iq5.firstname, iq5.timemodified,
                       MAX(iq5.duplicate_count) AS duplicate_count
                FROM ($sql) iq5
                GROUP BY iq5.id, iq5.idnumber, iq5.iclicker_id, iq5.lastname, iq5.firstname, iq5.timemodified";

        // Project the $stdfields.
        $sql = "SELECT $stdfields
                FROM ($sql) iqlast
                WHERE true";

        // Save the most expensive condition in the bottom of the query tree.
        $query = isset($args['query']) && strlen($args['query']) > 0;
        if ($query) {
            $querystring = strtolower($args['query']);
            $sql = "$sql AND (LOWER(idnumber) LIKE '%$querystring%' OR
                              LOWER(iclicker_id) LIKE '%$querystring%' OR
                              LOWER(firstname||' '||lastname) LIKE '%$querystring%' OR
                              LOWER(lastname||', '||firstname) LIKE '%$querystring%')";
        }

        $filterconflicts = isset($args['filterconflicts']) && strlen($args['filterconflicts']) > 0;
        if ($filterconflicts) {
            $sql = "$sql AND duplicate_count > 0";
        }

        // Ascending?
        $ascending = true;
        if (isset($args['ascending'])) {
            $ascending = $args['ascending'];
        }
        $ascendingstr = $ascending ? "ASC" : "DESC";

        // Sorting operations.
        $sort = isset($args['orderby']) && $args['orderby'];
        if ($sort) {
            $orderby = $args['orderby'];
            switch($orderby) {
                case 'idnumber':
                    $sql = "$sql ORDER BY iqlast.idnumber $ascendingstr";
                    break;
                case 'name':
                    $sql = "$sql ORDER BY iqlast.lastname||iqlast.firstname $ascendingstr";
                    break;
                case 'iclicker_id':
                    $sql = "$sql ORDER BY iqlast.iclicker_id $ascendingstr";
                    break;
                case 'timemodified':
                    $sql = "$sql ORDER BY iqlast.timemodified $ascendingstr";
                    break;
            }
        }

        return $DB->get_records_sql($sql);
    }

    /**
     * @param $iclickeruser @see install.xml iclickerregistration_user table.
     * @param $courseid ID of the course.
     * @return bool True
     * @throws dml_missing_record_exception
     * @throws dml_multiple_records_exception
     */
    public function is_user_in_course($iclickeruser, $courseid) {
        global $DB;
        $sql = "SELECT COUNT(*) AS is_enrolled
                FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                WHERE ue.userid = $iclickeruser->id AND
                      e.courseid = $courseid";
        $isenrolled = $DB->get_record_sql($sql)->is_enrolled > 0;
        return $isenrolled;
    }

    /**
     * @param $iclickeruser @see install.xml iclickerregistration_user table.
     * @return array of courses in which user belongs to.
     */
    public function get_courses($iclickeruser) {
        global $DB;
        $sql = "SELECT e.courseid, course.fullname, course.shortname
                FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {course} course ON course.id = e.courseid

                WHERE ue.userid = $iclickeruser->id AND
                      e.courseid <> 1";  // No site-level course.
        return $DB->get_records_sql($sql);
    }

    /**
     * @param $iclickeruser @see install.xml iclickerregistration_user table.
     * @return array Array of objects containing, courses and the duplicate count in those courses.
     */
    public function get_iclicker_user_duplicate_profile($iclickeruser) {
        $iclickeridproperty = self::$iclickeridproperty;

        $iclickeruserregistered = isset($iclickeruser->$iclickeridproperty);
        if (!$iclickeruserregistered) {
            return [];  // Return empty duplicate profile.
        }

        $courses = $this->get_courses($iclickeruser);
        $duplicateprofiles = array();

        foreach ($courses as $course) {
            if ($this->is_iclicker_id_duplicate_in_course($iclickeruser->$iclickeridproperty,
                $iclickeruser->idnumber, $course->courseid)) {
                $duplicateprofile = new \stdClass;
                $duplicateprofile->course = $course;
                $duplicateprofile->duplicatecount = 1;  // Note: This is a fake number at the moment, but doesnt matter.
                $duplicateprofiles[] = $duplicateprofile;
            }
        }

        return $duplicateprofiles;
    }

    /**
     * @param $iclickeruser @see install.xml iclickerregistration_user table.
     * @param $courseid ID of the course to check if the $iclickeruser have a duplicate in.
     * @return array Containing a single object with the course (corresponding to $courseid) and duplicate count of that course.
     */
    public function get_iclicker_user_duplicate_profile_in_course($iclickeruser, $courseid) {
        $duplicateprofiles = $this->get_iclicker_user_duplicate_profile($iclickeruser);
        $newduplicateprofile = array();
        foreach ($duplicateprofiles as $profile) {
            if ($profile->course->courseid === $courseid) {
                $newduplicateprofile[] = $profile;
                break;
            }
        }

        return $newduplicateprofile;
    }

    /**
     * @param $iclickeruser @see install.xml iclickerregistration_user table.
     * @return bool True if the $iclickeruser->iclicker_id (note iclicker_id is aliased by $iclickeridproperty)
     *              exist, and thus registered.
     */
    public function is_user_registered($iclickeruser) {
        $iclickeridproperty = self::$iclickeridproperty;
        return !!$iclickeruser->$iclickeridproperty;
    }

    /**
     * @param $iclickerid iClicker ID to be check.
     * @return bool True if the user validates. DOES NOT RETURN FALSE DUE TO THE MANY KIND OF ERRORS.
     * @throws invalid_iclicker_id Instead of returning false, throws an exception.
     */
    public function validate_iclicker_id($iclickerid) {
        // Length <= 8, support for old clicker device ids.
        if (strlen("$iclickerid") > 8) {
            throw new invalid_iclicker_id("Character count > 8");
        }

        // Can't use iclickeridpattern.
        if (preg_match(self::$iclickeridpattern, $iclickerid) !== 1) {
            throw new invalid_iclicker_id("iclicker_id can only contains A-F and 0-9");
        }

        $idarray = array();
        $idarray[0] = substr($iclickerid, 0, 2);
        $idarray[1] = substr($iclickerid, 2, 2);
        $idarray[2] = substr($iclickerid, 4, 2);
        $idarray[3] = substr($iclickerid, 6, 2);
        $checksum = 0;
        foreach ($idarray as $piece) {
            $hex = hexdec($piece);
            $checksum = $checksum ^ $hex;
        }

        if ($checksum != 0) {
            throw new invalid_iclicker_id("iclicker_id checkusm validation failed.");
        }

        return true;
    }

    /**
     * @param {Object} $user User object. Ensure that user have an idnumber.
     * @return mixed @see db/install.xml for iclickerregistration_users schema.
     */
    public function get_iclicker($user) {
        return $this->get_iclicker_by_idnumber($user->idnumber);
    }

    /**
     * @param {string} $idnumber
     * @return mixed @see db/install.xml for iclickerregistration_users schema.
     */
    public function get_iclicker_by_idnumber($idnumber) {
        global $DB;
        return $DB->get_record('iclickerregistration_users', array('idnumber' => $idnumber), '*');
    }

    /**
     * @return array All iclickers entry in db.
     */
    public function get_iclickers() {
        global $DB;
        return $DB->get_records('iclickerregistration_users', null, 'idnumber ASC');
    }

    /**
     * @param $iclickerobj @see db/install.xml for iclickerregistration_users specs.
     * @return id of the record in iclickerregistration_users table.
     * @throws invalidiclickerid
     */
    public function register_iclicker_id($iclickerobj) {
        global $DB;
        $iclickerobj->timemodified = time();
        $iclickerobj->iclickerid = strtoupper($iclickerobj->iclickerid);
        $this->validate_iclicker_id($iclickerobj->iclickerid);
        return $DB->insert_record('iclickerregistration_users', $iclickerobj);
    }

    /**
     * @param $iclickerobj @see db/install.xml for iclickerregistration_users specs.
     * @throws invalidiclickerid
     */
    public function update_iclicker_id($iclickerobj) {
        global $DB;
        $iclickerobj->timemodified = time();
        $iclickerobj->iclickerid = strtoupper($iclickerobj->iclickerid);
        $this->validate_iclicker_id($iclickerobj->iclickerid);
        $DB->update_record('iclickerregistration_users', $iclickerobj);
    }

    /**
     * @param $iclickerobj @see db/install.xml for iclickerregistration_users specs.
     */
    public function delete_iclicker_id($iclickerobj) {
        global $DB;
        $DB->delete_records('iclickerregistration_users', (array)$iclickerobj);
    }

    /**
     * @param {Object} $user Mooldle user object.
     * @return bool true if user is already registered.
     */
    public function is_user_already_registered($user) {
        return $this->is_user_already_registered_by_idnumber($user->idnumber);
    }

    /**
     * @param {string} $idnumber idnumber of the user.
     * @return bool True if the idnumber have a registered iclicker.
     *              False if idnumber is invalid, or user is not registered.
     */
    public function is_user_already_registered_by_idnumber($idnumber) {
        if (isset($idnumber) === false || $idnumber === "") {
            return false;
        }
        return $this->get_iclicker_by_idnumber($idnumber) !== false;
    }

    /**
     * @param {string} $iclickerid
     * @return mixed @see db/install.xml for iclickerregistration_users schema.
     */
    public function is_iclicker_id_already_registered($iclickerid) {
        return $this->get_iclicker_by_iclicker_id($iclickerid) !== false;
    }

    /**
     * @param {string} $iclickerid
     * @return mixed @see db/install.xml for iclickerregistration_users schema.
     */
    public function get_iclicker_by_iclicker_id($iclickerid) {
        global $DB;
        return $DB->get_record('iclickerregistration_users', array('iclickerid' => $iclickerid), '*');
    }
}
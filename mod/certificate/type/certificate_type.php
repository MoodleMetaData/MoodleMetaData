<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Certificate module internal API,
 *
 * preview.php is meant for previewing a certificate.
 *
 * @package    mod_certificate
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @class certicate_type
 *
 * This class don't have much point other than reducing the need for thinking
 * when dealing with certificate types. Prior to this, you just include/require
 * a certificate type and it have lots of assumptions. Having a constructor
 * where the requirements are explicit makes future development of certificate
 * types easier.
 */
abstract class certificate_type {
    /**
     * @param $certificate {Object} corresponding to certificate schema in db/install.xml
     * @param $certificateissue {Object} corresponding to certificate_issues schema in db/install.xml
     * @param $course {Object} course in which $certificate is attached to.
     * @param $coursemodule {Object} course_modules
     * @param $user {Object}  defaults to current user aka global $USER.
     */
    public function __construct($certificate, $certificateissue, $course, $coursemodule, $user = null) {
        global $USER;

        $this->certificate = $certificate;
        $this->certificateissue = $certificateissue;
        $this->course = $course;
        $this->coursemodule = $coursemodule;
        $this->user = $user == null? $USER : $user;
    }

    /**
     * Render the certificate to pdf.
     * @return {PDF Object}
     */
    public function get_pdf() {}
}
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
 * Handles viewing a certificate
 *
 * @package    mod_certificate
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__)."/../../config.php");
require_once("$CFG->dirroot/mod/certificate/locallib.php");
require_once("$CFG->dirroot/mod/certificate/deprecatedlib.php");
require_once("$CFG->dirroot/mod/certificate/type/certificate_type.php");
require_once("$CFG->libdir/pdflib.php");

/**
 * @class certificate_type_pdf_renderer
 */
abstract class certificate_pdf_renderer_abstract {
    /**
     * For more info of these objects, @see mod/certificate/db/install.xml
     *
     * @param $certificate object returned by database query on table 'certificate'
     * @param $course object returned by database query on table 'course'
     * @param $certificateissues object returned by database query on table 'certificate_issues'
     */
    public function __construct($certificate, $course, $certificateissues, $coursemodule) {
        $this->certificate = $certificate;
        $this->certificateissues = $certificateissues;
        $this->course = $course;
        $this->coursemodule = $coursemodule;
    }

    /**
     * Renders the certificate to a pdf object.
     * @return $pdf {PDF object}
     */
    public function get_pdf() {
        global $CFG, $USER;

        // Call the appropriate certificate_type::render
        $certificatetype = $this->certificate->certificatetype;
        require_once("$CFG->dirroot/mod/certificate/type/$certificatetype/certificate.php");
        $certificate = new $certificatetype($this->certificate, $this->certificateissues, $this->course,
            $this->coursemodule, $USER);

        return $certificate->get_pdf();
    }
}

/**
 * @class certificate_pdf_renderer_preview
 *
 * Use this to render certificate for preview purposes. No data is saved in db.
 */
class certificate_pdf_renderer_preview extends certificate_pdf_renderer_abstract {
    /**
     * @inheritdoc
     */
    public function __construct($certificate, $course, $coursemodule, $certificateissues = null) {
        // If no certificateissues provided, we replace it with some dummy,
        // enough to that the $this->get_pdf() will be able to render something.
        if (empty($certificateissues))  {
            // Create a dummy certificate_issues.
            $certificateissues = new stdClass;
            $certificateissues->timecreated = time();
            $certificateissues->code = certificate_generate_code();
        }

        parent::__construct($certificate, $course, $certificateissues, $coursemodule);
    }
}

/**
 * @class certificate_pdf_renderer
 *
 * Use this to do render certificate and send notification (email or otherwise) to
 * teacher and students.
 */
class certificate_pdf_renderer extends certificate_pdf_renderer_abstract {
    /**
     * @param object $certificate
     * @param $course
     * @param $coursemodule
     * @param object $certificateissues
     */
    public function __construct($certificate, $course, $coursemodule, $certificateissues = null) {
        // See if certificate issues exist.
        if (empty($certificateissues)) {
            global $USER;
            $certificateissues = certificate_get_issue($course, $USER, $certificate, $coursemodule);
        }

        parent::__construct($certificate, $course, $certificateissues, $coursemodule);
    }
}
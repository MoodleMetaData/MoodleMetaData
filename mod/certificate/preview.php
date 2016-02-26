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

require_once("../../config.php");
require_once("$CFG->dirroot/mod/certificate/locallib.php");
require_once("$CFG->dirroot/mod/certificate/deprecatedlib.php");
require_once("$CFG->libdir/pdflib.php");

// For more information about
$id = required_param('id', PARAM_INT);  // Course Module ID
$name = required_param('name', PARAM_TEXT);
$orientation = required_param('orientation', PARAM_TEXT);
$borderstyle = required_param('borderstyle', PARAM_TEXT);
$bordercolor = required_param('bordercolor', PARAM_INT);
$printseal = required_param('printseal', PARAM_TEXT);
$printsignature = required_param('printsignature', PARAM_TEXT);
$printwmark = required_param('printwmark', PARAM_TEXT);
$printdate = required_param('printdate', PARAM_INT);
$printteacher = required_param('printteacher', PARAM_INT);
$datefmt = required_param('datefmt', PARAM_INT);
$printgrade = required_param('printgrade', PARAM_INT);
$gradefmt = required_param('gradefmt', PARAM_INT);
$printoutcome = required_param('printoutcome', PARAM_INT);
$printhours = required_param('printhours', PARAM_INT);
$printnumber = required_param('printnumber', PARAM_INT);
$certificatetype = required_param('certificatetype', PARAM_RAW);
$customtext = required_param('customtext', PARAM_TEXT);

// Create a certificate object.
$certificate = new stdClass;
$certificate->course = $COURSE->id;
$certificate->name = $name;
$certificate->orientation = $orientation;
$certificate->borderstyle = $borderstyle;
$certificate->bordercolor = $bordercolor;
$certificate->printseal = $printseal;
$certificate->printsignature = $printsignature;
$certificate->printwmark = $printwmark;
$certificate->printdate = $printdate;
$certificate->printteacher = $printteacher;
$certificate->datefmt = $datefmt;
$certificate->printgrade = $printgrade;
$certificate->gradefmt = $gradefmt;
$certificate->printoutcome = $printoutcome;
$certificate->printhours = $printhours;
$certificate->printnumber = $printnumber;
$certificate->customtext = $customtext;
$certificate->certificatetype = $certificatetype;

if (!$cm = get_coursemodule_from_id('certificate', $id)) {
    print_error('Course Module ID was incorrect');
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');
}

// Security related stuff.
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
// We can't just let anyone view this. Only those that can
// edit/manage can (e.g. teacher, editingteacher, manager).
require_capability('mod/certificate:manage', $context);

// Initialize $PAGE, compute blocks
$PAGE->set_url('/mod/certificate/preview.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string(get_string('certificatepreviewtitle', 'certificate')));
$PAGE->set_heading(format_string($course->fullname));

$filename = certificate_get_certificate_filename($certificate, $cm, $course) . '.pdf';

// Render certificate into a pdf object..
require("$CFG->dirroot/mod/certificate/certificate_pdf_renderer.php");
$cprp = new certificate_pdf_renderer_preview($certificate, $course, $cm);
$pdf = $cprp->get_pdf();

// PDF contents are now in $file_contents as a string.
$filecontents = $pdf->Output('', 'S');

send_file($filecontents, $filename, 0, 0, true, false, 'application/pdf');
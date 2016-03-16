<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';
require_once $CFG->dirroot.'\lib\tcpdf\tcpdf.php';
require_once 'lib.php';

/**
 * The form to display the tab for general information.
 */
class syllabus_form extends moodleform {
	
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;
		
		// initialize the form.
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
		
		
		$mform->registerNoSubmitButton('syllubusgenerate');

 		$mform->addElement('html','<form> generate syllabus: 
 				<input type="submit" name="syllubusdisplay" value="preview"/>
 				<input type="submit" name="syllubusdownload" value="download"/></form>'); 
		
		
		if(isset($_POST['syllubusdownload'])){

			$filename = $this->do_generate(2);
		}
		if(isset($_POST['syllubusdisplay'])){
		
			$filename = $this->do_generate(1);
		}

		
		$this->add_action_buttons(true, "done generation");
	}

	//If you need to validate your form information, you can override  the parent's validation method and write your own.	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		global $DB, $CFG, $USER; //Declare them if you need them
		
		return $errors;
    }
	
	public static function save_data($data) {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;
	}
	
	function do_generate($optionno){
		global $CFG, $DB, $USER;
		global $course;
		$sessionnumber = 0;
		$readingnumber = 0;
		$instructoremail = '';
		$officehours = '';
		$officelocation = '';
		$courseInstructor = '';
		$phonenumber = 0;
		$instructoremail = '';
		$coursedescription = '';
		
//collecting relative data from database===============================================================================		
		if($existCourseInfo = $DB->get_record('courseinfo', array('courseid'=>$course->id))){
			$coursetopic = $existCourseInfo->coursetopic;
			$coursedescription = $existCourseInfo->coursedescription;
			$courseInstructor = $USER->lastname.', '.$USER->firstname;
			if($existInstructorInfo = $DB->get_record('courseinstructors', array('courseid'=>$existCourseInfo->id, 'userid'=>$USER->id))){
				$officelocation = $existInstructorInfo->officelocation;
				$officehours = $existInstructorInfo->officehours;
				$instructoremail =  $existInstructorInfo->email;
				$phonenumber = $existInstructorInfo->phonenumber;
			}
			$sessionnumber = $DB->count_records('coursesession', array('courseid'=>$existCourseInfo->courseid));
			if($sessionnumber>0){
				$coursesessions = $DB->get_records('coursesession', array('courseid'=>$existCourseInfo->courseid));			
			}	
			$readingnumber = $DB->count_records('coursereadings', array('courseid'=>$existCourseInfo->courseid));
			if($readingnumber>0){
				$coursereadings = $DB->get_records('coursereadings', array('courseid'=>$existCourseInfo->courseid));
			}
			
			$courseobjectives = $DB->get_records('courseobjectives', array('courseid'=>$existCourseInfo->courseid));
			$courseassessments = $DB->get_records('courseassessment', array('courseid'=>$existCourseInfo->courseid));
		}
		

		
//start pdf generation===============================================================================		
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Olaf Lederer');
		$pdf->SetTitle('Course Syllabus');
		$pdf->SetSubject('Syllabus PDF');
		$pdf->SetKeywords('TCPDF, PDF, Course, Syllubs');
		 
		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
	// set font
	$pdf->SetFont('times', 'B', 20);
	// add a page
	$pdf->AddPage();
	$logo = '<p><img src="ualbertalogo.jpg" alt="test alt attribute" width="200" height="70" border="0" /></p>';
	$pdf->writeHTMLCell(0, 0, '', '', $logo, 0, 1, 0, true, 'L', true);
	$coursefullname = $course->shortname.': '.$course->fullname;
	$pdf->Cell(0, 0, '', 0, 0, 'C');
	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');
	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');
	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');
	$pdf->Ln();
	$pdf->Cell(0, 0, $coursefullname, 0, 1, 'C');
	$pdf->Cell(0, 0, '', 0, 0, 'C');
	$pdf->Ln();
//put general course information(instructor,officehour,location) into the pdf------------------------------------------
	$courselogistics = <<<EOD
	Fall/Winter/Spring/Summer<br>
	<b>Course Weight</b>: *3
EOD;
	$pdf->SetFont('times', '', 12);
	$pdf->writeHTMLCell(0, 0, '', '', $courselogistics, 0, 1, 0, true, 'C', true);
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$courselogistics = <<<EOD
	<b>Coordinator</b>: $courseInstructor<br>
	<b>Office</b>: $officelocation, <b>phone</b>: $phonenumber<br>
	<b>Email</b>: $instructoremail<br>
	<b>Office Hours</b>: $officehours 
EOD;
	$pdf->SetFont('times', '', 12);
	$pdf->writeHTMLCell(0, 0, '', '', $courselogistics, 0, 1, 0, true, 'C', true);
	$pdf->AddPage();
	
	
	
//put course overview information into the pdf------------------------------------------	
	$pdf->SetFont('times', 'B', 15);
	$pdf->Write(5, 'Course description', '', 0, 'L', true, 0, false, false, 0);	
	$pdf->SetFont('times', '', 10);
	$pdf->Write(0, $coursedescription, '', 0, 'L', true, 0, false, false, 0);
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	

//put course reading information into the pdf------------------------------------------
	$pdf->SetFont('times', 'B', 15);
	$pdf->Write(5, 'Course Readings', '', 0, 'L', true, 0, false, false, 0);
	if($readingnumber>0){
		$pdf->SetFont('times', '', 10);
		$readingno = 1;
		foreach ($coursereadings as $coursereading) {
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
			$reading = <<<EOD
			$readingno: $coursereading->readingname
			$coursereading->readingurl
EOD;
			$pdf->Write(0, $reading, '', 0, 'L', true, 0, false, false, 0);
			$readingno ++;
		}
	}
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	
	
//put course objective information into the pdf------------------------------------------
	$pdf->SetFont('times', 'B', 15);
	$pdf->Write(5, 'Course Objectives', '', 0, 'L', true, 0, false, false, 0);
	$pdf->SetFont('times', '', 10);
	$objdes = 'The course is designed to develop the following knowledge, skills and attitudes:';
	$pdf->Write(5, $objdes, '', 0, 'L', true, 0, false, false, 0);
	if(isset($courseobjectives)){
	$knowledge = <<<EOD
		Knowledge
		Students who successfully complete this course will be able to:
EOD;
	$Skills = <<<EOD
		Skills
		Students who successfully complete this course will be able to:
EOD;
	$Attitude = <<<EOD
		Attitude
		Students who successfully complete this course will be able to:
EOD;
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->SetFont('times', 'B', 12);
	$pdf->Write(10, $knowledge, '', 0, 'L', true, 0, false, false, 0);
	$pdf->SetFont('times', '', 10);
	foreach ($courseobjectives as $courseobjective) {
		if($thisobj = $DB->get_record('learningobjectives', array('id'=>$courseobjective->objectiveid))){
			if($thisobj->objectivetype == 'Knowledge'){
				$kobj = <<<EOD
				<ul>
				<li>$thisobj->objectivename</li>
				</ul>
EOD;
				$pdf->writeHTMLCell(0, 0, '', '', $kobj, 0, 1, 0, true, '', true);
			}
		}
	}
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->SetFont('times', 'B', 12);
	$pdf->Write(10, $Skills, '', 0, 'L', true, 0, false, false, 0);
	$pdf->SetFont('times', '', 10);
	foreach ($courseobjectives as $courseobjective) {
		if($thisobj = $DB->get_record('learningobjectives', array('id'=>$courseobjective->objectiveid))){
			if($thisobj->objectivetype == 'Skill'){
				$sobj = <<<EOD
				<ul>
				<li>$thisobj->objectivename</li>
				</ul>
EOD;
				$pdf->writeHTMLCell(0, 0, '', '', $sobj, 0, 1, 0, true, '', true);
			}
		}
	}
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->SetFont('times', 'B', 12);
	$pdf->Write(10, $Attitude, '', 0, 'L', true, 0, false, false, 0);
	$pdf->SetFont('times', '', 10);
	foreach ($courseobjectives as $courseobjective) {
		if($thisobj = $DB->get_record('learningobjectives', array('id'=>$courseobjective->objectiveid))){
			if($thisobj->objectivetype == 'Attitude'){
				$aobj = <<<EOD
				<ul>
				<li>$thisobj->objectivename</li>
				</ul>
EOD;
				$pdf->writeHTMLCell(0, 0, '', '', $aobj, 0, 1, 0, true, '', true);
			}
		}
	}
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	}
	
	
//put course grading information into the pdf------------------------------------------
	$pdf->SetFont('times', 'B', 15);
	$pdf->Write(5, 'Grading', '', 0, 'L', true, 0, false, false, 0);
	$pdf->SetFont('times', '', 10);
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	if (isset($courseassessments)){
		$assessmenttable = '
	<table border="0.1" cellspacing="0.1" cellpadding="0.1" id="gradingtable">
    <tr>
        <th width="17%" align="center"><b>Title</b></th>
        <th width="10%" align="center"><b>Weight</b></th>
        <th width="16%" align="center"><b>Date</b></th>
		<th width="17%" align="center"><b>Type</b></th>
		<th width="40%" align="center"><b>Description</b></th>
    </tr>';	
		foreach ($courseassessments as $courseassessment) {
			$assessmenttable .= '<tr>
					<td width="17%">'.$courseassessment->assessmentname.'</td>
					<td width="10%" align="center">'.$courseassessment->assessmentweight.'%</td>
					<td width="16%" align="center">'.$courseassessment->assessmentduedate.'</td>
					<td width="17%">'.$courseassessment->type.'</td>	
					<td width="40%">'.$courseassessment->description.'</td>	
					</tr>';
		}
		$assessmenttable .= '</table>';
		$pdf->writeHTML($assessmenttable, true, false, true, false, '');
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	}
	
	
//put course session information into the pdf------------------------------------------
		$pdf->SetFont('times', 'B', 15);
		$pdf->Write(5, 'Course Sessions', '', 0, 'L', true, 0, false, false, 0);
		if($sessionnumber>0){
			$pdf->SetFont('times', '', 10);
			$sessionno = 1;
			foreach ($coursesessions as $coursesession) {
				$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
				$courselectures = <<<EOD
				<font size="10"><b>$sessionno: $coursesession->sessiontitle </b></font><br><hr>
				<b>Date</b>: $coursesession->sessiondate <br>
				<b>Length</b>: $coursesession->sessionlength <br>
				<b>Type</b>: $coursesession->sessiontype <br>
				<b>Description</b>:<br> $coursesession->sessiondescription <br>
				<b>Guest teacher</b>: $coursesession->sessionguestteacher<hr>
EOD;
				$pdf->writeHTMLCell(0, 0, '', '', $courselectures, 0, 1, 0, true, '', true);
				$sessionno ++;
			}
		}
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	
	
	
//put course policy information into the pdf------------------------------------------
		$pdf->SetFont('times', 'B', 15);
		$pdf->Write(5, 'Policy', '', 0, 'L', true, 0, false, false, 0);
		
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	
	

	
// terminat$courseInstructorur file with TCPDF output------------------------------------------
		if ($optionno == 1){
			$pdf->Output('syllubus.pdf', 'I'); 
		}else if ($optionno == 2){
			$pdf->Output('syllubus.pdf', 'D');
		}
	}
	
}

?>
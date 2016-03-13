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

 		$mform->addElement('html','<form> generate syllabus: <input type="submit" 
				name="syllubusgenerate" value="generate"/></form>'); 
		
		
		if(isset($_POST['syllubusgenerate'])){

			$filename = $this->do_generate();
			//$this->sample_pdf();
/* 			$mform->addElement('html','<form> 					
					click following link for download: <br>
					<a href="#" download="'.$filename.'">download</a></form>'); */
			
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
	
	function do_generate(){
		global $CFG, $DB, $USER;
		global $course;
		$sessionnumber = 0;
		$readingnumber = 0;
		$instructoremail = '';
		$officehours = '';
		$officelocation = '';
		//$coursesessions = array();
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
			//echo "<script type='text/javascript'>alert('$sessionnumber');</script>";
			if($sessionnumber>0){
				$coursesessions = $DB->get_records('coursesession', array('courseid'=>$existCourseInfo->courseid));			
			}	
			$readingnumber = $DB->count_records('coursereadings', array('courseid'=>$existCourseInfo->courseid));
			if($readingnumber>0){
				$coursereadings = $DB->get_records('coursereadings', array('courseid'=>$existCourseInfo->courseid));
			}
			
			$courseobjectives = $DB->get_records('courseobjectives', array('courseid'=>$existCourseInfo->courseid));
		}
		
		
		
		
		
		
//start pdf generation===============================================================================		
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Olaf Lederer');
		$pdf->SetTitle('TCPDF Example');
		$pdf->SetSubject('TCPDF Tutorial');
		$pdf->SetKeywords('TCPDF, PDF, example, tutorial');
		 
		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
	// set font
	$pdf->SetFont('times', 'B', 20);
	
	// add a page
	$pdf->AddPage();
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
	Fall/Winter/Spring/Summer
	Course Weight: *3
EOD;
	$pdf->SetFont('times', '', 10);
	$pdf->Write(5, $courselogistics, '', 0, 'C', true, 0, false, false, 0);	
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$courselogistics = <<<EOD
	Coordinator: $courseInstructor
	Office: $officelocation, phone: $phonenumber
	Email: $instructoremail
	Office Hours: $officehours 
EOD;
	$pdf->SetFont('times', '', 10);
	$pdf->Write(0, $courselogistics, '', 0, 'C', true, 0, false, false, 0);
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
	
	
	
//put course session information into the pdf------------------------------------------
	$pdf->SetFont('times', 'B', 15);
	$pdf->Write(5, 'Grading', '', 0, 'L', true, 0, false, false, 0);

	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();

	
	
//put course session information into the pdf------------------------------------------
	$pdf->SetFont('times', 'B', 15);
	$pdf->Write(5, 'Course Sessions', '', 0, 'L', true, 0, false, false, 0);
	if($sessionnumber>0){
		$pdf->SetFont('times', '', 10);
		$sessionno = 1;
		foreach ($coursesessions as $coursesession) {
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
			$courselectures = <<<EOD
			<font size="10"><b>$sessionno: $coursesession->sessiontitle </b></font><br>
			Date: $coursesession->sessiondate <br>
			Length: $coursesession->sessionlength <br>
			Type: $coursesession->sessiontype <br>
			Description: $coursesession->sessiondescription <br>
			Guest teacher: $coursesession->sessionguestteacher
EOD;
			//$pdf->Write(8, $courselectures, '', 0, 'L', true, 0, false, false, 0);
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
	
	


		
	
	
	# terminat$courseInstructorur file with TCPDF output
	$pdf->Output('syllubusgenerate.pdf', 'I'); 
	$filename= "syllubusgenerate.pdf";
	return $filename;
	}
	
	
	
	
	
	function sample_pdf(){
	
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Nicola Asuni');
		$pdf->SetTitle('TCPDF Example 028');
		$pdf->SetSubject('TCPDF Tutorial');
		$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
	
		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
	
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	
		// set margins
		$pdf->SetMargins(10, PDF_MARGIN_TOP, 10);
	
		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	
		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	
		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
	
		// ---------------------------------------------------------
	
		$pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');
	
		// set font
		$pdf->SetFont('times', 'B', 20);
	
		$pdf->AddPage('P', 'A4');
		$pdf->Cell(0, 0, 'A4 PORTRAIT', 0, 1, 'C');
	
		$pdf->AddPage('L', 'A4');
		$pdf->Cell(0, 0, 'A4 LANDSCAPE', 1, 1, 'C');
	
		$pdf->AddPage('P', 'A5');
		$pdf->Cell(0, 0, 'A5 PORTRAIT', 1, 1, 'C');
	
		$pdf->AddPage('L', 'A5');
		$pdf->Cell(0, 0, 'A5 LANDSCAPE', 1, 1, 'C');
	
		$pdf->AddPage('P', 'A6');
		$pdf->Cell(0, 0, 'A6 PORTRAIT', 1, 1, 'C');
	
		$pdf->AddPage('L', 'A6');
		$pdf->Cell(0, 0, 'A6 LANDSCAPE', 1, 1, 'C');
	
		$pdf->AddPage('P', 'A7');
		$pdf->Cell(0, 0, 'A7 PORTRAIT', 1, 1, 'C');
	
		$pdf->AddPage('L', 'A7');
		$pdf->Cell(0, 0, 'A7 LANDSCAPE', 1, 1, 'C');
	
	
		// --- test backward editing ---
	
	
		$pdf->setPage(1, true);
		$pdf->SetY(50);
		$pdf->Cell(0, 0, 'A4 test', 1, 1, 'C');
	
		$pdf->setPage(2, true);
		$pdf->SetY(50);
		$pdf->Cell(0, 0, 'A4 test', 1, 1, 'C');
	
		$pdf->setPage(3, true);
		$pdf->SetY(50);
		$pdf->Cell(0, 0, 'A5 test', 1, 1, 'C');
	
		$pdf->setPage(4, true);
		$pdf->SetY(50);
		$pdf->Cell(0, 0, 'A5 test', 1, 1, 'C');
	
		$pdf->setPage(5, true);
		$pdf->SetY(50);
		$pdf->Cell(0, 0, 'A6 test', 1, 1, 'C');
	
		$pdf->setPage(6, true);
		$pdf->SetY(50);
		$pdf->Cell(0, 0, 'A6 test', 1, 1, 'C');
	
		$pdf->setPage(7, true);
		$pdf->SetY(40);
		$pdf->Cell(0, 0, 'A7 test', 1, 1, 'C');
	
		$pdf->setPage(8, true);
		$pdf->SetY(40);
		$pdf->Cell(0, 0, 'A7 test', 1, 1, 'C');
	
		$pdf->lastPage();
	
		// ---------------------------------------------------------
	
		//Close and output PDF document
		$pdf->Output('example_028.pdf', 'I');
	
	
	
	}
}

?>
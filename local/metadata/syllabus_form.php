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
	//$pdf->Write(5, $courselogistics, '', 0, 'C', true, 0, false, false, 0);	
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
	//$pdf->Write(0, $courselogistics, '', 0, 'C', true, 0, false, false, 0);
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
	
	
	
//put course grading information into the pdf------------------------------------------
	$pdf->SetFont('times', 'B', 15);
	$pdf->Write(5, 'Grading', '', 0, 'L', true, 0, false, false, 0);
	$pdf->SetFont('times', '', 10);
	$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
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
		$pdf->SetTitle('TCPDF Example 014');
		$pdf->SetSubject('TCPDF Tutorial');
		$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
		
		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 014', PDF_HEADER_STRING);
		
		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		
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
		
		// IMPORTANT: disable font subsetting to allow users editing the document
		$pdf->setFontSubsetting(false);
		
		// set font
		$pdf->SetFont('helvetica', '', 10, '', false);
		
		// add a page
		$pdf->AddPage();
		
		/*
		 It is possible to create text fields, combo boxes, check boxes and buttons.
		 Fields are created at the current position and are given a name.
		 This name allows to manipulate them via JavaScript in order to perform some validation for instance.
		 */
		
		// set default form properties
		$pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
		
		$pdf->SetFont('helvetica', 'BI', 18);
		$pdf->Cell(0, 5, 'Example of Form', 0, 1, 'C');
		$pdf->Ln(10);
		
		$pdf->SetFont('helvetica', '', 12);
		
		// First name
		$pdf->Cell(35, 5, 'First name:');
		$pdf->TextField('firstname', 50, 5);
		$pdf->Ln(6);
		
		// Last name
		$pdf->Cell(35, 5, 'Last name:');
		$pdf->TextField('lastname', 50, 5);
		$pdf->Ln(6);
		
		// Gender
		$pdf->Cell(35, 5, 'Gender:');
		$pdf->ComboBox('gender', 30, 5, array(array('', '-'), array('M', 'Male'), array('F', 'Female')));
		$pdf->Ln(6);
		
		// Drink
		$pdf->Cell(35, 5, 'Drink:');
		//$pdf->RadioButton('drink', 5, array('readonly' => 'true'), array(), 'Water');
		$pdf->RadioButton('drink', 5, array(), array(), 'Water');
		$pdf->Cell(35, 5, 'Water');
		$pdf->Ln(6);
		$pdf->Cell(35, 5, '');
		$pdf->RadioButton('drink', 5, array(), array(), 'Beer', true);
		$pdf->Cell(35, 5, 'Beer');
		$pdf->Ln(6);
		$pdf->Cell(35, 5, '');
		$pdf->RadioButton('drink', 5, array(), array(), 'Wine');
		$pdf->Cell(35, 5, 'Wine');
		$pdf->Ln(6);
		$pdf->Cell(35, 5, '');
		$pdf->RadioButton('drink', 5, array(), array(), 'Milk');
		$pdf->Cell(35, 5, 'Milk');
		$pdf->Ln(10);
		
		// Newsletter
		$pdf->Cell(35, 5, 'Newsletter:');
		$pdf->CheckBox('newsletter', 5, true, array(), array(), 'OK');
		
		$pdf->Ln(10);
		// Address
		$pdf->Cell(35, 5, 'Address:');
		$pdf->TextField('address', 60, 18, array('multiline'=>true, 'lineWidth'=>0, 'borderStyle'=>'none'), array('v'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'dv'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'));
		$pdf->Ln(19);
		
		// Listbox
		$pdf->Cell(35, 5, 'List:');
		$pdf->ListBox('listbox', 60, 15, array('', 'item1', 'item2', 'item3', 'item4', 'item5', 'item6', 'item7'), array('multipleSelection'=>'true'));
		$pdf->Ln(20);
		
		// E-mail
		$pdf->Cell(35, 5, 'E-mail:');
		$pdf->TextField('email', 50, 5);
		$pdf->Ln(6);
		
		// Date of the day
		$pdf->Cell(35, 5, 'Date:');
		$pdf->TextField('date', 30, 5, array(), array('v'=>date('Y-m-d'), 'dv'=>date('Y-m-d')));
		$pdf->Ln(10);
		
		$pdf->SetX(50);
		
		// Button to validate and print
		$pdf->Button('print', 30, 10, 'Print', 'Print()', array('lineWidth'=>2, 'borderStyle'=>'beveled', 'fillColor'=>array(128, 196, 255), 'strokeColor'=>array(64, 64, 64)));
		
		// Reset Button
		$pdf->Button('reset', 30, 10, 'Reset', array('S'=>'ResetForm'), array('lineWidth'=>2, 'borderStyle'=>'beveled', 'fillColor'=>array(128, 196, 255), 'strokeColor'=>array(64, 64, 64)));
		
		// Submit Button
		$pdf->Button('submit', 30, 10, 'Submit', array('S'=>'SubmitForm', 'F'=>'http://localhost/printvars.php', 'Flags'=>array('ExportFormat')), array('lineWidth'=>2, 'borderStyle'=>'beveled', 'fillColor'=>array(128, 196, 255), 'strokeColor'=>array(64, 64, 64)));
		
		// Form validation functions
		$js = <<<EOD
function CheckField(name,message) {
    var f = getField(name);
    if(f.value == '') {
        app.alert(message);
        f.setFocus();
        return false;
    }
    return true;
}
function Print() {
    if(!CheckField('firstname','First name is mandatory')) {return;}
    if(!CheckField('lastname','Last name is mandatory')) {return;}
    if(!CheckField('gender','Gender is mandatory')) {return;}
    if(!CheckField('address','Address is mandatory')) {return;}
    print();
}
EOD;
		
		// Add Javascript code
		$pdf->IncludeJS($js);
		
		// ---------------------------------------------------------
		
		//Close and output PDF document
		$pdf->Output('example_014.pdf', 'I');
	}
}

?>
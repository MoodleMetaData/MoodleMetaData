<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';
require_once $CFG->dirroot.'/lib/tcpdf/tcpdf.php';
require_once 'lib.php';

class reporting_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;
		
		// initialize the form.
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
 		$mform->addElement('html','<form> generate report: <br>
				in pdf format:
 				<input type="submit" name="reportdisplay" value="preview"/>
 				<input type="submit" name="reportdownload" value="download"/>
				<br>in csv format:
 				<input type="submit" name="reportcsv" value="download"/>
				</form>'); 
		
		
		if(isset($_POST['reportdisplay'])){

			$this->generatepdf(2);
		}
		if(isset($_POST['reportdownload'])){
		
			$this->generatepdf(1);
		}
		if(isset($_POST['reportcsv'])){
		
			$this->generatecsv();
		}
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
	
	public function generatepdf($optionno){
		//start pdf generation===============================================================================		
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		// set font
		$pdf->SetFont('times', 'B', 20);
		// add a page
		$pdf->AddPage();
		
		
		// terminate with TCPDF output------------------------------------------
		if ($optionno == 1){
			$pdf->Output('syllubus.pdf', 'I'); 
		}else if ($optionno == 2){
			$pdf->Output('syllubus.pdf', 'D');
		}
	}
	
	public function generatecsv(){
		
	}
}

?>
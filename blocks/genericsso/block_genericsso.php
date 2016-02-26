<?php

class block_genericsso extends block_base {
    function init() {
		
		if (isset ( $this->config->title)){
			$this->title = 	$this->config->title;
		} else {
			$this->title = get_string('pluginname', 'block_genericsso');
		}
    }
	function instance_allow_multiple() {
		return true;
	}
	function applicable_formats() {
		return array('all' => true);
	}
    function get_content() {
        global $CFG, $DB, $OUTPUT, $USER, $COURSE;
		if ($this->content !== NULL) {
			return $this->content;
		}
		$this->content         =  new stdClass;
		if (isset ( $this->config->title)){
			$this->title = 	$this->config->title;
		} else {
			$this->title = get_string('pluginname', 'block_genericsso');
		}
		if (! empty($this->config->sharedsecret)) {
			$secret = $this->config->sharedsecret;
		} else {
			$secret = 'somesecret';
		}
		//$secret = 'sdas';//$this->config->sharedsecret;
//get encryption key from shared secret
$key = substr(md5($secret), 0, 16);


$ccid=base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $USER->idnumber, MCRYPT_MODE_ECB));
$firstname=base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $USER->firstname, MCRYPT_MODE_ECB));
$lastname=base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $USER->lastname, MCRYPT_MODE_ECB));
$studentid=base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $USER->username, MCRYPT_MODE_ECB));
$sectionid=base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $COURSE->idnumber, MCRYPT_MODE_ECB));

		//$this->content->text=$this->config->url;
		if (isset ($this->config->newwindow) && $this->config->newwindow==1) {
			$target='_blank';
		} else {
			$target='_self';
			
		}
		
		if (isset($this->config->buttontext)){
			$buttontext=$this->config->buttontext;
		} else { 
			$buttontext='Access external system';
		}
		
		if (! empty($this->config->sharedsecret)) {
			$this->content->text='<form action="'.$this->config->url.'" method="POST" target="'.$target.'"><input type=hidden name="ccid" value="'.$ccid.'"><input type=hidden name="firstName" value="'.$firstname.'"><input type=hidden name="lastName" value="'.$lastname.'"><input type=hidden name="studentId" value="'.$studentid.'"><input type=hidden name="sectionId" value="'.$sectionid.'"><input type=hidden name="timestamp" value="'.time().'"><input type=hidden name="hash" value="caaadc77c4ce61dbd8b41f81431a1630"><input type=submit name="submit" value="'.$buttontext.'"></form>';
		} else {
		$this->content->text = 'This Block needs to be configured.';
		}
        return $this->content;
    }


	
	
	function instance_config_save($data, $nolongerused = false) {
		global $DB;

		$config = clone($data);
		parent::instance_config_save($config, $nolongerused);
	}
}



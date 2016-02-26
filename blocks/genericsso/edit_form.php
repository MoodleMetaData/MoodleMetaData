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
class block_genericsso_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_genericsso'));
        $mform->addRule('config_title', null, 'required', null, 'client');
        $mform->setType('config_title', PARAM_MULTILANG);
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $mform->addElement('editor', 'config_text', get_string('configcontent', 'block_genericsso'), null, $editoroptions);
        $mform->addRule('config_text', null, 'required', null, 'client');
        $mform->setType('config_text', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.
        $mform->addElement('advcheckbox', 'config_newwindow', get_string('newwindow', 'block_genericsso'), '',
            array('group' => 1), array(0, 1));
        $mform->addElement('text', 'config_url', get_string('url', 'block_genericsso'));
        $mform->addRule('config_url', null, 'required', null, 'client');
        $mform->setType('config_url', PARAM_URL);
        $mform->addElement('password', 'config_sharedsecret', get_string('sharedsecret', 'block_genericsso'));
        $mform->addRule('config_sharedsecret', null, 'required', null, 'client');
        $mform->addElement('text', 'config_buttontext', get_string('buttontext', 'block_genericsso'));
        $mform->setType('config_buttontext', PARAM_TEXT);
    }
}
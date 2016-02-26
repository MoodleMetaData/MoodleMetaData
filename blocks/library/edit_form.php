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
 * A custom configuration form that extends the block_edit_form and
 * is used by the generic library block.
 *
 * @package library
 * @author Josh Stagg jstagg@ualberta.ca
 **/
class block_library_edit_form extends block_edit_form{

    protected function specific_definition( $mform ) {
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block_library'));

        $mform->addElement('advcheckbox', 'config_enableaskbubble',
                            get_string('enableaskbubble', 'block_library'),
                            get_string('enableaskbubbledesc', 'block_library'));
        $mform->setDefault('config_enableaskbubble', 1);

        $mform->addElement('advcheckbox', 'config_enablesearch',
                            get_string('enablesearchbox', 'block_library'),
                            get_string('enablesearchboxdesc', 'block_library'));
        $mform->setDefault('config_enablesearch', 1);

        $mform->addElement('advcheckbox', 'config_enablelibrarylinks',
            get_string('enablelibrarylinks', 'block_library'),
            get_string('enablelibrarylinksdesc', 'block_library'));
        $mform->setDefault('config_enablelibrarylinks', 1);

        $mform->addElement('advcheckbox', 'config_enablecourselinks',
            get_string('enablecourselinks', 'block_library'),
            get_string('enablecourselinksdesc', 'block_library'));
        $mform->setDefault('config_enablecourselinks', 0);

        $mform->addElement('editor', 'config_rawhtml', get_string('librarylinksettings', 'block_library'), array(
            'subdirs'=>0,
            'maxbytes'=>0,
            'maxfiles'=>0,
            'changeformat'=>0,
            'context'=>null,
            'noclean'=>0,
            'trusttext'=>0));
        $mform->setType('fieldname', PARAM_RAW);
    }

}
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

defined('MOODLE_INTERNAL') || die();

/**
 * This is the label info class that ties together a few arrays so that they are
 * linked together.  I've kept it fairly small, so that it can be passed to
 * javascript quickly.
 *
 * Very briefly, this class contains a list of all modules in a section and the
 * adjusted indent values that they should be set to ($moddepth).
 *
 * @package    format_collblct
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_depths {
    /** list of IDs for each module in the course */
    public $modid;
    /** the adjusted indent value that the mod should be set to */
    public $moddepth;

    /**
     * The constructor is pretty straightforward -> initialize the arrays.
     *
     */
    public function __construct() {
        $this->modid = array();
        $this->moddepth = array();
    }
}
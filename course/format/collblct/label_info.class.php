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
 * Very briefly, the class holds a list of label IDs for the opening tags and the
 * ID of the module that it should close at ($closeid).  It also contains the info
 * that is included in the deptharray (indices and values).  All of this info is
 * used by the javascript code to construct the collapsible labels.
 *
 * @package    format_collblct
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class label_info {

    /** this is the completed list of label IDs */
    public $labelid;
    /** this contains the close IDs for each label as a string */
    public $closeid;
    /** array containing the depth indices {-1, 0, 2, ...} */
    public $depthindex;
    /** array containing the indent values for each particular depth level */
    public $depthvalue;

    /**
     * The constructor is pretty straightforward -> initialize the arrays.
     *
     */
    public function __construct() {
        $this->labelid = array();
        $this->closeid = array();
        $this->depthindex = array();
        $this->depthvalue = array();
    }

    /**
     * This method flips the last elements (from $reverseindex forward) of
     * all of the label_info arrays.
     *
     * @param int $reverseindex The arrays are flipped after this position.
     *
     */
    public function reverse_arrays_from_index($reverseindex) {
        // Close ID can be skipped, it is assumed that all the last entries in this array are the same.
        $this->reverse_array_from_index($this->labelid, $reverseindex);
        $this->reverse_array_from_index($this->depthindex, $reverseindex);
        $this->reverse_array_from_index($this->depthvalue, $reverseindex);
    }

    /**
     * This method flips the last elements (from $reverseindex forward) of
     * the array that it is passed ($arraytoreverse).
     *
     * @param array $arraytoreverse This is the array to reverse the end of.
     * @param int $reverseindex The array is reversed from this index forward.
     *
     */
    private function reverse_array_from_index(&$arraytoreverse, $reverseindex) {
        $temp = array_slice($arraytoreverse, $reverseindex);
        $reverse = array_reverse($temp);

        for ($i = 0; $i < count($arraytoreverse) - $reverseindex; $i++) {
            $arraytoreverse[$i + $reverseindex] = $reverse[$i];
        }
    }
}
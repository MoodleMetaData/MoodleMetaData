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
require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once('label_info.class.php');
require_once('mod_depths.class.php');

define("YOUTUBELINK", "www.youtube.com/embed");

/**
 * This is the accordion menu class that creates nested menus.  For an example
 * of how to use it, check out the format.php class.
 *
 * I have had to change the structure of this class because Moodle made some internal
 * changes so that the sequence variable is no longer used.  The code for parsing the
 * mods is similar to before (in terms of cases), but now I simply keep track of where
 * each label opens and closes by using a stack.
 *
 * Prior to parsing each of the mods for the rendering information, the mods are
 * iterated through once to created a deptharray.  This deptharray is what allows
 * the plugin to handle different levels of nesting.
 *
 * The actual creation of the collapsible labels is now handled by javascript.  This
 * class computes all of the necessary information and then sends it to javascript
 * via a function call.
 *
 * @package    format_collblct
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class collapsed_menu{
    /** This is the header level to draw the first level of nesting at (<h4>) */
    const HEADER_START = 7;
    /** This is a collection of text strings to search for to skip the label - Moodle's HTML editor will generally reformat into
     ** the second item */
    private $skiplabel = array("class='skiplabel'", "class=\"skiplabel\"", "class = 'skiplabel'", "class = \"skiplabel\"");

    /** Is this the first label at this level of nesting? */
    public $firstlabel;
    /** This is the course object */
    private $course;
    /** I added this to be able to do unit tests without always displaying the mods */
    private $enableprint;
    /** print_section uses this, not sure what for */
    private $displaysection;
    /** this is the depth array */
    private $deptharray;
    /** this is a temporary stack containing label IDs */
    private $labelstack;
    /** another stack, but stores the depth of the current label */
    private $depthstack;
    /** this object gets sent to javascript to setup the HTML for the labels */
    private $labelinfo;
    /** this object gets sent to javascript to adjust the indents */
    private $moddepths;

    /**
     * This function constructs and sets up the accordion menu.
     *
     * @param object $course This is course object.
     * @param int $displaysection The numerical value of the current section.
     * @param object $enableprint This can be turned off for unittests.
     *
     */
    public function __construct(&$course, $displaysection, $enableprint) {
        // This should probably happen directly in the javascript now.
        if ($enableprint) {
            echo '<div id="acc'.$displaysection.'" class="accordion">';
        }

        $this->course = $course;
        $this->enableprint = $enableprint;
        $this->displaysection = $displaysection;

        // Setup our state variables.
        $this->firstlabel = true;
        $this->labelstack = array();
        $this->depthstack = array();
        $this->labelinfo = new label_info();
        $this->moddepths = new mod_depths();
    }

    /**
     * This is the public facing method that someone using this class
     * should call.  Internally, it does the double pass through the mod
     * list (first computing depths, then second computing the rendering
     * information), then the collapsed label information is sent to a
     * javascript function.
     *
     */
    public function render_menu() {
        global $PAGE;

        $this->calculate_depths();
        $this->parse_mods();

        // Check last close tag to see if reversing is needed (special case).  More details found in function.
        $temp = $this->check_last_close_tag();
        if ($temp != -1) {
            $this->reverse_close_tags($temp);
        }

        // Setup the JS call.
        if ($this->enableprint) {
            $PAGE->requires->js_function_call('setup_nested_section', array(json_encode($this->labelinfo),
                                              json_encode($this->moddepths), self::HEADER_START, $this->displaysection), false);
        }

    }

    /**
     * This function is responsible for any cleanup needed after all the mods
     * are printed.  Currently this is just the closing div tag.
     *
     */
    public function close_menu() {
        // TODO: should be some sort of closing div here.
    }

    /**
     * This method returns the labelinfo member.  I've added this mostly
     * for testing purposes, although it could be useful in other cases.
     *
     */
    public function get_label_info() {
        return $this->labelinfo;
    }

    /**
     * This method returns the moddepths member.  I've added this mostly
     * for testing purposes, although it could be useful in other cases.
     *
     */
    public function get_mod_depths() {
        return $this->moddepths;
    }

    /**
     * This method calculates the valid depths that are used by going through
     * the mod list once prior to any other operations occuring.  It computes
     * all of the various depths and their associated indents by itering
     * through all of the labels.  This makes some of the later computation a
     * little more straightforward to perform and read.
     *
     */
    private function calculate_depths() {
        $this->deptharray = array();
        $this->deptharray[] = -1; // Indent -1 holds non-label items.

        $modinfo = get_fast_modinfo($this->course);
        // This step seems silly, but the section part of modinfo is empty if there are no mods.
        if (!empty($modinfo->sections[$this->displaysection])) {
            foreach ($modinfo->sections[$this->displaysection] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];
                if (($mod->sectionnum == $this->displaysection) && $mod->visible) {
                    if ($mod->modfullname == "Label") {
                        $this->deptharray[] = intval($mod->indent);
                    }
                }
            }
        }
        // Change the array to be unique values only and sorted.
        $this->deptharray = array_values(array_unique($this->deptharray));
        asort($this->deptharray);
    }

    /**
     * This method prepares the rendering information by parsing all the mods
     * in the mod list.  This information is then passed to do a javascript function
     * that adjusts the html tags so that the accordion menu can be created.
     *
     */
    private function parse_mods() {
        // Indent level of -1 is where mods not under a label go.
        $currentindent = -1;

        $modinfo = get_fast_modinfo($this->course);
        // This step seems silly, but the section part of modinfo is empty if there are no mods.
        if (!empty($modinfo->sections[$this->displaysection])) {
            foreach ($modinfo->sections[$this->displaysection] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];
                if (($mod->sectionnum == $this->displaysection) && $mod->visible) {
                    if ($mod->modfullname == "Label") {
                        if ($this->check_for_label_exception($mod)) {
                            $this->parse_non_label($mod, $currentindent);
                        } else {
                            $this->parse_label($mod, $currentindent);
                        }
                    } else {
                        $this->parse_non_label($mod, $currentindent);
                    }
                }
            }
        }
        // Some final cleanup -> close any open labels.
        $currentdepth = array_search($currentindent, $this->deptharray);
        for ($i = 0; $i < $currentdepth; $i++) {
            $this->close_label($mod->id, true);
        }
    }

    /**
     * This method checks for any label exceptions that should be rendered
     * normally.  Currently, this just involves looking for a youtube link.
     *
     * @param object $mod The mod to check for an exception
     * @return bool T/F indicating exception found (T) or not (F)
     *
     */
    private function check_for_label_exception($mod) {
        // Check for youtube link.
        $result = strpos($mod->content, YOUTUBELINK);

        // Check for html class requesting the label be skipped.
        if ($result === false) {
            for ($i = 0; $i < count($this->skiplabel); $i++) {
                // This looks very awkward, but the !== does not work quite the way one would expect.
                if (strpos($mod->content, $this->skiplabel[$i]) === false) {
                    continue;
                } else {
                    $result = true;
                    break;
                }
            }
        }

        return ($result === false) ? false : true;
    }

    /**
     * This method sets up the bookkeeping arrays to open a label.  The values
     * that are necessary when the label is closed are added to each of the two
     * stacks {labelstack, depthstack}.
     *
     * @param int $id This is the ID of the label to start the collapsed menu.
     * @param int $depth This is the depth index of the label.
     *
     */
    private function open_label($id, $depth) {
        $this->labelstack[] = $id;
        $this->depthstack[] = $depth;
    }

    /**
     * This method sets up the bookkeeping arrays to close a label.  The needed
     * values are pulled off each of the stacks and then added to the classes
     * that will later be passed to the javascript function.
     *
     * @param int $id This is the ID of the mod that will end the collapsed menu.
     * @param bool $inclusive T -> collapsed menu includes this mod, F -> collapsed menu closes before this mod.
     *
     */
    private function close_label($id, $inclusive) {
        $temp = array_pop($this->labelstack);
        // Protect against invalid structures with inappropriate closing.
        if (isset($temp)) {
            $this->labelinfo->labelid[] = $temp;
            $this->labelinfo->closeid[] = $inclusive ? "I$id" : "N$id";
        }
        $temp = array_pop($this->depthstack);
        if (isset($temp)) {
            $this->labelinfo->depthindex[] = $temp;
            $this->labelinfo->depthvalue[] = $this->deptharray[$temp];
        }
    }

    /**
     * This method parses a label and adds information to the render information
     * arrays that are later given to javascript to setup the collapsed labels.
     *
     * @param object $mod This is the current mod (passed by ref for speed, no changes should be made)
     * @param int $currentindent This is the current indent level (passed by ref, value is updated)
     *
     */
    private function parse_label(&$mod, &$currentindent) {
        // Write label info to render array.
        $this->write_label_renderinfo($mod, $currentindent);
        // Update indent.
        $currentindent = $mod->indent;
    }

    /**
     * This method writes the rendering information to the bookkeeping arrays by handling
     * the three major cases:
     * 1) First label or a new nesting level: open a new menu.
     * 2) Subsequent label at the same set of indent.  This is fairly easy: close the old, open the new.
     * 3) Indent change to indicate an inwards movement: close all open menus and then open the new menu.
     *
     * @param object $mod This is the current mod (passed by ref for speed, no changes should be made)
     * @param int $currentindent This is the current indent level (passed by ref, value is updated)
     *
     */
    private function write_label_renderinfo(&$mod, &$currentindent) {
        // Calculate depth.
        $depth = array_search($mod->indent, $this->deptharray);
        // Moving outwards -> nesting, so old label is not closed.
        if (($mod->indent > $currentindent) || $this->firstlabel) {
            // Add the label to the render array.
            $this->open_label($mod->id, $depth);
            $this->firstlabel = false;
        } else if ($mod->indent == $currentindent) {
            // Same level of nesting, close previous + open new.
            // Close previous label.
            $this->close_label($mod->id, false);

            // Add the label to the render array.
            $this->open_label($mod->id, $depth);
        } else {
            // Moving inwards [close one or more menus].
            $currentdepth = array_search($currentindent, $this->deptharray);
            $depthchange = $currentdepth - $depth;
            for ($i = 0; $i <= $depthchange; $i++) {
                // Close previous label.
                $this->close_label($mod->id, false);

            }
            // And open up the current label.
            $this->open_label($mod->id, $depth);
        }
    }

    /**
     * This method parses a non-label and adds information to the render information
     * arrays that are later given to javascript to setup the collapsed labels.
     *
     * @param object $mod This is the current mod (passed by ref for speed, no changes should be made)
     * @param int $currentindent This is the current indent level (passed by ref, value is updated)
     *
     */
    private function parse_non_label(&$mod, &$currentindent) {
        // Always calculate rounded indent -> needed to adjust indent of each mod.
        $roundedindent = $this->calc_rounded_indent($mod->indent);

        if ($mod->indent <= $currentindent) {
            // Calculate new depth.
            $depth = array_search($roundedindent, $this->deptharray);
            // Save old depth.
            $currentdepth = array_search($currentindent, $this->deptharray);
            /* This loop is slightly different than the other, since one less label needs to
             * be closed.  Do not try to write both in the same loop. */
            $depthchange = $currentdepth - $depth;
            for ($i = 0; $i < $depthchange; $i++) {
                // Close previous label.
                $this->close_label($mod->id, false);
            }
            // Update indent level.
            $currentindent = $roundedindent;
        }

        // Store mod ID and proper depth for javascript function.
        $this->moddepths->modid[] = $mod->id;
        $this->moddepths->moddepth[] = $mod->indent - $roundedindent - 1;
    }

    /**
     * This method computes a "rounded" indent value.  The deptharray member holds a
     * series of indent values corresponding to the indent levels of all the labels.  A
     * non-label can fall somewhere between these indent values, so this function rounds
     * the indent value to a valid label value.  The rounded indent value is the one that
     * is closest to the actual indent value witout going over (price is right style!).
     *
     * @param int $indent This is the indent value to round
     * @return int Rounded indent value
     *
     */
    private function calc_rounded_indent($indent) {
        // We have to "round" the indent to the most valid entry in the depth array, defaulting to zero.
        $roundedindent = -1;
        for ($i = 0; $i < count($this->deptharray); $i++) {
            if ($indent > $this->deptharray[$i]) {
                $roundedindent = $this->deptharray[$i];
            }
        }
        return $roundedindent;
    }

    /**
     * If multiple labels close on the last element, the "inclusive" flag alone
     * is not sufficient.  In order to close the labels properly, they have to
     * close from outermost to innermost.  This function checks to see if this
     * condition holds in the current label_info object.
     *
     * @return int {-1} denotes no reversing necessary, {0+} denotes position where reversing must begin.
     *
     */
    private function check_last_close_tag() {
        $length = isset($this->labelinfo->labelid) ? count($this->labelinfo->labelid) : 0;
        if ($length == 0) {
            return -1;
        }
        $lastclosetag = $this->labelinfo->closeid[$length - 1];
        $lastduplicate = $length;

        // Find out how many levels closed on the last element.
        for ($i = $length - 2; $i > -1; $i--) {
            if ($this->labelinfo->closeid[$i] == $lastclosetag) {
                $lastduplicate = $i;
            } else {
                // Break on first closing tag that doesn't match.
                break;
            }
        }

        // If multiple levels closed, then swap the order.
        return ($lastduplicate != $length) ? $lastduplicate : -1;
    }

    /**
     * This method is a wrapper for the label_info function that reverses
     * the order of the array from $reverseindex forward.
     *
     * @param int $reverseindex Reverse the label_info arrays from this index forward.
     *
     */
    private function reverse_close_tags($reverseindex) {
        $this->labelinfo->reverse_arrays_from_index($reverseindex);
    }

}
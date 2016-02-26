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
* This file contains initialization functions for the accordion menus. I
* have changed the color_init function so that it simply stores the values
* in global variables.  These colors are applied later when the accordion
* menu is being constructed.
*
* @package    format_collblct
* @category   course/format
* @copyright  2012 Craig Jamieson
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

//<!--//--><![CDATA[//><!--
$("html").addClass("js");
$.fn.accordion.defaults.container = false;
$(function ()
{
    var totalsections = 30;

    for (i = 0; i < totalsections; i++)
    {
        stringnum = i.toString();
        $("#acc" + stringnum).accordion({
            obj: "div",
            wrapper: "div",
            el: ".h",
            head: "h7, h8, h9, h10, h11, h12",
            next: "div",
            showMethod: "show",
            hideMethod: "hide",
            standardExpansible: true,
            initShow: "#current"
        });
    }
    $("html").removeClass("js");
});

var background_color;
var foreround_color;

/**
 * This function is called directly from the php file to initialize some globals.  
 * I used to apply the colors via the .css() function here directly, but the 
 * accordion menu is created a bit later in Moodle 2.4.  Instead, I store the values
 * in globals and then they are applied later in jquery.nestedAccordion.js.
 *
 */
function color_init(moodlebackgroundcolor, moodleforegroundcolor)
{
	background_color = moodlebackgroundcolor;
	foreground_color = moodleforegroundcolor;
}
//--><!]]>
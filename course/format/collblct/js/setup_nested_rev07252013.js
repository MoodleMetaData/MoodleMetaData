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
* This file contains the definition for the inbox iframe and the event handlers that are used for
* the block.
*
* @package    format_collblct
* @category   course/format
* @copyright  2012 Craig Jamieson
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

/**
 * This function is responsible for adding the html tags so that the collapsible
 * labels can be created.  This function is called directly by Moodle and the values
 * that it receives are JSON encoded from PHP.  The tags for each of the labels are
 * added in turn and then indent values are adjusted.
 *
 * The commented alert function is just a way to double check to ensure that a
 * non-cached version of the javascript is not being loaded.
 *
 */
function setup_nested_section(jsonlabelinfo, jsonmoddepths, header_start, sectionnumber)
{
	var labelinfo = jQuery.parseJSON(jsonlabelinfo);
	var moddepths = jQuery.parseJSON(jsonmoddepths);
	
	for(var i = 0; i < labelinfo.labelid.length; i++)
	{
		var opentag = '#module-' + labelinfo.labelid[i];
		var closetag = 'module-' + labelinfo.closeid[i].substr(1);
		var labelname = $(opentag).find('p').text();
		var headertag = header_start + labelinfo.depthindex[i] - 1;
		$(opentag).before('<h' + headertag.toString() + '>' + labelname + '</h' + headertag.toString() + '>');
		var closedom = document.getElementById(closetag);
		if(labelinfo.closeid[i].charAt(0) == 'N')
		{
			$(opentag).nextUntil(closedom).andSelf().wrapAll('<div class="inner" />');
		}
		else
		{
			$(opentag).nextUntil(closedom).andSelf().add(closedom).wrapAll('<div class="inner" />');
		}
		$(opentag).remove();
	}
	
	remove_all_mod_indents(sectionnumber);
	add_correct_mod_indents(moddepths);
}

/**
 * This function removes all mod-indent values from the modules that already exist
 * on the screen.  Moodle currently has definitions for up to 15 levels of indent,
 * so I have choosen to remove them all.  I am somewhat concerned that is function
 * might be a little slow.
 *
 */
function remove_all_mod_indents(sectionnumber)
{
	var total_indents_to_remove = 15;

	for(var i = 1; i < total_indents_to_remove; i++)
	{
		var classname = 'mod-indent-' + i.toString();
		var selector = '#section-' + sectionnumber + ' .accordion div';
		$(selector).removeClass(classname);
	}
}

/**
 * This function applies the updated indent levels to each of the modules that exist
 * on the screen.  The list of all modules (modid) and the proper depths (moddepth) 
 * are passed in the moddepths object.
 *
 */
function add_correct_mod_indents(moddepths)
{
	for(var i = 0; i < moddepths.modid.length; i++)
	{
		var idtag = '#module-' + moddepths.modid[i];
		var indent = moddepths.moddepth[i];
		if(indent >= 1)
		{
			$(idtag).find(".mod-indent").addClass("mod-indent-" + indent.toString());
		}

	}
}
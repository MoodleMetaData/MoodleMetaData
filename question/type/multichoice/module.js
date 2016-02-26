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
 * JavaScript required by the multichoice question type.
 *
 * @package    qtype
 * @subpackage multichoice
 * @copyright  2015 UofA
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.qtype_multichoice = M.qtype_multichoice || {};


M.qtype_multichoice.init = function (Y) {
    if(!M.qtype_multichoice.loaded) {
        M.qtype_multichoice.Y = Y;
        var buttons = Y.all('.mcstrike');
        var strike = function (e) {
            var button = e.target;

            var elem = button.get('previousSibling');

            if (elem.get('tagName') == 'LABEL') {
                if (elem.getStyle('textDecoration') == 'line-through') {
                    elem.setStyle('textDecoration', 'none');
                    button.set('text', M.util.get_string('crossout', 'qtype_multichoice'));
                }
                else {
                    elem.setStyle('textDecoration', 'line-through');
                    button.set('text', M.util.get_string('undo', 'qtype_multichoice'));
                }
            }

            // Prevent bubbling from occurring.
            e.stopImmediatePropagation();
            e.stopPropagation();
            e.preventDefault();
        }

        buttons.on('click', strike);
        M.qtype_multichoice.loaded = true;
    }
}

M.qtype_multichoice.loaded = false;
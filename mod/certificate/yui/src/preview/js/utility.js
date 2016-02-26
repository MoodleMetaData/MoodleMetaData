// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Certificate preview javascript module
 * This handles the update of preview in mod_form.php
 *
 * Just some miscellaneous javascript codes.
 *
 * @package    mod_certificate
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

Y.namespace('M.mod_certificate').utility = {
    /**
     * Aspect ratio enumeration.
     */
    aspect_ratio: {
        a4_aspect_ratio: 1.414,
        letter_aspect_ratio: Math.sqrt(2)
    },

    /**
     * Orientation enumeration.
     */
    orientation: {
        landscape: 0,
        portrait: 1
    }
};

Object.freeze(Y.namespace('M.mod_certificate').utility.aspect_ratio);
Object.freeze(Y.namespace('M.mod_certificate').utility.orientation);
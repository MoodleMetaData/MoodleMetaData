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
 * @param qs The string to look for. Defaults to document.location.search
 * @returns {{}} Object containing mapping of url ids and their corresponding value.
 */

module.exports = {
    get_query_params: function (qs) {
        qs = qs || document.location.search;
        qs = qs.split('+').join(' ');

        var params = {},
            tokens,
            re = /[?&]?([^=]+)=([^&]*)/g;

        while (tokens = re.exec(qs)) {
            params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
        }

        return params;
    },

    current_user_types: [
        "student",
        "teacher",
        "admin",

        "access_denied"
    ],

    current_user_type: "student",

    query_delay: 500  // Only query if no typing for some ms. This is to avoid query per key press.
};
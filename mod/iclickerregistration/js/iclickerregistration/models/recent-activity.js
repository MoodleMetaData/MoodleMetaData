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
 * Deals with the mod_iclickerregistration's view.php front end.
 *
 * @package   mod_iclickerregistration
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * iClicker model/resource. This implements all the REST methods. Operational methods
 * are simply placed as parameter argument.
 */

var utility = require('../utility');

module.exports = function(app) {
    var model_name = 'M.mod_iclickerregistration.models.RecentActivity';
    app.factory(model_name,
        ['$resource',
            function($resource) {
                return $resource('rest.php/recent-activity/:courseid',
                    {
                        course_id: '@courseid'
                    },
                    {
                        'get':    {method:'GET'}
                    });
            }]);

    return model_name;
};
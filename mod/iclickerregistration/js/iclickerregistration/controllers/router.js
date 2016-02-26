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

var utility = require('../utility');

module.exports = function(app) {
    app.config(['$routeProvider',
        function ($routeProvider) {
            var default_route = '/';
            switch (utility.current_user_type) {
                case 'student':
                    default_route = '/iclicker-info';
                    break;
                case 'teacher':
                    default_route = '/teacher-view-iclicker-info';
                    break;
                case 'admin':
                    default_route = '/admin-view-iclicker-info';
                    break;
                case 'access_denied':
                    default_route = '/access-denied-non-redirect';
                    break;
                default:
                    default_route = '/iclicker-info';
            }

            $routeProvider.
                when('/iclicker-info', {
                    template: require('../views/iclicker-info.html'),
                    controller: require('./iclicker-info')(app)
                }).
                when('/admin-view-iclicker-info', {
                    template: require('../views/admin-view-iclicker-info.html'),
                    controller: require('./admin-view-iclicker-info')(app)
                }).
                when('/teacher-view-iclicker-info', {
                    template: require('../views/teacher-view-iclicker-info.html'),
                    controller: require('./teacher-view-iclicker-info')(app)
                }).
                when('/edit-iclicker-info/:idnumber', {
                    template: require('../views/edit-iclicker-info.html'),
                    controller: require('./edit-iclicker-info')(app)
                }).
                when('/register-iclicker-info/:idnumber', {
                    template: require('../views/register-iclicker-info.html'),
                    controller: require('./register-iclicker-info')(app)
                }).
                when('/access-denied', {
                    template: require('../views/access-denied.html'),
                    controller: require('./access-denied')(app)
                }).
                when('/access-denied-non-redirect', {
                    templateUrl: require('../views/access-denied.html'),
                    controller: require('./access-denied-non-redirect')(app)
                }).
                otherwise({redirectTo: default_route});
        }]);
};
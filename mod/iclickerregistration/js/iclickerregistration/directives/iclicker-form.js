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
 * iclickerInfo directive. This will allow us to reuse iclicker-info.html
 * as <iclicker-info></iclicker-info> tag.
 */
module.exports = function(app) {
    app.directive('iclickerForm',
        ['$location', require('../models/iclicker')(app),
            function ($location, iClicker) {
                // return the directive definition object
                return {
                    // only match this directive to element tags
                    restrict: 'E',

                    template: require('../views/partials/iclicker-form.html'),

                    scope: {
                        lang: '=',
                        submitCallBack: '=',
                        cancelCallBack: '=',
                        inputErrors: '=',
                        inputSuccess: '=',
                        submitText: '=',
                        cancelText: '=',
                        inputTextPlaceHolder: '=',
                        inputValue: '='
                    },

                    link: function(scope, element, attrs) {
                        var default_cancel_cb = function () { window.history.back(); };
                        scope.cancelCallBack = default_cancel_cb;
                    }
                }
            }]);

};
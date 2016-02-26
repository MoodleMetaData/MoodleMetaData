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
    app.directive('iclickerInfo',
        ['$uibModal', '$location',
            require('../models/iclicker')(app),
            function ($uibModal, $location, iClicker) {
                // return the directive definition object
                return {
                    // only match this directive to element tags
                    restrict: 'E',

                    template: require('../views/partials/iclicker-info.html'),

                    scope: {
                        lang: '=',
                        iclickerUser: '=',
                        manuallyEnrolled: '=',

                        // These callbacks are optional. Provide at your own risk.
                        unregisterCallBack: '=',
                        editCallBack: '=',
                        registerCallBack: '='
                    },

                    link: function(scope, element, attrs) {
                        var default_unregister_cb = function() {
                            var modal_instance = $uibModal.open({
                                animation: true,
                                template: require('../views/delete-iclicker-id-modal-dialog.html'),
                                size: 'sm',
                                windowTopClass: 'mod-iclickerregistration-delete-confirmation-dialog',
                                controller: require('../controllers/delete-iclicker-id-modal-dialog')(app)
                            });

                            modal_instance.result.then(function() {
                                iClicker.delete({idnumber: 'current_user'},
                                    function(response) {
                                        if (response.status === 0) {
                                            $location.url('/').replace();  // Go to default page.
                                            return;
                                        }
                                    });
                            }, function() {
                                // Cancel deletion.
                                return;
                            });
                        };

                        scope.unregisterCallBack =
                            scope.unregisterCallBack || default_unregister_cb;

                        var default_edit_cb = function() {
                            $location.url('/edit-iclicker-info/current_user');
                            return;
                        }

                        scope.editCallBack =
                            scope.editCallBack || default_edit_cb;

                        var default_register_cb = function() {
                            $location.url('/register-iclicker-info/current_user');
                            return;
                        }

                        scope.registerCallBack =
                            scope.registerCallBack || default_register_cb;
                    }
                };
            }]);
};
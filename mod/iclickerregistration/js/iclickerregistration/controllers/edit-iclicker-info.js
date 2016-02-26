/**
 * EditiClickerInfo controller, handles editing of iclicker id.
 *
 * Use case(s):
 * * User already have an iclicker registred: Proceed "normally".
 * * User don't have an iclicker registered: Switch to registration controller and view.
 */
var utility = require('../utility');

module.exports = function(app) {
    var controller_name = 'M.mod_iclickerregistration.controllers.EditiClickerInfo';
    app.controller(
        controller_name,
        ['$scope', '$location', '$routeParams', '$timeout',
            require('../models/iclicker')(app),
            require('../models/user')(app),
            function ($scope, $location, $routeParams, $timeout, iClicker, User) {
                $scope.lang = M.str.mod_iclickerregistration;
                $scope.input_success = null;
                $scope.input_errors = [];

                // See if the user already exist, if so, switch to a register controller/view.
                $scope.current_user_iclicker = iClicker.get({idnumber: $routeParams.idnumber},
                    function (current_user_iclicker) {
                        var not_registered = !current_user_iclicker.iclicker_id;
                        if (not_registered) {
                            $location.url('/register-iclicker-info/' + $routeParams.idnumber).replace();
                            return;
                        }
                    });

                // Acquire information about the user being edited for aesthetic reasons (e.g. display name and stuff).
                $scope.current_user = User.get({idnumber: $routeParams.idnumber});

                $scope.update_cb = function (iclicker_id) {
                    $scope.current_user_iclicker.iclicker_id = iclicker_id;

                    // Exit when iclicker_id is not valid in the first place.
                    var iclicker_id = $scope.current_user_iclicker.iclicker_id || "";

                    iClicker.update($scope.current_user_iclicker,
                        function (update_result) {
                            $scope.input_errors = [];
                            switch (update_result.status) {
                                case 0:
                                    $scope.input_success = $scope.lang.registrationsuccess;
                                    $timeout(function () {
                                        $location.url('/');
                                    }, 1000);
                                    break;
                                case "access denied":
                                    $location.path('/access-denied');
                                    break;
                                case "duplicate iclicker_id in same course":
                                    $scope.input_errors.push($scope.lang.duplicateiclickeridinsamecourse);
                                    break;
                                case "invalid iclicker_id format":
                                    $scope.input_errors.push($scope.lang.invalidiclickerid);
                                    break;
                                default:
                                    // This is not supposed to reach here. But if it does,
                                    // better display the exception than throw an error.
                                    $scope.input_errors.push(update_result.status);
                                    break;
                            }
                        });
                };
            }
        ]);
    return controller_name;
};
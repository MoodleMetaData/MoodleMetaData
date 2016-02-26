/**
 * RegisteriClickerInfo controller, handles iclicker registration of the current user.
 *
 * Uses case(s):
 * * Current user don't have iclicker registred: Proceed "normally".
 * * Current user is registered already: Switch to edit controller and view.
 */
var utility = require('../utility');

module.exports = function(app) {
    var controller_name = 'M.mod_iclickerregistration.controllers.RegisteriClickerInfo';
    app.controller(
        controller_name,
        ['$scope', '$location', '$routeParams', '$timeout',
            require('../models/iclicker')(app),
            require('../models/user')(app),
            function ($scope, $location, $routeParams, $timeout, iClicker, User) {
                $scope.lang = M.str.mod_iclickerregistration;
                $scope.iclicker_id = '';
                $scope.input_success = null;
                $scope.input_errors = [];

                // See if the user already exist, if so, switch to a different controller/view.
                iClicker.get({idnumber: $routeParams.idnumber},
                    function (current_user_iclicker) {
                        var registered = !!current_user_iclicker.iclicker_id;
                        if (registered) {
                            $location.url('/edit-iclicker-info/' + $routeParams.idnumber).replace();
                        }
                    });

                // Acquire information about the user being edited for aesthetic reasons (e.g. display name and stuff).
                $scope.current_user = User.get({idnumber: $routeParams.idnumber});

                $scope.register_cb = function (iclicker_id) {
                    $scope.iclicker_id = iclicker_id;
                    iClicker.save({
                        'idnumber': $routeParams.idnumber,
                        'iclicker_id': $scope.iclicker_id
                    }, function (save_result) {
                        $scope.input_errors = [];
                        switch (save_result.status) {
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
                                $scope.input_errors.push(save_result.status);
                                break;
                        }
                    });
                };
            }
        ]);
    return controller_name;
};
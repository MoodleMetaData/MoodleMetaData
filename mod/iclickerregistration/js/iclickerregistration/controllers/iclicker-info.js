/**
 * iClickerInfo controller, displaying basic iclicker information to the user.
 */
module.exports = function(app) {
    var controller_name = 'M.mod_iclickerregistration.controllers.iClickerInfo';
    app.controller(
        controller_name,
        ['$scope', '$location',
            require('../models/iclicker')(app),
            function ($scope, $location, iClicker) {
                $scope.lang = M.str.mod_iclickerregistration;
                $scope.manually_enrolled = false;

                $scope.current_user_iclicker = iClicker.get({idnumber: 'current_user'},
                    function (current_user_iclicker) {
                        var manually_enrolled_user = !current_user_iclicker.idnumber;
                        if (manually_enrolled_user) {
                            $scope.manually_enrolled = true;
                            return;
                        }

                        var not_registered = !current_user_iclicker.iclicker_id;
                        if (not_registered) {
                            $location.url('/register-iclicker-info/current_user').replace();
                            return;
                        }
                    });
            }
        ]);

    return controller_name;
};
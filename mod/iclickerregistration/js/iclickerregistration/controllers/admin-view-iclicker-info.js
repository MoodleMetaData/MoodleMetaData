var utility = require('../utility');

/**
 * AdminViewiClickerInfo controller, displays the iclicker information of the admin,
 * aswell as all the users in the moodle site (have iclicker or not).
 */
module.exports = function(app) {
    var controller_name = 'M.mod_iclickerregistration.controllers.AdminViewiClickerInfo';
    app.controller(
        controller_name,
        ['$scope', '$location',
            require('../models/iclicker')(app),
            function ($scope, $location, iClicker, ngTable) {
                $scope.lang = M.str.mod_iclickerregistration;
                $scope.manually_enrolled = false;

                $scope.current_user_iclicker = iClicker.get({idnumber: 'current_user', admin_mode: true},
                    function (current_user_iclicker) {
                        var manually_enrolled_user = !current_user_iclicker.idnumber;
                        if (manually_enrolled_user) {
                            $scope.manually_enrolled = true;
                            return;
                        }
                    });
            }
        ]);
    return controller_name;
};
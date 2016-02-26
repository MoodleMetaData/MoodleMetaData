var utility = require('../utility');

/**
 * TeacherViewiClickerInfo controller, displays the iclicker information of the admin,
 * as well as all the users in the current course (have iclicker or not). Think of this
 * as a restrictred AdminViewiClickerInfo.
 *
 * @see AdminViewiClickerInfo controller for documentation.
 */
module.exports = function(app) {
    var controller_name = 'M.mod_iclickerregistration.controllers.TeacherViewiClickerInfo';
    app.controller(
        controller_name,
        ['$scope', '$location', '$window', '$sce',
            require('../models/iclicker')(app),
            function ($scope, $location, $window, $sce, iClicker) {
                $scope.lang = M.str.mod_iclickerregistration;
                $scope.generateclassrotsterhelp = $sce.trustAsHtml($scope.lang.generateclassrotsterhelp);
                $scope.manually_enrolled = false;
                $scope.current_user_iclicker = iClicker.get({
                    idnumber: 'current_user',
                    admin_mode: false  // Implicitly false, but whatever.
                }, function (current_user_iclicker) {
                    var manually_enrolled_user = !current_user_iclicker.idnumber;
                    if (manually_enrolled_user) {
                        $scope.manually_enrolled = true;
                        return;
                    }
                });

                $scope.generate_class_roster = function() {
                    $window.open('/mod/iclickerregistration/rest.php/generate_roster_file?course_module_id=' + utility.get_query_params().id);
                };
            }
        ]);
    return controller_name;
};
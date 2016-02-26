
/**
 * Controller for access denied situations that needs redirecting.
 */
module.exports = function(app) {
    var controller_name = 'M.mod_iclickerregistration.controllers.AccessDenied';
    app.controller(
        controller_name,
        ['$scope', '$location', '$timeout',
            function ($scope, $location, $timeout) {
                $scope.lang = M.str.mod_iclickerregistration;
                $timeout(
                    function () {
                        $location.path('/');
                    }, 3000);

            }]);
    return controller_name;
};
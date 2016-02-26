/**
 * Controller for access denied situations that don't need redirecting.
 * Use this to those whose default path is AccessDenied anyway, thus to avoid
 * refreshing every x seconds, use this.
 */
module.exports = function(app) {
    var controller_name = 'M.mod_iclickerregistration.controllers.AccessDeniedNonRedirect';
    app.controller(
        controller_name,
        ['$scope', function ($scope) {
            $scope.lang = M.str.mod_iclickerregistration;
        }]);
    return controller_name;
};
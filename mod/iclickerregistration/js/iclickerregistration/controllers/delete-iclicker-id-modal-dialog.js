
module.exports = function(app) {
    var controller_name = 'M.mod_iclickerregistration.controllers.DeleteModalDialog';
    app.controller(controller_name,
        ['$scope', '$uibModalInstance',
            function ($scope, $uibModalInstance) {
                $scope.lang = M.str.mod_iclickerregistration;
                $scope.ok = function () {
                    $uibModalInstance.close();
                };

                $scope.cancel = function () {
                    $uibModalInstance.dismiss('cancel');
                };
            }]);
    return controller_name;
}
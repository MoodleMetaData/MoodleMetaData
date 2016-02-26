module.exports = function(app) {
    app.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
            when('/', {
                template: require('./../views/index.html')
            }).
            otherwise({ redirectTo: '/' });
    }]);
};
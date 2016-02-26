jQuery = (typeof jQuery === "undefined")? require('jquery') : jQuery;
$ = (typeof $ === "undefined")? jQuery : $;
var utility = require("./utility");
var angular = require("angular");

try {
    angular.module('M.mod_iclickerregistration.iClickerRegistrationApp');
} catch (e) {
    // App is not loaded.

    /**
     * Angular Application module.
     *
     * Note: Controllers and Directives (a part of view), must use this angular
     * module since they will all be living under the element with an attribute
     *
     * ng-app='M.mod_iclickerregistration.iClickerRegistrationApp'
     *
     * Thus, in order for angular to know where to place a Controller's view or
     * Directive's view, use M.mod_iclickerregistration.iClickerRegistrationApp
     * as base module. @see controllers/controllers.js for examples.
     */
    var app = angular.module('M.mod_iclickerregistration.iClickerRegistrationApp',
        [require('angular-route'),
            require('angular-ui-bootstrap'),
            require('angular-resource')]);

    // Initialize Directives/views.
    var directives = [
        require('./directives/iclicker-info'),
        require('./directives/iclicker-info-list'),
        require('./directives/iclicker-form')
    ];
    directives.forEach(function (d) {
        d(app);
    });

    // Initialize Controllers.
    var controllers = [
        require('./controllers/access-denied'),
        require('./controllers/access-denied-non-redirect'),
        require('./controllers/admin-view-iclicker-info'),
        require('./controllers/iclicker-info'),
        require('./controllers/register-iclicker-info'),
        require('./controllers/edit-iclicker-info'),
        require('./controllers/teacher-view-iclicker-info'),
        require('./controllers/delete-iclicker-id-modal-dialog')
    ];
    controllers.forEach(function (c) {
        c(app);
    });

    utility.current_user_type = "student";
    $(document).on("user_type_change", function (e, data) {
        utility.current_user_type = data.user_type;

        // Router.
        var router = require('./controllers/router')(app);
    });
}
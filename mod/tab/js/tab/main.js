$ = jQuery = require('jquery');
var angular = require('angular');

var router = require('./controllers/router');
// Controller(s).
var tab_manager_controller = require('./controllers/tab-manager');
var tab_set_controller = require('./controllers/tab-set');

var app = angular.module('M.mod_tab.TabApp', [
    require('angular-route'),
    require('angular-ui-bootstrap'),
    require('angular-resource'),
    require('angular-sanitize'),
    require('angular-animate')
]);

router(app);
tab_manager_controller(app);
tab_set_controller(app);
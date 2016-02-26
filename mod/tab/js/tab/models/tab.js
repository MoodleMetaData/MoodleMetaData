var utility = require('../utility');

/**
 * @model Tab
 *
 * Retrieve tab content from server.
 */
module.exports = function(app) {
    var model_name = 'M.mod_tab.models.Tab';
    app.factory(model_name,
        ['$resource',
            function($resource) {
                return $resource('rest.php/tab/:action/:id',
                    {
                        course_module_id: utility.get_query_params().id  // course module id of the original tab group.
                    },

                    {
                        'can_edit': {method: 'GET', params: {action: 'can_edit'}, cache: true, responseType: 'text'},
                        'get': {method:'GET', params: {id: "@id"}, cache: true },
                        'get_mod_tab_metadata': { method: 'GET', params: { action: 'metadata', id: "@id" }, isArray: true, cache: true},
                        'get_course_tabs_metadata': { method: 'GET', params: { action: 'course_tabs_metadata' }, isArray: true, cache: true},
                        'is_enabled': { method: 'GET', params: { action: 'is_tab_menu_enabled'}, responseType: 'text', cache: true},
                    });
            }]);

    return model_name;
};
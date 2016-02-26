var utility = require("../utility");

/**
 * @module TabManagerController.
 *
 * Manages tabs in mod_tab's in the current course. If displaymenu is false,
 * the only mod_tab would be the current one.
 */
module.exports = function(app) {
    var controller_name = 'TabManagerController';
    app.controller(controller_name,
        [ '$scope', '$location', '$window',
            require('../models/tab')(app),
            function ($scope, $location, $window, Tab) {
                $scope.str = M.str.mod_tab;  // This will contain lang files.
                $scope.collapse = true;

                $scope.can_edit = Tab.can_edit();
                $scope.can_edit.$promise.then(function(data) {
                    $scope.can_edit = !!parseInt(data[0], 10);
                });

                $scope.is_menu_enabled = false;
                Tab.is_enabled().$promise.then(function(data) {
                    $scope.is_menu_enabled = !!parseInt(data[0], 10);
                });

                // selected_metadata will be the metadata of the currently selected item in menu. Aka,
                // The metadata of the current tab group (or tab sets).
                $scope.selected_metadata = { cmid: utility.get_query_params().id };

                // List of metadatas for each tab group (or tab sets).
                $scope.tab_sets_metadata = [];

                Tab.get_course_tabs_metadata({}, function(metadatas) {
                    $scope.selected_metadata = metadatas[0];

                    // We want to set $scope.selected_metadata to the tab group with cmid (course module id)
                    // corresponding to the current tab module.
                    for (var i = 0; i < metadatas.length; i++) {
                        var current_cmid = utility.get_query_params().id;
                        if (metadatas[i].cmid === current_cmid) {
                            metadatas[i].active = true;
                            $scope.selected_metadata = metadatas[i];
                            break;
                        }
                    }

                    $scope.tab_sets_metadata = metadatas
                });

                /**
                 * Called when changing tab menu.
                 *
                 * @param tab_set_index Index of the new selected "tab sets metadata".
                 */
                $scope.change_tabset_cb = function(tab_set_index) {
                    // Uncomment for fully-single page mod_tab.
                    //$scope.selected_metadata = $scope.tab_sets_metadata[tab_set_index];

                    // Got no choice, but in order to change the navigation block, this non-snappy way is the
                    // only way.
                    $window.location.href = '/mod/tab/view.php?id='+$scope.tab_sets_metadata[tab_set_index].cmid;
                };

                /**
                 * Called when edit button of a tab menu entry is clicked. This will redirect to the editing page
                 * to the tag group associated with the clicked tab menu entry.
                 *
                 * @param tab_set_index Index of the new selected "tab sets metadata". In which the corresponding
                 *                      tab group will be edited.
                 */
                $scope.edit_tab_set = function() {
                    /*
                     * Edit the mod_tab corresponding to current cmid or utility.get_query_params().id,
                     * otherwise, user parameter tab_set_index, to edit other course modules.
                     */
                    var edit_cmid = utility.get_query_params().id;
                    if ($scope.tab_sets_metadata.length > 0) {
                        var edit_cmid = $scope.selected_metadata.cmid;
                    }

                    $window.location.href = '/course/mod.php?update=' + edit_cmid;
                };
            }]);

    return controller_name;
};
var utility = require("../utility");
var base64 = require("../node_modules/js-base64/base64.js").Base64;

/**
 * @module TabSetController.
 *
 * Sits inside @see TabMenuController (aka $parent). We inherit data from current "tab group metadata", and from
 * that we acquire metadata for each tabs related to the "tab group metadata".
 */
module.exports = function(app) {
    var controller_name = 'TabSetController';
    app.controller(controller_name,
        [ '$scope', '$location', '$sce',
            require('../models/tab')(app),
            function ($scope, $location,  $sce, Tab) {
                /*
                 * Current tab module metadata.
                 */
                $scope.mod_tab_metadata = $scope.$parent.selected_metadata;

                /**
                 * List of metadatas for each tab in current mod_tab.
                 *
                 * @type {Array}
                 */
                $scope.current_mod_tab_tab_metadata = [];

                /**
                 * Keeps track of current tab.
                 * @type {null}
                 */
                $scope.current_tab_content = null;

                var resize_google_docs = function(html_content) {
                    var embedded_google_docs_pattern = /(<iframe [^>]*src="http(?:s|\s):\/\/docs.google\.com[^">]+"[^>]*)(>\s*<\/iframe>)/gi;
                    var embedded_google_docs_replacement = "$1 style=\"width: 100%;height: 100vh;\" $2";
                    return html_content.replace(embedded_google_docs_pattern, embedded_google_docs_replacement);
                };

                /**
                 * @param tab_content_id ID of the tab content to load.
                 */
                var load_tab_content = function(tab_content_id) {
                    $scope.current_tab_content = null;  // Trigger "Loading..." msg in views.
                    Tab.get({id: tab_content_id}, function (response) {
                        var content = base64.decode(response.content);
                        content = resize_google_docs(content);

                        $scope.current_tab_content = $sce.trustAsHtml(content);
                    });
                };

                /**
                 * Called when the $parent.selected_metadata changed. This will update
                 * the current set of tabs.
                 */
                function update_metadata() {
                    $scope.current_mod_tab_tab_metadata = Tab.get_mod_tab_metadata(
                        {id: $scope.mod_tab_metadata.cmid},
                        function (tab_metadatas) {
                            // Get the content of the first one.
                            if (tab_metadatas.length > 0) { load_tab_content(tab_metadatas[0].id); }
                        });
                };

                update_metadata();

                /**
                 * Select callback. When a new tab is selected.
                 * @param tab_content_id Tab content id in the current set of tabs.
                 */
                $scope.select_cb = function(tab_content_id) {
                    load_tab_content(tab_content_id);
                };

                /**
                 * Monitors the parent's $parent.selected_metadata, and call update_metadata to acquire new tab
                 * group.
                 */
                $scope.$watch('$parent.selected_metadata',
                    function(new_val, old_val) {
                        // Ensure that there is actually a change.
                        // Note: I don't new_val/old_val data structure to change, thus I'm just comparing
                        // cmid.
                        if (new_val.cmid !== old_val.cmid || old_val === null) {
                            console.log(new_val);
                            $scope.mod_tab_metadata = new_val;
                            update_metadata();
                        }
                    }, true);
            }]);

    return controller_name;
};
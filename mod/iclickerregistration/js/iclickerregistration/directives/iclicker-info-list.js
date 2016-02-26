// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

var utility = require('../utility');

/**
 * iclickerInfo directive. This will allow us to reuse iclicker-info.html
 * as <iclicker-info></iclicker-info> tag.
 */
module.exports = function(app) {
    app.directive('iclickerInfoList',
        ['$uibModal', '$location', '$sce',
            require('../models/iclicker')(app),
            function ($uibModal, $location, $sce, iClicker) {
                return {
                    restrict: 'E',
                    template: require('../views/partials/iclicker-info-list.html'),

                    scope: {
                        lang: '=',
                        adminMode: '=',

                        // Optional. Provide at your own risk.
                        pageChangeCallBack: '=',
                        ascChangeCallBack: '=',
                        currentPage: '=',
                        maxSize: '=',
                        itemsPerPage: '=',
                        query: '=',
                        orderBy: '=',
                        asc: '=',
                        hideUnregistered: '=',
                        filterConflicts: '=',
                        duplicateCount: '=',
                        pageCount: '=',
                        iclickers: '=',
                        queryChangeCallBack: '=',
                        hideUnregisteredCallBack: '=',
                        filterConflictsCallBack: '=',
                        sortCallBack: '='
                    },

                    link: function(scope, element, attrs) {
                        scope.currentPage = 1;
                        scope.maxSize = 5;
                        scope.itemsPerPage = 100;
                        scope.query = '';
                        scope.orderBy = 'idnumber';
                        scope.asc = true;
                        scope.hideUnregistered = true;
                        scope.filterConflicts = false;
                        scope.duplicateCount = 0;
                        scope.pageCount = 0;

                        // Support mentioned that these might be langed file with links, thus we must trust
                        // these.
                        scope.iclickeridconflictlegendtext = $sce.trustAsHtml(scope.lang.iclickeridconflictlegendtext);

                        /**
                         * 1. Query all the LEFT JOIN of user => iclicker (iclicker rows are null when no entry for user).
                         * 2. Update all iclicker models.
                         * 3. Update total_items model.
                         */
                        var query_iclickers = function () {
                            var begin = scope.itemsPerPage * (scope.currentPage - 1);
                            var end = begin + scope.itemsPerPage;
                            var index_pair_str = begin + '-' + end;
                            iClicker.query({
                                    index_pair: index_pair_str,
                                    query: scope.query,
                                    order_by: scope.orderBy,
                                    ascending: scope.asc,
                                    admin_mode: scope.adminMode,
                                    hide_unregistered: scope.hideUnregistered,
                                    filter_conflicts: scope.filterConflicts
                                },
                                function (query_result) {
                                    if (query_result.status === "access denied") {
                                        $location.path('/access-denied');
                                    }

                                    scope.iclickers = query_result.users;
                                    scope.totalItems = query_result.user_count;
                                    scope.duplicateCount = query_result.duplicate_count;
                                });
                        };

                        scope.pageChangeCallBack = function () {
                            query_iclickers();
                        };

                        var query_change_event = function() {};

                        scope.queryChangeCallBack = function () {
                            // Don't query immediately when query change (key press). Try to wait for x ms without query change.
                            window.clearTimeout(query_change_event);
                            query_change_event = setTimeout(query_iclickers, utility.query_delay);
                        };
                        scope.hideUnregisteredCallBack = function () {
                            query_iclickers();
                        };
                        scope.edit_iclicker_cb = function (index) {
                            if (index === null || typeof index === "undefined") {
                                $location.url('/edit-iclicker-info/current_user');
                            } else {
                                $location.url('/edit-iclicker-info/' + scope.iclickers[index].idnumber);
                            }
                        };
                        scope.register_iclicker_cb = function (index) {
                            if (index === null || typeof index === "undefined") {
                                $location.url('/register-iclicker-info/current_user');
                            } else {
                                $location.url('/register-iclicker-info/' + scope.iclickers[index].idnumber);
                            }
                        };
                        scope.delete_iclicker_cb = function (index) {
                            if (index === null || typeof index === "undefined") {
                                // TODO: Nothing to delete.
                                $location.url('/register-iclicker-info/current_user');
                                return;
                            } else {
                                iClicker.delete({
                                    'idnumber': scope.iclickers[index].idnumber,
                                    'id': scope.iclickers[index].id,
                                    'iclicker_id': scope.iclickers[index].iclicker_id
                                }, function (delete_result) {
                                    if (delete_result.status === "access denied") {
                                        $location.path('/access-denied');
                                        return;
                                    }

                                    query_iclickers();
                                });
                            }
                        };

                        scope.sortCallBack = function(sorty_by_value) {
                            scope.asc = true;
                            scope.orderBy = sorty_by_value;
                            query_iclickers();
                        };

                        scope.ascChangeCallBack = function() {
                            scope.asc = !scope.asc;
                            query_iclickers();
                        };

                        scope.filterConflictsCallBack = function() {
                            query_iclickers();
                        };

                        query_iclickers();
                    }
                };
            }]);
}
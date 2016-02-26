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

/**
 * Javascript required for eclass course management block
 *
 * @package   block_course_management
 * @copyright  Trevor Jones <tdjones@ualberta.ca>
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


M.blocks_eclasscoursemanagement = M.blocks_eclasscoursemanagement || {};

M.blocks_eclasscoursemanagement.init = function () {
    // Add our event listeners to the submit to intercept submission and display warnings.
    var config_form = Y.one('#configure_form');

    var cancelled = false;
    config_form.one(".btn-cancel").on('click', function (evt) {
        cancelled = true;
    });

    /**
     * Note that this doesn't really submit the form directly anymore.
     */
    Y.on('submit', function (evt) {
        if (cancelled === false) {
            evt.preventDefault();
            var start = M.blocks_eclasscoursemanagement.date_select_to_component('start', config_form);
            var end = M.blocks_eclasscoursemanagement.date_select_to_component('end', config_form);

            // Form is instead indirectly submitted through this method.
            M.blocks_eclasscoursemanagement.validate_dates(start, end);
        }
    }, config_form);
};

/**
 *
 * @param start {Object} of dates containing year, month, day as fields.
 * @param end {Object} of dates containing year, month, day as fields.
 * @returns {boolean} true if valid, otherwise false.
 */
M.blocks_eclasscoursemanagement.validate_dates = function (start, end) {
    var sd = new Date(start['year'], start['month'], start['day'], 0, 0, 0);
    var ed = new Date(end['year'], end['month'], end['day'], 0, 0, 0);

    var today = new Date();
    if (sd.getTime() >= ed.getTime() && !is_open) {
        (new M.core.alert({
            message: 'Error: Start date must be before end date.',
            modal: "true",
            visible: false
        })).show();
        return false;
    } else {
        var is_open = M.blocks_eclasscoursemanagement.is_course_open();

        /**
         * open_course criteria:
         * 1. Course wasn't open. Message won't make sense otherwise.
         * 2. ed >= today, otherwise the following case could occur (prior to the existence of case 1 and 2):
         *    sd <= ed, ed <= today. Adjusting sd to another valid value will open prompt despite the course
         *    being close still since ed <= today.
         */
        var open_course =
            !is_open &&
            sd.getTime() <= today.getTime() &&
            ed.getTime() >= today.getTime();

        /**
         * close_course criteria:
         * 1. Since we know open_course's sd <= today <= ed, there exist no ed < today by observation. Thus
         *    no intersect.
         * 2. Course used to be open. The confirm dialouge message won't makes sense otherwise.
         */
        var close_course =
            is_open &&
            ed.getTime() <= today.getTime();

        if (open_course) {
            M.blocks_eclasscoursemanagement.validate_dates_helper.
                open_course_case(function() {
                    M.blocks_eclasscoursemanagement.validate_dates_helper.
                        open_later_scenario(today, sd, ed);
                });
        } else if (close_course) {
            M.blocks_eclasscoursemanagement.validate_dates_helper.
                close_course_case(function() {
                    M.blocks_eclasscoursemanagement.validate_dates_helper.
                        open_later_scenario(today, sd, ed);
                });
        } else {
            M.blocks_eclasscoursemanagement.validate_dates_helper.
                open_later_scenario(today, sd, ed);
        }
    }
};

/**
 * validate_dates_helper object is an object to place helper methods for validate_dates method.
 *
 * This was created to convert javascript alert() to YUI's confirm/alert for prompting. In doing so,
 * we have to deal with YUI's asynchronous methods as opposed to javascript's serialize alert()
 * function. This means converting some control flow if/else statements to callbacks.
 */
M.blocks_eclasscoursemanagement.validate_dates_helper =
    M.blocks_eclasscoursemanagement.validate_dates_helper || {};

/**
 * @param yes_callback {function object} Called when user select yes.
 * @param no_callback {function object} Called when user select no.
 */
M.blocks_eclasscoursemanagement.validate_dates_helper.open_course_case =
    function (yes_callback, no_callback) {
        var confirm = new M.core.confirm({
            question: "By selecting a start date that has already passed " +
            "your course will be automatically opened.",
            modal: "true",
            visible: false
        });

        confirm.on("complete-yes", function () {
            if (yes_callback) yes_callback();
        });

        confirm.on("complete-no", function () {
            if (no_callback) no_callback();
        });

        confirm.show();
    };

/**
 * @param yes_callback {function object} Called when user select yes.
 * @param no_callback {function object} Called when user select no.
 */
M.blocks_eclasscoursemanagement.validate_dates_helper.close_course_case =
    function (yes_callback, no_callback) {
        var confirm = new M.core.confirm({
            question: "By selecting an end date that has already passed " +
            "your course will be automatically closed.",
            modal: "true",
            visible: false
        });

        confirm.on("complete-yes", function () {
            if (yes_callback) yes_callback();
            M.blocks_eclasscoursemanagement.validate_dates_helper.
                open_later_scenario(today_date, start_date, end_date);
        });

        confirm.on("complete-no", function () {
            if (no_callback) no_callback();
            // Do nothing.
        });

        confirm.show();
    };

/**
 * Deals with cases in which the course is opened in later dates.
 * @param today_date {Date} date today.
 * @param start_date {Date} starting date of course.
 * @param end_date {Date} end date of course.
 */
M.blocks_eclasscoursemanagement.validate_dates_helper.open_later_scenario =
    function (today_date, start_date, end_date) {
        var is_open = M.blocks_eclasscoursemanagement.is_course_open();
        var open_later =
            !is_open &&
            start_date.getTime() > today_date.getTime();
        var close_course_open_later =
            is_open &&
            start_date.getTime() > today_date.getTime();

        if (open_later) {
            var confirm = new M.core.confirm({
                question: "By selecting a start date that is in the future your " +
                "course will open on the selected date.",
                modal: "true",
                visible: false
            });

            confirm.on("complete-yes", function () {
                Y.one('#configure_form').getDOMNode().submit();
            });

            confirm.on("complete-no", function () {
                // Do nothing.
            });

            confirm.show();
        } else if (close_course_open_later) {
            var confirm = new M.core.confirm({
                question: "By selecting a start date that is in the future your " +
                "course will be automatically closed. Course will " +
                "open on the selected date.",
                modal: "true",
                visible: false
            });

            confirm.on("complete-yes", function () {
                Y.one('#configure_form').getDOMNode().submit();
            });

            confirm.on("complete-no", function () {
                // Do nothing.
            });

            confirm.show();
        } else {
            // Other cases, just submit.
            Y.one('#configure_form').getDOMNode().submit();
        }
    };

M.blocks_eclasscoursemanagement.date_select_to_component = function (name, parentForm) {
    var year = parentForm.one("[name=\"" + name + "[year]\"]").get('value');
    var month = parentForm.one("[name=\"" + name + "[month]\"]").get('value');
    var day = parentForm.one("[name=\"" + name + "[day]\"]").get('value');
    var res = [];
    res['year'] = year;
    // Date object expects month to be 0-11 so, subtract 1.
    res['month'] = month - 1;
    res['day'] = day;
    return res;
};

/**
 * @return true if course is open, false otherwise.
 */
M.blocks_eclasscoursemanagement.is_course_open = function () {
    var config_form = Y.one('#configure_form');
    var course_status = config_form.one("#id_visibility option").getHTML();
    return course_status.toLowerCase() == "Open".toLowerCase();
};
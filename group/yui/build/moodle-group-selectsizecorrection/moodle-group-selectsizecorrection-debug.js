YUI.add('moodle-group-selectsizecorrection', function (Y, NAME) {

/**
 * Javascript correction to the sizing problem of select element.
 *
 * @class M.group-selectsizecorrection
 * @author Joey Andres <jandres@ualberta.ca>
 * @constructor
 * @extends M.core.dragdrop
 */

var SELECTSIZECORRECTIONNAME = 'group-selectsizecorrection';

var SELECTSIZECORRECTION = function (config) {
    SELECTSIZECORRECTION.superclass.constructor.apply(this, config);
};

/**
 * @class SELECTSIZECORRECTION
 * @brief Class for handling select size correction.
 */
Y.extend(SELECTSIZECORRECTION, Y.Base, {
    initializer: function (config) {
        void(config);  // Avoid jslint complains.

        var selectLists = Y.all('.select-full-height');
        selectLists.each(this.setupSizeCorrection, this);
        selectLists.each(this.correctSelectSize, this);
    },

    /**
     * Main function of correcting select element size.
     * @param selectElem {YUI Select Element}
     */
    correctSelectSize: function(selectElem) {
        this.correctSelectWidth(selectElem);
        this.correctSelectHeight(selectElem);
    },

    /**
     * Establish the callbacks on resize.
     * @param selectElem {YUI Select Element}
     */
    setupSizeCorrection: function(selectElem) {
        // Save the original style, so we can precompute it in case it changed.
        selectElem.getDOMNode().originalStyle = selectElem.getDOMNode().style;

        thisAnchor = this;
        Y.one(window).on("resize", function () {
            thisAnchor.correctSelectSize.call(thisAnchor, selectElem);
        });
    },

    /**
     * Fix the width of the select element.
     * @param selectElem {YUI Select Element}
     */
    correctSelectWidth: function(selectElem) {
        var selectParentElem = selectElem.ancestor();

        // Go recompute the original style. This resets
        // the size, so we can get the proper width, not the
        // changed width, to match its content.
        selectElem.getDOMNode().style = selectElem.getDOMNode().originalStyle;

        // Only consider clientWidth (without the vertical scrollbar, if any).
        // If parent width is greater than select element's width, expand
        // client width to parent's width. This gets rid of akward outlines.
        // Otherwise, don't change anything, thus activating scrollbar on
        // wrapper.
        if (selectParentElem.getComputedStyle("width") >
            selectElem.getComputedStyle("width")) {
            selectElem.setStyle("width",
                selectParentElem.getComputedStyle("width"));
        }
    },

    /**
     * Correct the height of parent element.
     * Precondition: This function is called from selectFullHeight.
     * @param selectElem {YUI Select Element}
     */
    correctSelectHeight: function(selectElem) {
        var descendants = selectElem.all("*");
        selectElem.setAttribute("size", descendants.size());

        if (selectElem.getAttribute("size") < this.get("lineCount")) {
            selectElem.setAttribute("size", this.get("lineCount"));
        }

        var selectParentElem = selectElem.ancestor();

        var selectElemLineHeight = 0;
        if (selectElem.getAttribute("size") > this.get("lineCount")) {
            // Acquire original select size and store it.
            var selectElemTempSize = selectElem.getAttribute("size");

            // Assign the lineCount to the new select element.size,
            // this recomputes the size of the select element, wrt our
            // desired line count.
            selectElem.setAttribute("size", this.get("lineCount"));

            // Acquire the height, as if the select element only have
            // the desired line count.
            selectElemLineHeight = selectElem.getDOMNode().offsetHeight;

            // Restore the original select size, thus recompute the original
            // select height.
            selectElem.setAttribute("size", selectElemTempSize);
        } else {
            // It is one of the precondition of this, to be called
            // from selectFullHeight function, thus, if
            // selectElem.size < MIN_LINE-COUNT, selectElem.size is adjusted
            // to 20.
            selectElemLineHeight = selectElem.getDOMNode().offsetHeight;
        }

        selectParentElem.setStyle("height", selectElemLineHeight + "px");
    }
}, {
    NAME: SELECTSIZECORRECTIONNAME,
    ATTRS: {
        lineCount: {value: 20} // Default line count, just in case.
    }
});

M.select_size_correction =
M.select_size_correction = null;
M.init_selectsizecorrection = function(config) {
    M.select_size_correction = new SELECTSIZECORRECTION(config);
    return M.select_size_correction;
};

}, '@VERSION@', {"requires": ["base", "node"]});

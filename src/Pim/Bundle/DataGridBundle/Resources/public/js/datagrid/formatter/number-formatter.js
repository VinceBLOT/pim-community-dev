/* global define */
define(['underscore', 'oro/datagrid/cell-formatter'],
function(_, CellFormatter) {
    'use strict';

    /**
     * Cell formatter that format percent representation
     *
     * @export oro/datagrid/number-formatter
     * @class  oro.datagrid.NumberFormatter
     * @extends oro.datagrid.CellFormatter
     */
    var NumberFormatter = function (options) {
        options = options ? _.clone(options) : {};
        _.extend(this, options);
        this.formatter = getFormatter(this.style);
    };

    var getFormatter = function(style) {
        var functionName = 'format' + style.charAt(0).toUpperCase() + style.slice(1);
        return formatter[functionName];
    };

    NumberFormatter.prototype = new CellFormatter();

    _.extend(NumberFormatter.prototype, {
        /** @property {String} */
        style: 'decimal',

        /**
         * @inheritDoc
         */
        fromRaw: function (rawData) {
            return rawData;
        },

        /**
         * @inheritDoc
         */
        toRaw: function (formattedData) {
            return formattedData;
        }
    });

    return NumberFormatter;
});

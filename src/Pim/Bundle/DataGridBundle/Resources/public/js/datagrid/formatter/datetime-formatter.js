/* global define */
define(['underscore', 'backgrid', 'pim/formatter/datetime'],
function(_, Backgrid, DateTimeFormatter) {
    'use strict';

    /**
     * Date formatter for date cell
     *
     * @export  oro/datagrid/date-formatter
     * @class   oro.datagrid.DateTimeFormatter
     * @extends Backgrid.CellFormatter
     */
    var DatagridDateTimeFormatter = function (options) {
        _.extend(this, options);
    };

    DatagridDateTimeFormatter.prototype = new Backgrid.CellFormatter();
    _.extend(DatagridDateTimeFormatter.prototype, {
        /**
         * Allowed types are "date", "time" and "dateTime"
         *
         * @property {string}
         */
        type: 'dateTime',

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

    return DatagridDateTimeFormatter;
});

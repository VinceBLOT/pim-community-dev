define(
    ['jquery', 'pim/date-context', 'bootstrap.bootstrapsdatepicker'],
    function ($, DateContext) {
        'use strict';

        var datepickerOptions = {
            todayHighlight: true,
            format: DateContext.get('format').toLowerCase(),
            language: DateContext.get('language')
        };

        var init = function () {
            var datepicker = $('.datepicker');

            datepicker.datepicker(datepickerOptions);
        };

        return {
            init: init
        };
    }
);

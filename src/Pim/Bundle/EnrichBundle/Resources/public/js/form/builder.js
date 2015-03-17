'use strict';

define(
    ['jquery', 'underscore', 'pim/form-registry'],
    function($, _, FormRegistry) {
        var buildForm = function  (formName) {
            var promise = $.Deferred();

            $.when(
                FormRegistry.getForm(formName),
                FormRegistry.getFormExtensions(formName)
            ).done(function (Form, extensions) {
                var form = new Form();

                var promises = [];
                _.each(extensions, function(extension) {
                    promises.push(buildForm(extension));
                });
                $.when.apply($, promises).done(function() {
                    form.setExtensions(arguments);
                        promise.resolve(form);
                });
            });

            return promise.promise();
        };

        return {
            build: function buildRootForm (formName) {
                var promise = $.Deferred();

                buildForm(formName).done(function(form) {
                    form.configure().done(function () {
                        promise.resolve(form);
                    });
                });

                return promise.promise();
            }
        };
    }
);
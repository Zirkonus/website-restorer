/*
 Language system service
 created by Oza / 28-12-2016
 */

(function () {
    'use strict';

    angular.module('App').factory('previewImageControl', previewImageControl);

    previewImageControl.$inject = [];

    function previewImageControl () {

        var settings = {
            openControl: false,
            src: '',
            callback: function() {}
        };

        var service = {
            settings: settings,
            configure: configure,
            init: init
        };

        return service;

        //
        //
        //

        function init(callback) {
            settings.callback = callback;
        }

        function configure(openControl, src) {
            settings.openControl = openControl;
            settings.src = src;

            settings.callback(openControl, src);
        }

    }

})();
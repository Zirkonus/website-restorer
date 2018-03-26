/*
 Language system service
 created by Oza / 28-12-2016
 */

(function () {
    'use strict';

    angular.module('App').factory('addPageDialogControl', addPageDialogControl);

    addPageDialogControl.$inject = ['$rootScope'];

    function addPageDialogControl ($rootScope) {

        var settings = {
            openControl: false,
            callback: function () { console.error('No callback.') }
        };

        var service = {
            settings: settings,
            configure: configure,
            reject: reject
        };

        return service;

        //
        //
        //

        function configure(openControl, callback) {
            settings.openControl = openControl;

            if (callback){
                settings.callback = callback;
            }

            if (openControl) {
                $rootScope.$emit('setScrollFreeze', true);
            }

            console.log(settings);
        }

        function reject() {
            $rootScope.$emit('setScrollFreeze', false);
            settings.openControl = false;
        }

    }

})();
/*
 Language system service
 created by Oza / 28-12-2016
 */

(function () {
    'use strict';

    angular.module('App').factory('confirmDialogControl', confirmDialogControl);

    confirmDialogControl.$inject = ['$rootScope'];

    function confirmDialogControl ($rootScope) {

        var settings = {
            message: '',
            openControl: false,
            confirmCallback: function () { console.error('No confirm callback.') }
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

        function configure(message, openControl, confirmCallback) {
            settings.message = message;
            settings.openControl = openControl;
            if (confirmCallback){
                settings.confirmCallback = confirmCallback;
            }

            if (openControl) {
                $rootScope.$emit('setScrollFreeze', true);
            }
        }

        function reject() {
            $rootScope.$emit('setScrollFreeze', false);
            settings.openControl = false;
        }

    }

})();
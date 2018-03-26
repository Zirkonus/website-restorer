/*
 Language system service
 created by Oza / 28-12-2016
 */

(function () {
    'use strict';

    angular.module('App').factory('changePassDialogControl', changePassDialogControl);

    changePassDialogControl.$inject = ['$rootScope'];

    function changePassDialogControl ($rootScope) {

        var settings = {
            openControl: false
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

        function configure(openControl) {
            settings.openControl = openControl;

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
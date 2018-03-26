/*
 Language system service
 created by Oza / 28-12-2016
 */

(function () {
    'use strict';

    angular.module('App').factory('errorDialogControl', errorDialogControl);

    errorDialogControl.$inject = ['$rootScope'];

    function errorDialogControl ($rootScope) {

        var settings = {
            openControl: false,
            title: '',
            message: '',
            type: 'error', //error, warning
            confirmButtonEnable: false,
            confirmButtonText: 'Ok',
            rejectButtonEnable: false,
            rejectButtonText: 'Cancel',
            confirmCallback: function () { }
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

        function configure(openControl, configureObj) {
            settings.openControl = openControl;

            settings.title = configureObj.title;
            settings.message = configureObj.message;
            settings.type = configureObj.type;
            settings.confirmButtonEnable = configureObj.confirmButtonEnable;
            settings.confirmButtonText = configureObj.confirmButtonText;
            settings.rejectButtonEnable = configureObj.rejectButtonEnable;
            settings.rejectButtonText = configureObj.rejectButtonText;

            if (configureObj.confirmCallback){
                settings.confirmCallback = configureObj.confirmCallback;
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
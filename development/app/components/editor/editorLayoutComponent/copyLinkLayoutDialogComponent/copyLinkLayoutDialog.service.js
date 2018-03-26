/*
 Language system service
 created by Oza / 28-12-2016
 */

(function () {
    'use strict';

    angular.module('App').factory('copyLinkLayoutDialogControl', copyLinkLayoutDialogControl);

    copyLinkLayoutDialogControl.$inject = ['$rootScope'];

    function copyLinkLayoutDialogControl ($rootScope) {

        var settings = {
            link: '',
            linkWBM: '',
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

        function configure(link, linkWBM, openControl) {
            settings.link = link;
            settings.linkWBM = linkWBM;
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
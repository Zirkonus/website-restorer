/*
 Language system service
 created by Oza / 28-12-2016
 */

(function () {
    'use strict';

    angular.module('App').factory('copyLinkGalleryDialogControl', copyLinkGalleryDialogControl);

    copyLinkGalleryDialogControl.$inject = ['$rootScope'];

    function copyLinkGalleryDialogControl ($rootScope) {

        var settings = {
            link: '',
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

        function configure(link, openControl) {
            settings.link = link;
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
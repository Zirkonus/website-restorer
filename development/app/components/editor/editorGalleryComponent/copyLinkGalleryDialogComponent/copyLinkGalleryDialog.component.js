/*
 Copy link dialog component
 created by Oza / 13-04-2017
 */

(function () {
    'use strict';

    angular.module('App').component('copyLinkGalleryDialog', {
        templateUrl: '/app/components/editor/editorGalleryComponent/copyLinkGalleryDialogComponent/copyLinkGalleryDialog.html',
        bindings: {
        },
        controller: copyLinkGalleryDialogController,
        controllerAs: 'vm'
    });

    copyLinkGalleryDialogController.$inject = ['$lang', 'copyLinkGalleryDialogControl'];

    function copyLinkGalleryDialogController($lang, copyLinkGalleryDialogControl) {
        var vm = this;

        vm.lang = $lang;

        vm.settings = copyLinkGalleryDialogControl.settings;

        vm.confirmReject = copyLinkGalleryDialogControl.reject;

        //
        //
        //


    }

})();


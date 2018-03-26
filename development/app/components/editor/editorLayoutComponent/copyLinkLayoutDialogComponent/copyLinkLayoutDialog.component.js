/*
 Copy link dialog component
 created by Oza / 13-04-2017
 */

(function () {
    'use strict';

    angular.module('App').component('copyLinkLayoutDialog', {
        templateUrl: '/app/components/editor/editorLayoutComponent/copyLinkLayoutDialogComponent/copyLinkLayoutDialog.html',
        bindings: {
        },
        controller: copyLinkLayoutDialogController,
        controllerAs: 'vm'
    });

    copyLinkLayoutDialogController.$inject = ['$lang', 'copyLinkLayoutDialogControl'];

    function copyLinkLayoutDialogController($lang, copyLinkLayoutDialogControl) {
        var vm = this;

        vm.lang = $lang;

        vm.settings = copyLinkLayoutDialogControl.settings;

        vm.confirmReject = copyLinkLayoutDialogControl.reject;

        //
        //
        //


    }

})();


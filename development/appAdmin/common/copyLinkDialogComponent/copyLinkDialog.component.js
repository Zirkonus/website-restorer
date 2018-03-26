/*
 Copy link dialog component
 created by Oza / 13-04-2017
 */

(function () {
    'use strict';

    angular.module('App').component('copyLinkDialog', {
        templateUrl: '/appAdmin/common/copyLinkDialogComponent/copyLinkDialog.html',
        bindings: {
        },
        controller: copyLinkDialogController,
        controllerAs: 'vm'
    });

    copyLinkDialogController.$inject = ['$lang', 'copyLinkDialogControl'];

    function copyLinkDialogController($lang, copyLinkDialogControl) {
        var vm = this;

        vm.lang = $lang;

        vm.settings = copyLinkDialogControl.settings;

        vm.confirmReject = copyLinkDialogControl.reject;

        //
        //
        //


    }

})();


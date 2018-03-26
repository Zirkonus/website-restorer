/*
 Confirm dialog component
 created by Oza / 11-04-2017
 */

(function () {
    'use strict';

    angular.module('App').component('confirmDialog', {
        templateUrl: '/app/common/confirmDialogComponent/confirmDialog.html',
        bindings: {
        },
        controller: confirmDialogController,
        controllerAs: 'vm'
    });

    confirmDialogController.$inject = ['$lang', 'confirmDialogControl'];

    function confirmDialogController($lang, confirmDialogControl) {
        var vm = this;

        vm.lang = $lang;

        vm.settings = confirmDialogControl.settings;

        vm.confirmReject = confirmDialogControl.reject;

        //
        //
        //



    }

})();


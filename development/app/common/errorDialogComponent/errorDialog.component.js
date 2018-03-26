/*
 Confirm dialog component
 created by Oza / 11-04-2017
 */

(function () {
    'use strict';

    angular.module('App').component('errorDialog', {
        templateUrl: '/app/common/errorDialogComponent/errorDialog.html',
        bindings: {
        },
        controller: errorDialogController,
        controllerAs: 'vm'
    });

    errorDialogController.$inject = ['$lang', 'errorDialogControl'];

    function errorDialogController($lang, errorDialogControl) {
        var vm = this;

        vm.lang = $lang;

        vm.settings = errorDialogControl.settings;

        vm.confirmReject = errorDialogControl.reject;

        //
        //
        //



    }

})();


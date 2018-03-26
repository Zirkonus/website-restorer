/*
 Confirm dialog component
 created by Oza / 11-04-2017
 */

(function () {
    'use strict';

    angular.module('App').component('renameDialog', {
        templateUrl: '/app/components/editor/editorLayoutComponent/renameDialogComponent/renameDialog.html',
        bindings: {
        },
        controller: renameDialogController,
        controllerAs: 'vm'
    });

    renameDialogController.$inject = ['$lang', 'renameDialogControl'];

    function renameDialogController($lang, renameDialogControl) {
        var vm = this;

        vm.lang = $lang;

        vm.settings = renameDialogControl.settings;

        vm.confirmReject = renameDialogControl.reject;

        //
        //
        //

    }

})();


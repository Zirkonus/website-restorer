/*
 Header
 created by Oza / 27-03-2017
 */

(function () {
    'use strict';

    angular.module('App').component('dialogBody', {
        templateUrl: '/app/common/dialogBodyComponent/dialogBody.html',
        transclude: true,
        bindings: {
            openControl: '=',
            closeDialog: '&'
        },
        controller: dialogBodyController,
        controllerAs: 'vm'
    });

    dialogBodyController.$inject = ['$lang'];

    function dialogBodyController($lang) {
        var vm = this;

        vm.lang = $lang;

    }

})();


/*
 Header
 created by Oza / 27-03-2017
 */

(function () {
    'use strict';

    angular.module('App').component('dropdownMenu', {
        templateUrl: '/app/common/dropdownMenuComponent/dropdownMenu.html',
        transclude: true,
        bindings: {
            configList: '=',
            cbParam: '=',
            dropdownOpen: '<'
        },
        controller: dropdownMenuController,
        controllerAs: 'vm'
    });

    dropdownMenuController.$inject = ['$lang'];

    function dropdownMenuController($lang) {
        var vm = this;

        vm.lang = $lang;

    }

})();


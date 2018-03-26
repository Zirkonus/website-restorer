/*
 Header
 created by Oza / 27-03-2017
 */

(function () {
    'use strict';

    angular.module('App').component('header', {
        templateUrl: '/appAdmin/components/header/header.html',
        bindings: {

        },
        controllerAs: 'vm',
        controller: headerController
    });

    headerController.$inject = ['$lang'];

    function headerController($lang) {
        var vm = this;

        vm.lang = $lang;

        vm.menu = {
            opened: false
        };

        vm.openLogout = false;

        vm.clickLogout = function () {
            vm.openLogout = !vm.openLogout;
        };
    }

})();


/*
 Header
 created by Oza / 27-03-2017
 */

(function () {
    'use strict';

    angular.module('App').component('header', {
        templateUrl: '/auth/components/header/header.html',
        bindings: {

        },
        controllerAs: 'vm',
        controller: headerController
    });

    headerController.$inject = ['$lang', '$state'];

    function headerController($lang, $state) {
        var vm = this;

        vm.lang = $lang;

        vm.state = $state;

        vm.menu = {
            opened: false
        };
    }

})();


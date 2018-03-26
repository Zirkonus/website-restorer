/**
 * Created by German Kushch on 18.04.2017.
 */

(function () {
    'use strict';

    angular.module('App').component('dashBoard', {
        templateUrl: '/appAdmin/components/dashboard/dashboard.template.html',
        bindings: {
            generalInfo: '<'
        },
        controllerAs: 'vm',
        controller: dashboardController
    });

    dashboardController.$inject = ['$lang', '$location', 'user', 'transport', 'loadData'];

    function dashboardController($lang, $location, user, transport, loadData) {
        var vm = this;

        vm.lang = $lang;

        vm.location = $location;

        vm.$onInit = function () {
            console.log(vm.generalInfo);
        };
        
    }

})();

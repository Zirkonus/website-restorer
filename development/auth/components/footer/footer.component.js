/**
 * Created by German Kushch on 04.05.2017.
 */

(function () {
    'use strict';

    angular.module('App').component('footerTpl', {
        templateUrl: '/auth/components/footer/footer.template.html',
        bindings: {


        },
        controllerAs: 'vm',
        controller: footerController
    });

    footerController.$inject = ['$lang'];

    function footerController($lang) {

        var vm = this;

        vm.lang = $lang;


    }


})();


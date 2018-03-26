/**
 * Created by German Kushch on 19.04.2017.
 */

/**
 * Created by German Kushch on 18.04.2017.
 */

(function () {
    'use strict';

    angular.module('App').component('tileTpl', {
        templateUrl: '/appAdmin/components/dashboard/tile-component/tile.template.html',
        bindings: {
            iconColor: '@',
            tileValue: '<',
            tileName: '@',
            tileIcon: '@',
            tileUnit: '@'
        },
        controllerAs: 'vm',
        controller: tileController
    });

    tileController.$inject = ['$lang'];

    function tileController($lang) {
        var vm = this;

        vm.lang = $lang;

    }

})();


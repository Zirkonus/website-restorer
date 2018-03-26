/**
 * Created by Oza on 03.04.2017.
 */


(function () {
    'use strict';

    angular.module('App').component('menuExpander', {
        templateUrl: '/app/components/editor/editorLayoutComponent/menuExpanderComponent/menuExpander.html',
        bindings: {
            menuExpanderConfig: '=',
            rootItem: '<',
            menuTitle: '=',
            paramData: '=',
            opened: '<'
        },
        controllerAs: 'vm',
        controller: menuExpanderController
    });

    menuExpanderController.$inject = ['$lang', '$sce', '$rootScope'];

    function menuExpanderController($lang, $sce, $rootScope) {
        var vm = this;

        vm.lang = $lang;
        vm.sce = $sce;

        vm.actionMobile = actionMobile;

        //
        //
        //

        function actionMobile() {
            if ($rootScope.isScreenMobile()) {
                vm.menuExpanderConfig[1].callback(vm.paramData);
            }
        }

    }

})();




/**
 * Created by Oza on 03.04.2017.
 */


(function () {
    'use strict';

    angular.module('App').component('listTree', {
        templateUrl: '/app/components/editor/editorLayoutComponent/listTreeComponent/listTree.html',
        bindings: {
            listData: '=',
            listLimit: '<',
            currentLevel: '<',
            menuExpanderConfig: '<'
        },
        controllerAs: 'vm',
        controller: listTreeController
    });

    listTreeController.$inject = ['$lang', '$document', '$scope', '$rootScope'];

    function listTreeController($lang, $document, $scope, $rootScope) {
        var vm = this;

        vm.lang = $lang;

        vm.menuExpanderOpenIndex = null;

        vm.isNotEmptyArray = isNotEmptyArray;

        vm.calcListItemOffset = calcListItemOffset;
        vm.menuExpanderOpen = menuExpanderOpen;

        $document.ready(initListToggle);

        //
        //
        //

        function calcListItemOffset(deepLevel) {
            var baseWidthLaptop = 40;
            var levelWidthLaptop = 42;

            if ($rootScope.isScreenMobile()) {
                return 12 + 30 * deepLevel;
            }

            if ($rootScope.isScreenTablet()) {
                return baseWidthLaptop + deepLevel * levelWidthLaptop;
            }

            if ($rootScope.isScreenLaptop() || $rootScope.isScreenDesktop()) {
                return baseWidthLaptop + deepLevel * levelWidthLaptop;
            }

            return 700;
        }

        function menuExpanderOpen(index) {
            if(!$rootScope.isScreenTablet()) return;

            if (vm.menuExpanderOpenIndex === index) {
                vm.menuExpanderOpenIndex = null;
            }
            else {
                vm.menuExpanderOpenIndex = index;
            }
        }

        function initListToggle() {
            vm.controlToggle = vm.listData.map(function(item) {
                return {
                    isOpened: false
                };
            });

            $scope.$digest();
        }

        function isNotEmptyArray(item) {
            if (angular.isDefined(item) && angular.isArray(item) && item.length != 0) {
                return true;
            }
            return false;
        }

    }

})();




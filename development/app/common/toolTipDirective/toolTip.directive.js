/*
 Runs callback at click outside element
 created by Oza / 12-01-2016
 */


(function () {
    'use strict';

    angular
        .module('App')
        .directive('toolTip', toolTip);

    toolTip.$inject = [];

    function toolTip() {
        return {
            restrict: 'AE',
            templateUrl: '/app/common/toolTipDirective/toolTip.html',
            transclude: true,
            scope: {
                toolTip: '<',
                toolTipOpened: '<'
            },
            controller: toolTipController,
            controllerAs: 'vm',
            bindToController: true
        }
    }

    toolTipController.$inject = [];

    function toolTipController() {
        var vm = this;

    }

})();
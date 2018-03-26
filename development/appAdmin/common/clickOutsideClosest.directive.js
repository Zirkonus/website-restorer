/*
 Runs callback at click outside element
 created by Oza / 12-01-2016
 */


(function () {
    'use strict';

    angular
        .module('App')
        .directive('clickOutsideClosest', clickOutsideClosest);

    clickOutsideClosest.$inject = ['$document'];

    function clickOutsideClosest($document) {
        return {
            restrict: 'A',
            scope: {
                clickOutsideClosest: '&',
                closestSelector: '@'
            },
            link: function (scope, elem, attr) {

                $document.on('click', function (e) {

                    if (!e.target.closest(scope.closestSelector)) {
                        scope.$apply(function () {
                            scope.$eval(scope.clickOutsideClosest);
                        });
                    }
                });
            }
        }
    }

})();
/*
 Runs callback at click outside element
 created by Oza / 12-01-2016
 */


(function () {
    'use strict';

    angular
        .module('App')
        .directive('imageZoomer', imageZoomer);

    imageZoomer.$inject = [];

    function imageZoomer() {
        return {
            restrict: 'A',
            scope: {
                imageWidth: '<',
                imageHeight: '<'
            },
            link: function (scope, elem, attr) {

                console.log(elem[0].offsetWidth);

                if (scope.imageWidth >= elem[0].offsetWidth || scope.imageHeight >= elem[0].offsetHeight) {
                    elem.css({
                        'background-size': 'contain'
                    });
                }
                else {
                    elem.css({
                        'background-size': 'auto'
                    });
                }

            }
        }
    }

})();
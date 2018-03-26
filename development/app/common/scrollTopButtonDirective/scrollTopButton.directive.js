/*
 Runs callback at click outside element
 created by Oza / 12-01-2016
 */


(function () {
    'use strict';

    angular
        .module('App')
        .directive('scrollTopButton', scrollTopButton);

    scrollTopButton.$inject = ['$document', '$window'];

    function scrollTopButton($document, $window) {
        return {
            restrict: 'AE',
            templateUrl: '/app/common/scrollTopButtonDirective/scrollTopButton.html',
            scope: {
            },
            link: function (scope, elem, attr) {

                scope.showButtonFlag = false;

                $document.on('scroll', function (e) {

                    var scrollTopValue = $document[0].body.scrollTop || $document[0].documentElement.scrollTop;

                    if (scrollTopValue > $window.innerHeight && !scope.showButtonFlag) {
                        scope.showButtonFlag = true;
                        scope.$digest();
                    }
                    else if (scrollTopValue < $window.innerHeight && scope.showButtonFlag) {
                        scope.showButtonFlag = false;
                        scope.$digest();
                    }

                });

                scope.setScrollTop = function() {
                    $document.scrollTopAnimated(0, 300).then(function() {});
                    // $document[0].body.scrollTop = $document[0].documentElement.scrollTop = 0;
                }
            }
        }
    }

})();
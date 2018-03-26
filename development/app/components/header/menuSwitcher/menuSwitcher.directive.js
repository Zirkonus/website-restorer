(function () {
    'use strict';

    angular.module('App').directive('menuSwitcher', [
        '$window',
        '$rootScope',
        function ($window, $rootScope) {
            return {
                template:
                    '<div class="menu-switcher menu-open" ' +
                        'data-ng-class="{\'menu-open\': !menuState.opened, \'menu-close\': menuState.opened}"' +
                        'data-ng-click="menuState.opened = !menuState.opened">' +
                        '<div class="line-top"></div>' +
                        '<div class="line-middle"></div>' +
                        '<div class="line-bottom"></div>' +
                    '</div>',
                restrict: 'EA',
                scope: {
                    menuState: '='
                },
                replace: true,
                link: function (scope, element, attr) {

                    scope.toggleMenu = function() {
                        scope.menuState.opened = !scope.menuState.opened;
                    };

                    //Freeze body scroll at opened mobile menu
                    scope.$watch('menuState.opened',
                        function(newValue, oldValue) {
                            if (newValue) {
                                $window.scrollTo(0, 0); //scroll top
                                $rootScope.$emit('setScrollFreeze', true);
                            }
                            else {
                                $rootScope.$emit('setScrollFreeze', false);
                            }

                        }
                    );

                    //todo: add width watcher to test mobile --> desktop transform, while menu is open. To fix scroll freeze bug


                }
            }
        }
    ]);

})();
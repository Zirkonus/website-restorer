/*
 History list component
 created by Oza / 28-03-2017
 */

(function () {
    'use strict';

    angular.module('App').directive('historyIcon', [function() {
        return {
            template: '<span class="history-icon-block"></span>',
            restrict: 'AE',
            replace: true,
            scope: {
                type: '='
            },
            link: function (scope, element, attr) {

                var iconStyle = {
                    credits: {
                        'color': '#00a3ff',
                        'iconClass': "icon-icon-23"
                    },
                    file: {
                        'color': '#9966ff',
                        'iconClass': "icon-icon-24"
                    },
                    error: {
                        'color': '#f65b58',
                        'iconClass': "icon-icon-25"
                    },
                    delete: {
                        'color': '#f4d01a',
                        'iconClass': "icon-icon-26"
                    },
                    upload: {
                        'color': '#7bcb29',
                        'iconClass': "icon-icon-27"
                    }
                };

                var currStyle = iconStyle[scope.type];

                if(!currStyle) return;

                element.css({'color': currStyle['color']});
                element.addClass(currStyle['iconClass']);

            }
        }
    }]);
})();



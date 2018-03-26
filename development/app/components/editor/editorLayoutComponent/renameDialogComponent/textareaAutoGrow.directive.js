/*
 Textarea auto grow directive
 created by Oza / 14-04-2017
 */

(function () {
    'use strict';

    angular.module('App').directive('textareaAutoGrow', textareaAutoGrow);


    function textareaAutoGrow () {
        return {
            restrict: 'A',
            scope: {
                maxHeight: '<',
                minHeight: '<'
            },
            link: function (scope, element, attr) {

                element.css({
                    'height': scope.minHeight + 'px',
                    'max-height': scope.maxHeight + 'px'
                });

                setGrow();

                scope.$watch(
                    function() {return element[0].scrollHeight},
                    setGrow
                );

                element.on('keyup', setGrow);

                //
                //
                //


                function setGrow() {
                    element.css({'height': scope.minHeight + 'px'});

                    element.css({
                        'height': element[0].scrollHeight + 'px',
                        'overflow': element[0].scrollHeight > scope.maxHeight ? 'auto': 'hidden'
                    });
                }


            }
        }
    }

})();

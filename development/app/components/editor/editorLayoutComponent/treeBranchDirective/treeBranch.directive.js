/*
 created by Oza / 06-04-2016
 */


(function () {
    'use strict';

    angular
        .module('App')
        .directive('treeBranch', treeBranch);

    treeBranch.$inject = [];

    function treeBranch() {
        return {
            restrict: 'AE',
            templateUrl: '/app/components/editor/editorLayoutComponent/treeBranchDirective/treeBranch.html',
            scope: {
                branchDeep: '=',
                toggleValue: '=',
                typeToggleable: '<'
            },
            link: function (scope, elem, attr) {

                scope.getLeverArr = function(size) {
                    return new Array(+size);
                };

                scope.toggleChildLevel = function (status) {
                    if(status) {
                        scope.toggleValue.isOpened = !scope.toggleValue.isOpened;
                    }
                }

            }
        }
    }

})();
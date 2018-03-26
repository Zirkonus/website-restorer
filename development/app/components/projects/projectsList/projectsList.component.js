(function() {
    'use strict';

    angular.module('App').component('projectsList', {
        templateUrl: '/app/components/projects/projectsList/projectsList.html',
        bindings: {
            projectsListData: '<'
        },
        controllerAs: 'vm',
        controller: projectsListController
    });

    angular.module('App').directive('customLinkDirective', ['$timeout', '$window', function($timeout, $window) { 
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                $timeout(function() {
                    var elem             = element[0],
                        elemInner        = elem.innerHTML,
                        afterDotsLength  = 8;
                     angular.element($window).bind('resize', function() {
                        if(elemInner.length > (elem.clientWidth  / 9)) {
                            var beforeDotsLenght = (elem.clientWidth  / 9) - afterDotsLength + 3 - 4,
                                beforeDots  = elemInner.toString().substr(0, beforeDotsLenght),
                                afterDots   = elemInner.toString().substr((elemInner.length - afterDotsLength), afterDotsLength);

                            element[0].innerHTML = beforeDots + '...' + afterDots;
                        } else {
                            element[0].innerHTML = elemInner;
                        }
                        scope.$digest();
                    });

                    if(elemInner.length > (elem.clientWidth  / 9)) {
                        var beforeDotsLenght = (elem.clientWidth  / 9) - afterDotsLength + 3 - 4,
                                beforeDots  = elemInner.toString().substr(0, beforeDotsLenght),
                                afterDots   = elemInner.toString().substr((elemInner.length - afterDotsLength), afterDotsLength);
                                
                        element[0].innerHTML = beforeDots + '...' + afterDots;
                    }
                })
            }
        };
    }]);

    projectsListController.$inject = ['$lang'];

    function projectsListController($lang) {

        var vm = this;

        vm.lang = $lang;

        vm.projectsLimit = 6;
        vm.toolTipOpenedIndex = null;

        vm.increaseLimit = increaseLimit;
        vm.showProjectMoreButton = showProjectMoreButton;
        vm.toggleToolTip = toggleToolTip;

        vm.porjectsStatuses = {
            file: {
                labelText: 'FILE NOTICE',
                labelClass: 'status-file'
            },
            error: {
                labelText: 'ERROR NOTICE',
                labelClass: 'status-error'
            },
            upload: {
                labelText: 'UPLOAD NOTICE',
                labelClass: 'status-upload'
            },
            delete: {
                labelText: 'DELETE NOTICE',
                labelClass: 'status-delete'
            }
        };

        // vm.$onInit = function() {
        //     console.log(vm.projectsListData);
        // };

        function increaseLimit() {
            vm.projectsLimit += 3;
        }

        function showProjectMoreButton() {
            return vm.projectsLimit < vm.projectsListData.length;
        }

        function toggleToolTip(index) {
            if (vm.toolTipOpenedIndex !== index) {
                vm.toolTipOpenedIndex = index;
            } else {
                vm.toolTipOpenedIndex = null;
            }
        }


    }
})();
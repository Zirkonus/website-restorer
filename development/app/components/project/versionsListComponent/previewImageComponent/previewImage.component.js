/*
 Header
 created by Oza / 27-03-2017
 */

(function () {
    'use strict';

    angular.module('App').component('previewImage', {
        templateUrl: '/app/components/project/versionsListComponent/previewImageComponent/previewImage.html',
        bindings: {
        },
        controller: previewImageController,
        controllerAs: 'vm'
    });

    previewImageController.$inject = ['$document', '$scope', '$rootScope', 'previewImageControl'];

    function previewImageController($document, $scope, $rootScope, previewImageControl) {
        var vm = this;

        vm.imageSrc = '';
        vm.showPreview = false;

        vm.$onInit = function() {
            previewImageControl.init(function(open, src) {
                vm.showPreview = open;
                vm.imageSrc = src;
            });
        };

        vm.$postLink = function() {

            $document.on('mousemove', function(e) {

                if (!($rootScope.isScreenLaptop() || $rootScope.isScreenDesktop())) return;

                var previewEnterElement = e.target.closest('.preview-container');

                if (previewEnterElement) {

                    $scope.$apply(function() {
                        vm.imageSrc = previewEnterElement.getAttribute('preview-image-src');
                        vm.showPreview = true;
                    });

                }
                else {
                    $scope.$apply(function() {
                        vm.showPreview = false;
                    });
                }
            });

            $document.on('click', function(e) {

                if (!$rootScope.isScreenTablet()) return;

                var previewEnterElement = e.target.closest('.preview-container');

                if (previewEnterElement) {

                    $scope.$apply(function() {
                        vm.imageSrc = previewEnterElement.getAttribute('preview-image-src');
                        vm.showPreview = true;
                    });

                    e.stopPropagation();
                }
                else {
                    $scope.$apply(function() {
                        vm.showPreview = false;
                    });
                }
            })
        }

    }

})();


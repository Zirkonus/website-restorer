/**
 * Created by Oza on 03.04.2017.
 */


(function () {
    'use strict';

    angular.module('App').component('editorGallery', {
        templateUrl: '/app/components/editor/editorGalleryComponent/editorGallery.html',
        bindings: {
            imagesListData: '<'
        },
        controllerAs: 'vm',
        controller: editorGalleryController
    });

    editorGalleryController.$inject = ['$lang',
        '$rootScope',
        '$state',
        '$window',
        'confirmDialogControl',
        'copyLinkGalleryDialogControl',
        'Upload',
        'transport',
        'loadData'
    ];

    function editorGalleryController($lang, $rootScope, $state, $window, confirmDialogControl, copyLinkGalleryDialogControl, Upload, transport, loadData) {
        var vm = this;

        vm.lang = $lang;
        vm.dropdownMenuOpenIndex = null;
        vm.dropdownOpen = dropdownOpen;
        vm.imageBaseUrl = '/getpage=';

        vm.listLimit = 12;
        vm.listLoadMore = listLoadMore;
        vm.viewImageNewTab = viewImageNewTab;
        vm.deleteImageConfirm = deleteImageConfirm;
        vm.copyLink = copyLink;
        vm.uploadImage = uploadImage;

        vm.dropdownConfig = [
            {
                title: 'View',
                icon: 'icon-icon-11',
                callback: viewImageNewTab
            },
            {
                title: 'Download',
                icon: 'icon-icon-08',
                callback: callback
            },
            {
                title: 'Delete',
                icon: 'icon-icon-05',
                callback: deleteImageConfirm
            }
        ];

        //
        //
        //

        function dropdownOpen(index) {
            if($rootScope.isScreenLaptop()) return;

            if (vm.dropdownMenuOpenIndex === index) {
                vm.dropdownMenuOpenIndex = null;
            }
            else {
                vm.dropdownMenuOpenIndex = index;
            }
        }

        function callback(propObj) {
            console.log(this.title + ' - ' + propObj.id);
        }

        function viewImageNewTab(propObj) {
            var imageWindow = $window.open(vm.imageBaseUrl + propObj.id, '_blank');
            if (imageWindow) {
                imageWindow.focus();
            } else {
                alert('Please allow popups for this website');
            }
        }

        function deleteImageRequest(propObj) {
            return transport
                .go('/pages/delete', {id: propObj.id})
                .then(loadData.success, loadData.error)
                .then(function () {
                    vm.imagesListData.splice(propObj.index, 1);
                })
                .catch(function(err) {});
        }

        function deleteImageConfirm(propObj) {
            confirmDialogControl.configure(vm.lang.get('editor_images_delete_confirm_message'), true, callback);

            function callback() {
                deleteImageRequest(propObj);
            }
        }

        function copyLink(propObj) {
            copyLinkGalleryDialogControl.configure(propObj.link, true);
        }

        function uploadImage (file, errFiles) {

            if (file) {
                Upload
                    .upload({
                        url: '/images/create',
                        data: {
                            image: file,
                            version_id: $state.params.versionId
                        }
                    })
                    .then(loadData.success, loadData.error)
                    .then(function (data) {
                        vm.imagesListData.unshift(data[0]);
                    })
                    .catch(function(err) {});

            }
        }

        function listLoadMore () {
            if (vm.listLimit < vm.imagesListData.length) {
                vm.listLimit += 8;
            }
        }


    }

})();




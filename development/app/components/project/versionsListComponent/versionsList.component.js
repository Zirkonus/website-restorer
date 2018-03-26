/*
 History list component
 created by Oza / 28-03-2017
 */

(function () {
    'use strict';

    angular.module('App').component('versionsList', {
        templateUrl: '/app/components/project/versionsListComponent/versionsList.html',
        bindings: {
            versionsData: '<',
            emptyListMessage: '<',
            projectId: '<',
            ftpEnabled: '<'
        },
        controllerAs: 'vm',
        controller: versionsListController
    });

    versionsListController.$inject = [
        '$lang',
        '$scope',
        '$state',
        'transport',
        'loadData',
        'confirmDialogControl',
        'errorDialogControl',
        '$window',
        '$http',
        'ngDialog',
        'previewImageControl'
    ];

    function versionsListController($lang, $scope, $state, transport, loadData, confirmDialogControl, errorDialogControl, $window, $http, ngDialog, previewImageControl) {
        var vm = this;

        vm.lang = $lang;
        vm.currPaginationPage = 1;
        vm.dropdownMenuOpenIndex = null;

        vm.dropdownOpen = dropdownOpen;
        vm.isEmptyList = isEmptyList;
        vm.listLoadMore = listLoadMore;
        vm.openPreview = previewImageControl.configure;

        vm.popupOpened = false;
        vm.openPreviewImagePopup = function (img) {
            if (!vm.popupOpened) {
                vm.popupPreviewImage = img;
                var previewImagePopup = ngDialog.open({
                    template: '/app/components/project/versionsListComponent/popupsTemplates/previewImagePopup.html',
                    appendClassName: 'preview-image-popup',
                    scope: $scope,
                    controller: ['$scope', function($scope) {
                        console.log('previewImagePopup controller');
                        console.dir(vm.popupPreviewImage);                 
                        $scope.close = function() {
                            previewImagePopup.close();
                        }
                    }]
                });
                vm.popupOpened = true;            
                previewImagePopup.closePromise.then(function(data) {
                    vm.popupOpened = false;
                    // console.log(data.id + ' has been dismissed.');
                    // console.dir(data);
                });
            }
        };

        vm.dropdownConfig = {
            restored: [
                {
                    title: 'Check WBM',
                    icon: 'icon-icon-11',
                    callback: viewOriginalWBM
                },
                {
                    title: 'View Restored',
                    icon: 'icon-icon-11',
                    callback: viewRestoredPage
                },
                {
                    title: 'FTP Upload',
                    icon: 'icon-icon-36',
                    callback: ftpUploadCheck
                },
                {
                    title: 'Download',
                    icon: 'icon-icon-08',
                    callback: downloadFiles
                },
                {
                    title: 'Web Editor',
                    icon: 'icon-icon-09',
                    callback: openWebEditor
                },
                {
                    title: 'Delete',
                    icon: 'icon-icon-05',
                    callback: deleteRestoredVersionConfirm
                }
            ],
            basic: [
                {
                    title: 'View Page',
                    icon: 'icon-icon-11',
                    callback: viewOriginalWBM
                },
                {
                    title: 'Restore Site',
                    icon: 'icon-icon-21',
                    callback: restoreSiteVersion
                }
            ],
            in_progress: [
                {
                    title: 'Cancel',
                    icon: 'icon-icon-05',
                    callback: cancelInProgressVersionConfirm
                }
            ],
            error: [
                {
                    title: 'View Page',
                    icon: 'icon-icon-11',
                    callback: viewOriginalWBM
                },
                {
                    title: 'Restore Site',
                    icon: 'icon-icon-21',
                    callback: restoreSiteVersion
                },
                {
                    title: 'Delete',
                    icon: 'icon-icon-05',
                    callback: deleteRestoredVersionConfirm
                }
            ],
            cancel: []
        };

        vm.statusPill = {
            restored: 'Restored',
            in_progress: 'In progress',
            error: 'Error',
            cancel: 'Canceling',
            basic: ''
        };
        vm.statusTimeType = {
            restored: 'Done: ',
            in_progress: 'Started: ',
            error: 'Started: ',
            cancel: 'Started: ',
            basic: ''
        };

        // vm.$onInit = function() {
        //     console.log(vm.versionsData[0].date_archive);
        // };


        //
        //
        //

        function dropdownOpen(index) {
            if (vm.dropdownMenuOpenIndex === index) {
                vm.dropdownMenuOpenIndex = null;
            }
            else {
                vm.dropdownMenuOpenIndex = index;
            }
        }

        function restoreSiteVersion(paramObj) {
            transport
                .go('/versions/download', {id: paramObj.dataObj.id})
                .then(loadData.success, loadData.error)
                .then(function() {
                    paramObj.dataObj.status = 'in_progress';
                })
                .catch(function(err) {});
        }

        function downloadFiles(paramObj) {
            console.log(this.title + ' - ' + paramObj.dataObj.id);
            $window.open('/versions/upload?id=' + paramObj.dataObj.id, '_blank');
        }

        function openWebEditor(paramObj) {
            $state.go('editor.layout', {projectId: vm.projectId, versionId: paramObj.dataObj.id});
        }

        function isEmptyList() {
            return !angular.isArray(vm.versionsData.versions) || angular.isArray(vm.versionsData.versions) && vm.versionsData.versions.length === 0;
        }

        function deleteRestoredVersion(paramObj) {
            return transport
                .go('/versions/delete', {id: paramObj.dataObj.id})
                .then(loadData.success, loadData.error)
                .then(function() {
                    if ($state.current.name === 'project.all') {
                        paramObj.dataObj.status = 'basic';
                    }
                    else {
                        vm.versionsData.versions.splice(paramObj.index, 1);
                    }
                })
                .catch(function(err) {});
        }

        function deleteRestoredVersionConfirm(paramObj) {

            confirmDialogControl.configure(vm.lang.get('project_versions_delete_confirm_message'), true, callback);

            function callback() {
                deleteRestoredVersion(paramObj);
            }

        }

        function cancelInProgressVersion(paramObj) {
            return transport
                .go('/versions/cancel', {id: paramObj.dataObj.id})
                .then(loadData.success, loadData.error)
                .then(function(data) {
                    if (data.is_canceled) {
                        if ($state.current.name === 'project.all') {
                            paramObj.dataObj.status = 'basic';
                        }
                        else {
                            vm.versionsData.versions.splice(paramObj.index, 1);
                        }
                    }
                    else {
                        paramObj.dataObj.status = 'cancel';
                    }
                })
                .catch(function(err) {});
        }

        function cancelInProgressVersionConfirm(paramObj) {

            confirmDialogControl.configure(vm.lang.get('project_versions_cancel_confirm_message'), true, callback);

            function callback() {
                cancelInProgressVersion(paramObj);
            }

        }

        function viewOriginalWBM(paramObj) {
            var imageWindow = $window.open(paramObj.dataObj.version_url, '_blank');
            if (imageWindow) {
                imageWindow.focus();
            } else {
                alert('Please allow popups for this website');
            }
        }

        function viewRestoredPage(paramObj) {
            var imageWindow = $window.open('/getpage=' + paramObj.dataObj.home_page, '_blank');
            if (imageWindow) {
                imageWindow.focus();
            } else {
                alert('Please allow popups for this website');
            }
        }

        function ftpUploadRequest(paramObj) {
            console.log('ftp upload request, id = ' + paramObj.dataObj.id);
            transport
                .go('/versions/ftp-upload', {
                    id: paramObj.dataObj.id
                })
                .then(loadData.success, loadData.error)
                .then(function(data) {

                })
                .catch(function(err) {
                    console.log('Error during upload occurred!');
                });
        }

        function ftpUploadWarningOpen() {
            errorDialogControl.configure(true, {
                title: 'Error FTP upload',
                message: 'Oops! Project FTP settings not full...',
                type: 'warning',
                confirmButtonEnable: true,
                confirmButtonText: 'Edit',
                rejectButtonEnable: true,
                rejectButtonText: 'Cancel',
                confirmCallback: function() {
                    $state.go('projectEdit', {id: vm.projectId});
                }
            });
        }

        function ftpUploadCheck(paramObj) {
            console.log('params object', paramObj);
            if(vm.ftpEnabled) {
                ftpUploadRequest(paramObj);
            }
            else {
                ftpUploadWarningOpen();
            }
        }

        function listLoadMore () {

            var url = $state.current.name == 'project.all' ? '/versions/list' :
                    $state.current.name == 'project.inProgress' ? '/versions/list/in_progress' :
                    $state.current.name == 'project.restored' ? '/versions/list/restored' : '';

            // Disable infinite scroll loop directive
            vm.versionsData.is_more = 0;

            transport
                .go(url, {id: $state.params.id, page: vm.currPaginationPage + 1})
                .then(loadData.success, loadData.error)
                .then(function(data) {
                    vm.currPaginationPage++;
                    vm.versionsData.versions = vm.versionsData.versions.concat(data.versions);
                    vm.versionsData.is_more = data.is_more;
                })
                .catch(function(err) {});

        }

        // function

    }
})();


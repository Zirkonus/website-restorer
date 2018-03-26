/**
 * Created by Oza on 03.04.2017.
 */


(function () {
    'use strict';

    angular.module('App').component('editorLayout', {
        templateUrl: '/app/components/editor/editorLayoutComponent/editorLayout.html',
        bindings: {
            layoutListData: '<'
        },
        controllerAs: 'vm',
        controller: editorLayoutController
    });

    editorLayoutController.$inject = [
        '$window',
        '$lang',
        '$state',
        'transport',
        'loadData',
        'copyLinkLayoutDialogControl',
        'renameDialogControl',
        'confirmDialogControl',
        'addPageDialogControl'
    ];

    function editorLayoutController($window, $lang, $state, transport, loadData, copyLinkLayoutDialogControl, renameDialogControl, confirmDialogControl, addPageDialogControl) {
        var vm = this;

        vm.lang = $lang;

        //
        //
        //

        vm.listLimit = 10;
        vm.listLoadMore = listLoadMore;
        vm.addPageDialogOpen = addPageDialogOpen;

        vm.rootMenuExpanderConfig = [
            {
                title: 'Editor',
                icon: 'text-icon',
                type: 'text-btn',
                callback: openVisualEditor
            },
            {
                title: 'View',
                icon: 'icon-icon-31',
                type: 'circle-btn',
                callback: viewRestoredPage
            },
            {
                title: 'Copy link',
                icon: 'icon-icon-28',
                type: 'circle-btn',
                callback: copyLink
            },
            {
                title: 'Rename',
                icon: 'icon-icon-30',
                type: 'circle-btn',
                callback: renamePageConfirm
            }
        ];

        vm.itemMenuExpanderConfig = [
            {
                title: 'Editor',
                icon: 'text-icon',
                type: 'text-btn',
                callback: openVisualEditor
            },
            {
                title: 'View',
                icon: 'icon-icon-31',
                type: 'circle-btn',
                callback: viewRestoredPage
            },
            {
                title: 'Copy link',
                icon: 'icon-icon-28',
                type: 'circle-btn',
                callback: copyLink
            },
            {
                title: 'Rename',
                icon: 'icon-icon-30',
                type: 'circle-btn',
                callback: renamePageConfirm
            },
            {
                title: 'Delete',
                icon: 'icon-icon-26',
                type: 'circle-btn',
                callback: deletePageConfirm
            }
        ];

        function copyLink(propObj) {
            copyLinkLayoutDialogControl.configure('/getpage=' + propObj.dataObj.id, propObj.dataObj.web_path, true);
        }

        function renamePageRequest(propObj, newTitle) {
            return transport
                .go('pages/rename', {id: propObj.dataObj.id, title: newTitle})
                .then(loadData.success, loadData.error)
                .then(function success() {
                    propObj.dataObj.title = newTitle;
                })
                .catch(function(err) {});
        }

        function renamePageConfirm(propObj) {
            renameDialogControl.configure(propObj.dataObj.title, true, callback);

            function callback(newTitle) {
                renamePageRequest(propObj, newTitle);
            }
        }

        function deletePageRequest(propObj) {
            return transport
                .go('/pages/delete', {id: propObj.dataObj.id})
                .then(loadData.success, loadData.error)
                .then(function success() {
                    $state.reload();
                })
                .catch(function(err) {});
        }

        function deletePageConfirm(propObj) {
            confirmDialogControl.configure(vm.lang.get('editor_layout_delete_confirm_message'), true, callback);

            function callback() {
                deletePageRequest(propObj)
            }

        }

        function viewRestoredPage(paramObj) {
            var imageWindow = $window.open('/getpage=' + paramObj.dataObj.id, '_blank');
            if (imageWindow) {
                imageWindow.focus();
            } else {
                alert('Please allow popups for this website');
            }
        }

        function openVisualEditor(paramObj) {
            $state.go('visualEditor', {projectId: $state.params.projectId, versionId: $state.params.versionId, pageId: paramObj.dataObj.id});
        }

        function addPageRequest(pageObj) {
            return transport
                .go('/pages/create', {
                    source_id: +pageObj.sourceId,
                    parent_id: +pageObj.parentId,
                    title: pageObj.title})
                .then(loadData.success, loadData.error)
                .then(function () {
                    $state.reload();
                })
                .catch(function(err) {});
        }

        function addPageDialogOpen() {
            addPageDialogControl.configure(true, addPageRequest);
        }

        function listLoadMore() {
            if (vm.listLimit < vm.layoutListData.children.length) {
                vm.listLimit += 10;
            }
        }


    }

})();




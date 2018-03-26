/**
 * Created by Oza on 03.04.2017.
 */


(function () {
    'use strict';

    angular.module('App').component('visualEditor', {
        templateUrl: '/app/components/visualEditor/visualEditor.html',
        bindings: {
            projectData: '<',
            pageCode: '<'
        },
        controllerAs: 'vm',
        controller: visualEditorController
    });

    visualEditorController.$inject = ['$lang', '$state', '$window', 'transport', 'loadData', 'confirmDialogControl'];

    function visualEditorController($lang, $state, $window, transport, loadData, confirmDialogControl) {
        var vm = this;

        vm.lang = $lang;
        vm.state = $state;

        vm.editorHeight = $window.innerHeight - 205;
        vm.ckeditorInternal = {};

        vm.savePageRequest = savePageRequest;
        vm.exitConfirmDialog = exitConfirmDialog;
        vm.loadCkeditorInternal = loadCkeditorInternal;

        //
        //
        //

        function savePageRequest() {
            transport
                .go('/pages/edit', {id: $state.params.pageId, page_content: vm.pageCode})
                .then(loadData.success, loadData.error)
                .then(function() {
                    vm.ckeditorInternal.resetDirty();
                })
                .catch(function (err) {});
        }

        function exitVisualEditor() {
            $state.go('editor.layout', {projectId: vm.state.params.projectId, versionId: vm.state.params.versionId});
        }

        function exitConfirmDialog() {

            if (vm.ckeditorInternal.checkDirty()) {
                confirmDialogControl.configure($lang.get('editor_visual_exit_confirm_dialog'), true, exitVisualEditor);
            }
            else {
                exitVisualEditor()
            }
        }

        function loadCkeditorInternal() {
            return function(internalObj) {
                vm.ckeditorInternal = internalObj;
            }
        }


    }

})();




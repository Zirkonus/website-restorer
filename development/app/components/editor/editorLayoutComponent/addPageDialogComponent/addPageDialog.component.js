/*
 Add page dialog
 created by Oza / 14-04-2017
 */

(function () {
    'use strict';

    angular.module('App').component('addPageDialog', {
        templateUrl: '/app/components/editor/editorLayoutComponent/addPageDialogComponent/addPageDialog.html',
        bindings: {
            layoutListData: '='
        },
        controller: addPageDialogController,
        controllerAs: 'vm'
    });

    addPageDialogController.$inject = ['$lang', 'addPageDialogControl'];

    function addPageDialogController($lang, addPageDialogControl) {
        var vm = this;

        vm.lang = $lang;
        vm.settings = addPageDialogControl.settings;
        vm.dialogReject = addPageDialogControl.reject;
        vm.typeChild = false;
        vm.validDataCheck = validDataCheck;
        vm.addPageAccept = addPageAccept;
        vm.newPage = {
            parent: null,
            source: null,
            title: ''
        };


        vm.$onInit = function() {
            vm.layoutListReduced = reducedLayoutList();
        };


        //
        //
        //


        function reducedLayoutList() {
            var resultArray = [];

            reduser(vm.layoutListData);

            return resultArray;

            function reduser(obj) {
                resultArray.push(obj);

                if (obj.children.length) {
                    obj.children.forEach(function(item) {
                        reduser(item);
                    });
                }
            }
        }

        function validDataCheck() {
            if (vm.newPage.title && vm.newPage.source && (vm.newPage.parent || !vm.typeChild)) {
                return true
            }
            return false;
        }

        function addPageAccept() {

            vm.settings
                .callback({
                    title: vm.newPage.title,
                    sourceId: vm.newPage.source,
                    parentId: vm.typeChild ? vm.newPage.parent : vm.layoutListReduced[0].id
                })
                .then(function() {
                    vm.dialogReject();
                })
                .catch(function(err) {});

        }

    }

})();


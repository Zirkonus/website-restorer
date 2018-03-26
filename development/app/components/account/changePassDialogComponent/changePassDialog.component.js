/*
 Add page dialog
 created by Oza / 14-04-2017
 */

(function () {
    'use strict';

    angular.module('App').component('changePassDialog', {
        templateUrl: '/app/components/account/changePassDialogComponent/changePassDialog.html',
        bindings: {
            layoutListData: '='
        },
        controller: changePassDialogController,
        controllerAs: 'vm'
    });

    changePassDialogController.$inject = ['$lang', 'changePassDialogControl', 'transport', 'loadData'];

    function changePassDialogController($lang, changePassDialogControl, transport, loadData) {
        var vm = this;

        vm.lang = $lang;
        vm.settings = changePassDialogControl.settings;
        vm.dialogReject = dialogReject;
        vm.changePassRequest = changePassRequest;

        vm.passObj = {
            currPass: '',
            newPass: '',
            confirmPass: ''
        };

        // vm.$onInit = function() {
            // console.log(vm.layoutListData);
            // console.log(vm.layoutListReduced);
        // };


        //
        //
        //

        function changePassRequest() {
            transport
                .go('/user/password', {
                    'current_password': vm.passObj.currPass,
                    'password': vm.passObj.newPass,
                    'password_confirmation': vm.passObj.confirmPass
                })
                .then(loadData.success, loadData.error)
                .catch(function(err) {});
        }

        function dialogReject() {
            vm.passObj = {
                currPass: '',
                newPass: '',
                confirmPass: ''
            };

            changePassDialogControl.reject();
        }


    }

})();


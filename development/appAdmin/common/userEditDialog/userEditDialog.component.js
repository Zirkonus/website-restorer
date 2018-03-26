/**
 * Created by German Kushch on 24.04.2017.
 */

(function () {
    'use strict';

    angular.module('App').component('userEditDialog', {
        template: '<ng-include src="vm.getTemplate()"></ng-include>',
        bindings: {
            userInfo: '=',
            dialogType: '=',
            callback: '&'
        },
        controller: userEditDialogController,
        controllerAs: 'vm'
    });

    userEditDialogController.$inject = ['$lang', 'transport', 'loadData', 'user', '$location', '$stateParams'];

    function userEditDialogController($lang, transport, loadData, user, $location, $stateParams) {

        var vm = this;

        vm.lang = $lang;

        vm.user = user;

        vm.userClone = {};

        vm.userOption = [
            {
                name: 'Active',
                value: 1
            },
            {
                name: 'Inactive',
                value: 0
            }
        ];

        vm.defOption = null;

        vm.$onInit = function () {
            if (vm.userInfo) {
                for (var i = 0; i < vm.userOption.length; i++){
                    if (vm.userOption[i].value === vm.userInfo.is_active) {
                        vm.defOption = vm.userOption[i];
                        break;
                    }
                }
                vm.userClone = cloneObject(vm.userInfo);
            } else {
                vm.userInfo = {};
                vm.userInfo.username = '';
                vm.userInfo.email = '';
                vm.userInfo.password = null;
                vm.userInfo.credits = 0;
            }
            console.log('user: ', vm.userInfo);
        };

        vm.updateUser = updateUser;


        vm.addUser = addUser;

        vm.cancel = cancel;

        vm.getTemplate = getTemplate;

        function updateUser () {

            var dataToSend = {
                id: vm.userInfo.id || $stateParams.userId,
                username: vm.userInfo.username,
                email: vm.userInfo.email,
                credits: vm.userInfo.credits,
                is_active: vm.defOption.value
            };

            transport.go('admin/user/update', dataToSend)
                .then(loadData.success, loadData.error)
                .then(function (data) {
                    vm.user.closeDialog();
                    vm.callback();
                    $location.path('/user-list');
                })
                .catch(function (data) {
                   console.log('Error: ', data);
                });
        };

        function cancel () {
            if (vm.userClone) {
                for (var key in vm.userInfo)
                    vm.userInfo[key] = vm.userClone[key];
                vm.userClone = {};
            }
            vm.user.closeDialog();
        };

        function addUser() {
            var dataToSend = {
                username: vm.userInfo.username,
                email: vm.userInfo.email,
                password: vm.userInfo.password,
                credits: vm.userInfo.credits,
            };

            transport.go('admin/user/create', dataToSend)
                .then(loadData.success, loadData.error)
                .then(function (data) {
                    // vm.userList.push(data)
                    vm.callback();
                    vm.user.closeDialog();
                    $location.path('/user-list');
                })
                .catch(function (data) {
                    console.log('Error: ', data);
                });
        }

        function getTemplate () {
            return '/appAdmin/common/userEditDialog/user-' + vm.dialogType + '-dialog.template.html'
        };

        function cloneObject (obj) {
            var newObj = {};
            for (var key in obj)
                newObj[key] = obj[key];
            return newObj;
        };

    };

})();


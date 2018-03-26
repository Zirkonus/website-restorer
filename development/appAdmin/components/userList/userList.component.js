/**
 * Created by German Kushch on 19.04.2017.
 */

(function () {
    'use strict';

    angular.module('App').component('userList', {
        templateUrl: '/appAdmin/components/userList/user-list.template.html',
        bindings: {},
        controllerAs: 'vm',
        controller: userListController
    });

    userListController.$inject = ['$lang', '$location', 'user', '$scope'];

    function userListController($lang, $location, user, $scope) {
        var vm = this;

        vm.lang = $lang;

        vm.location = $location;

        vm.user = user;

        vm.currUser = null;

        vm.dialogType = '';

        vm.userList = [];

        vm.usersCount = 0;

        vm.pageNum = 1;

        vm.getInitials = getInitials;

        vm.clickEdit = clickEdit;

        vm.clickAdd = clickAdd;

        vm.deleteUser = deleteUser;

        vm.getUsersList = getUsersList;
        // vm.getUsersList();

        function getInitials (name) {
            var arr = name.split(' ');
            if (arr.length > 1)
                return arr[0][0] + arr[1][0];
            else
                return name[0];
        };

        function clickEdit (user) {
            vm.currUser = user;
            vm.dialogType = 'edit';
            vm.user.openDialog();
        };

        function clickAdd () {
            vm.currUser = {};
            vm.dialogType = 'add';
            vm.user.openDialog();
        }

        function deleteUser (user) {
            user.is_active = 0;
            vm.user.deleteUser(user.id)
                .then(function (data) {
                    // console.log('User: ', user.id, 'is inActive!');
                });
        };

        function getUsersList () {
            vm.user
                .getUsersList(vm.pageNum)
                .then(function (data) {
                    vm.userList = data.list;
                    vm.usersCount = data.count
                });
        };

        $scope.$watch(function () {
            return vm.pageNum;
        }, function () {
            getUsersList ();
        })

    }

})();

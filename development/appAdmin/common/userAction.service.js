/**
 * Created by German Kushch on 20.04.2017.
 */


(function () {

    'use strict';


    angular.module('App').factory('user', userService);

    userService.$inject = ['transport', 'loadData'];


    function userService (transport, loadData) {


        var vm = this;

        vm.isOpened = false;

        vm.openDialog = openDialog;
        vm.closeDialog = closeDialog;
        vm.deleteUser = deleteUser;
        vm.getUsersList = getUsersList;

        function openDialog () {
            vm.isOpened = true;
        }

        function closeDialog () {
            vm.isOpened = false;
        };

        function deleteUser (id) {
            return transport.go('admin/user/delete',{
                    id: id
                })
                .then(loadData.success,loadData.error)
        };

        function getUsersList (num) {
            return transport
                .go('admin/user-list', {
                    pageNum: num
                })
                .then(loadData.success, loadData.error);
        }

        return vm;
    };


})();
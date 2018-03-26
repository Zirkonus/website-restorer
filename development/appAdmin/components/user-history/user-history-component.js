/**
 * Created by German Kushch on 20.04.2017.
 */


(function () {

    'use strict';

    angular.module('App').component('userHistory',{
        templateUrl: '/appAdmin/components/user-history/user-history.template.html',
        bindings: {
            userInfo: '='
        },
        controller: userHistoryController,
        controllerAs: 'vm'
    });


    userHistoryController.$inject = ['$lang', 'user', 'transport', 'loadData', '$location', '$stateParams'];

    function userHistoryController ($lang, user, transport, loadData, $location, $stateParams) {

        var vm = this;

        vm.lang = $lang;

        vm.isOpened = false;

        vm.user = user;

        vm.dialogType = 'edit';

        vm.deleteUser = deleteUser;

        vm.dropdownModel = [
            {
                title: 'Edit',
                icon: 'icon-icon-09',
                callback: vm.user.openDialog
            },
            {
                title: 'Delete',
                icon: 'icon-icon-05',
                callback: vm.deleteUser
            }
        ];

        // vm.historyList = [
        //     {
        //         type: 1,
        //         message: 'Credits added',
        //         status: '800 credits',
        //         date: '10 Jul 2016, 11:01 AM'
        //     },
        //     {
        //         type: 2,
        //         message: 'Restored www.dribbble.com from 01.29.17',
        //         status: '20 credits',
        //         date: '05 Jul 2016, 12:00 AM'
        //     },
        //     {
        //         type: 3,
        //         message: 'Could not restore www.pixelicons.com from 05.12.16',
        //         status: 'no credits',
        //         date: '01 Jul 2016, 14:20 AM'
        //     },
        //     {
        //         type: 2,
        //         message: 'www.awwwards.com - setup successful',
        //         status: '',
        //         date: '01 Jul 2016, 11:01 AM'
        //     },
        //     {
        //         type: 2,
        //         message: 'www.pinterest.com version from 01.29.17 successfuly loaded',
        //         status: '',
        //         date: '03 Jul 2016, 12:00 AM'
        //     },
        //     {
        //         type: 3,
        //         message: 'Could not finish uploading www.awwwards.com to FTP',
        //         status: '',
        //         date: '08 Jul 2016, 14:20 AM'
        //     },
        //     {
        //         type: 4,
        //         message: 'www.dribbble.com project was deleted',
        //         status: '',
        //         date: '08 Jul 2016, 14:20 AM'
        //     },
        //     {
        //         type: 5,
        //         message: 'www.pinterest.com successfully archived in .zip',
        //         status: '',
        //         date: '08 Jul 2016, 14:20 AM'
        //     },
        //     {
        //         type: 2,
        //         message: 'www.awwwards.com - setup successful',
        //         status: '',
        //         date: '01 Jul 2016, 11:01 AM'
        //     },
        //     {
        //         type: 2,
        //         message: 'www.pinterest.com version from 01.29.17 successfuly loaded',
        //         status: '',
        //         date: '03 Jul 2016, 12:00 AM'
        //     },
        //     {
        //         type: 3,
        //         message: 'Could not finish uploading www.awwwards.com to FTP',
        //         status: '',
        //         date: '08 Jul 2016, 14:20 AM'
        //     },
        //     {
        //         type: 4,
        //         message: 'www.dribbble.com project was deleted',
        //         status: '',
        //         date: '08 Jul 2016, 14:20 AM'
        //     },
        //     {
        //         type: 2,
        //         message: 'www.awwwards.com - setup successful',
        //         status: '',
        //         date: '01 Jul 2016, 11:01 AM'
        //     },
        //     {
        //         type: 2,
        //         message: 'www.pinterest.com version from 01.29.17 successfuly loaded',
        //         status: '',
        //         date: '03 Jul 2016, 12:00 AM'
        //     },
        //     {
        //         type: 3,
        //         message: 'Could not finish uploading www.awwwards.com to FTP',
        //         status: '',
        //         date: '08 Jul 2016, 14:20 AM'
        //     },
        //     {
        //         type: 4,
        //         message: 'www.dribbble.com project was deleted',
        //         status: '',
        //         date: '08 Jul 2016, 14:20 AM'
        //     }
        // ];


        function deleteUser () {
            vm.user.deleteUser({
                id: vm.userInfo.id || $stateParams.userId
            })
                .then(function (data) {
                    $location.path('/user-list');
                });
        }

    };


})();

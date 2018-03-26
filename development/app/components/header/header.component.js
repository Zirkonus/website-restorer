/*
 Header
 created by Oza / 27-03-2017
 */

(function () {
    'use strict';

    angular.module('App').component('header', {
        templateUrl: '/app/components/header/header.html',
        bindings: {

        },
        controllerAs: 'vm',
        controller: headerController
    });

    headerController.$inject = ['$lang', 'notificationChecker', 'transport', 'loadData', '$state', '$window'];

    function headerController($lang, notificationChecker, transport, loadData, $state, $window) {
        var vm = this;

        vm.lang = $lang;
        vm.userDropdownOpened = false;
        vm.notificationChecker = notificationChecker;

        notificationChecker.startCron();

        transport
            .go('/user/get')
            .then(loadData.success, loadData.error)
            .then(function(data) {
                vm.userData = {
                    name: capitalize(data.name),
                    username: capitalize(data.username),
                    credits: data.credits,
                    avatarStr: data.name.charAt(0).toUpperCase() + data.username.charAt(0).toUpperCase()
                };
            })
            .catch(function(err) {});

        vm.menu = {
            opened: false
        };

        vm.userDropdownConfig = [
            {
                title: 'My Account',
                icon: 'icon-icon-10',
                callback: goToAccount
            },
            {
                title: 'Log Out',
                icon: 'icon-icon-48',
                callback: goToLogout
            }
        ];

        //
        //
        //

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function goToAccount() {
            vm.userDropdownOpened = false;
            $state.go('account');
        }

        function goToLogout() {
            vm.userDropdownOpened = false;
            $window.location = '/logout';
        }

    }

})();


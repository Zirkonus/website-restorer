/**
 * Created by Oza on 13.12.2016.
 */


(function () {
    'use strict';

    angular.module('App').component('account', {
        templateUrl: '/app/components/account/account.html',
        bindings: {
            userData: '<'
        },
        controllerAs: 'vm',
        controller: accountController
    });

    accountController.$inject = ['$lang', 'changePassDialogControl'];

    function accountController ($lang, changePassDialogControl) {

        var vm = this;

        vm.lang = $lang;

        // vm.$onInit = function() {
        //     console.log(vm.projectsListData);
        // };

        vm.changePassDialogOpen = changePassDialogOpen;

        //
        //
        //

        function changePassDialogOpen() {
            changePassDialogControl.configure(true);
        }


    }

})();

/**
 * Created by Oza on 13.12.2016.
 */

(function () {
    'use strict';

    angular.module('App').component('projectAdd', {
        templateUrl: '/app/components/projectAdd/projectAdd.html',
        bindings: {
        },
        controllerAs: 'vm',
        controller: projectAddController
    });

    projectAddController.$inject = ['$lang'];

    function projectAddController($lang) {
        var vm = this;

        vm.lang = $lang;

        vm.submitUrl = '/projects/create';

        vm.formData = {
            domain: '',
            niche: '',
            fetch_level_deep: '',
            ftp_address: '',
            ftp_port: '',
            ftp_folder: '/',
            ftp_username: '',
            ftp_password: ''
        };

        //
        //
        //

    }

})();



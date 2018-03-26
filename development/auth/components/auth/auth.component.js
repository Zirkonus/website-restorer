/**
 * Created by German Kushch on 04.05.2017.
 */


(function (window
) {

    'use strict';

    angular.module('App').component('auth', {
        template: '<ng-include src="vm.getTemplate()"></ng-include>',
        bindings: {
            tpl: '<'
        },
        controller: authController,
        controllerAs: 'vm'
    });

    authController.$inject = ['$lang', 'transport', 'loadData', 'CSRF_TOKEN', '$http'];

    function authController ($lang, transport, loadData, CSRF_TOKEN, $http) {

        var vm = this;

        vm.lang = $lang;

        vm.user = {};

        vm.passModel = {
            pass: false,
            pass2: false
        };

        vm.emailValid = true;

        vm.loginError = false;

        vm.getTemplate = getTemplate;

        vm.isValid = isValid;

        vm.checkPass = checkPass;

        vm.showPass = showPass;

        // vm.login = login;

        vm.token = CSRF_TOKEN;

        function getTemplate () {
            return './auth/components/auth/' + vm.tpl + '.template.html';
        };


        function isValid (email) {
            var reg = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            if (!reg.test(email))
                vm.emailValid = false;

            transport.go('check-email', {
                email: email
            })
                .then(loadData.success, loadData.error)
                .then(function (data) {
                    vm.emailValid = data;
                });
        };

        function showPass (id) {
            if (vm.passModel[id]) {
                document.getElementById(id).setAttribute('type', 'password');
            } else {
                document.getElementById(id).setAttribute('type', 'text');
            }
            vm.passModel[id] = !vm.passModel[id];
        };

        function checkPass () {
            return vm.user.pass === vm.user.pass2;
        };


        // function login () {
        //     // transport
        //     //     .go('/login', {
        //     //         password: vm.user.pass,
        //     //         email: vm.user.email,
        //     //         _token: CSRF_TOKEN
        //     //     })
        //     //     .then(loadData.success, loadData.error);
        //     $http({
        //         url: '/login',
        //         method: 'POST',
        //         headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        //         params: {
        //             password: vm.user.pass,
        //             email: vm.user.email,
        //             _token: CSRF_TOKEN
        //         }
        //     }).then(function () {
        //         console.log('ok');
        //     }, function () {
        //         console.log('error');
        //     });
        // };

        vm.$onInit = function () {
            var ee = document.getElementById('error-email');
            var el = document.getElementById('error-login');
            if (ee.getAttribute('value')) {
                vm.loginError = true;
            } else if (el.getAttribute('value')) {
                vm.loginError = true;
            }
            console.log(vm.loginError);
        };


    };

})(window);
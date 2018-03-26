/*
 Header
 created by Oza / 27-03-2017
 */

(function() {
    'use strict';

    angular.module('App').component('projectForm', {
        templateUrl: '/appAdmin/common/projectFormComponent/projectForm.html',
        bindings: {
            submitUrl: '=',
            formData: '='
        },
        controllerAs: 'vm',
        controller: projectsFormController
    });

    projectsFormController.$inject = ['$lang', 'transport', '$state'];

    function projectsFormController($lang, transport, $state) {
        var vm = this;

        vm.lang = $lang;
        vm.fieldFocusedName = '';

        vm.formSubmit = formSubmit;

        vm.isUnfocused = isUnfocused;
        vm.setFocusedFieldName = setFocusedFieldName;
        vm.isShowFieldError = isShowFieldError;
        vm.isShowFieldSuccess = isShowFieldSuccess;

        // vm.$onInit = function() {
        //     console.log(vm.submitUrl);
        //     console.log(vm.form_data);
        // };


        //
        //
        //

        function formSubmit() {
            transport
                .go(vm.submitUrl, vm.formData)
                .then(
                    function success(response) {
                        console.log(response);
                        $state.go('projectsList');
                    },
                    function error(response) {
                        console.error(response);
                    }
                )
        }

        function isUnfocused(fieldName) {
            return vm.fieldFocusedName !== fieldName;
        }

        function setFocusedFieldName(fieldName) {
            vm.fieldFocusedName = fieldName;
        }

        function isShowFieldError(field) {
            console.log(field);
            return field.$invalid && isUnfocused(field.$name) && (!field.$pristine || field.$modelValue);
        }

        function isShowFieldSuccess(field) {
            return field.$valid && vm.isUnfocused(field.$name) && (!field.$pristine || field.$modelValue);
        }

    }



})();
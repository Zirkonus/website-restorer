/*
 Header
 created by Oza / 27-03-2017
 */

(function () {
    'use strict';
    
    angular.module('App').component('projectForm', {
        templateUrl: '/app/common/projectFormComponent/projectForm.html',
        bindings: {
            submitUrl: '=',
            formData: '=',
            originEditDisable: '<'
        },
        controllerAs: 'vm',
        controller: projectsFormController
    });

    projectsFormController.$inject = ['$lang', 'transport', '$state', 'loadData'];

    function projectsFormController ($lang, transport, $state, loadData) {
        var vm = this;

        vm.lang = $lang;
        vm.fieldFocusedName = '';

        var fieldLetterLimit = 100;

        vm.formSubmit = formSubmit;
        vm.isUnfocused = isUnfocused;
        vm.setFocusedFieldName = setFocusedFieldName;
        vm.isShowFieldError = isShowFieldError;
        vm.isShowFieldSuccess = isShowFieldSuccess;
        vm.useFieldLetterLimit = useFieldLetterLimit;
        vm.checkDomainField = checkDomainField;
        vm.exitProjectForm = exitProjectForm;


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
                .then(loadData.success, loadData.error)
                .then(function () {
                    exitProjectForm();
                })
                .catch(function(err) {});
        }

        function isUnfocused (fieldName) {
            return vm.fieldFocusedName !== fieldName;
        }

        function setFocusedFieldName (fieldName) {
            vm.fieldFocusedName = fieldName;
        }

        function isShowFieldError (field) {
            return field.$invalid && isUnfocused(field.$name) && (!field.$pristine || field.$modelValue);
        }

        function isShowFieldSuccess (field) {
            return field.$valid && vm.isUnfocused(field.$name) && (!field.$pristine || field.$modelValue);
        }

        function useFieldLetterLimit () {
            var lettersAvaliable = fieldLetterLimit - vm.formData.niche.length;

            if (lettersAvaliable < 0) {
                vm.formData.niche = vm.formData.niche.slice(0, lettersAvaliable);
                return 0;
            }

            return lettersAvaliable;
        }

        function checkDomainField (form) {

            transport
                .go('projects/check', {domain: form.domain.$modelValue})
                .then(loadData.successNoAlert, loadData.error)
                .catch(function (err) {
                    if (err.errorType === 'data') {
                        form.domain.$invalid = true;
                        form.domain.$valid = false;
                        form.$invalid = true;
                        form.$valid = false;
                    }
                })
        }

        function exitProjectForm() {
            if (vm.originEditDisable) {
                $state.go('project.all', {id: $state.params.id});
            }
            else {
                $state.go('projects');
            }
        }


    }



})();


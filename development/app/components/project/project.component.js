/**
 * Created by Oza on 13.12.2016.
 */


(function () {
    'use strict';

    angular.module('App').component('project', {
        templateUrl: '/app/components/project/project.html',
        bindings: {
            projectData: '<'
        },
        controllerAs: 'vm',
        controller: projectController
    });

    projectController.$inject = ['$lang', 'transport', 'loadData', '$state', 'confirmDialogControl'];

    function projectController($lang, transport, loadData, $state, confirmDialogControl) {
        var vm = this;

        vm.lang = $lang;
        vm.projectInfoOpen = false;
        vm.filteredType = '';

        vm.deleteProjectConfirm = deleteProjectConfirm;
        vm.isFtpSettingsFull = isFtpSettingsFull;

        //
        //
        //

        function deleteProject() {
            transport.go('projects/delete', {id: vm.projectData.id})
                .then(loadData.success, loadData.error)
                .then(function() {
                    $state.go('projects');
                })
                .catch(function(err) {});
        }

        function deleteProjectConfirm() {
            confirmDialogControl.configure(vm.lang.get('project_delete_confirm_message'), true, deleteProject);
        }

        function isFtpSettingsFull() {
            var status = [
                vm.projectData.ftp_address,
                vm.projectData.ftp_folder,
                vm.projectData.ftp_password,
                vm.projectData.ftp_port,
                vm.projectData.ftp_username
            ].every(function(value) {
                return angular.isDefined(value) && value !== '';
            });

            return status;
        }

    }

})();


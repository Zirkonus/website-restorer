/**
 * Created by Oza on 13.12.2016.
 */


(function () {
    'use strict';

    angular.module('App').component('projectsList', {
        templateUrl: '/appAdmin/components/projectsList/projectsList.html',
        bindings: {
            projectsListData: '<'
        },
        controllerAs: 'vm',
        controller: projectsListController
    });

    projectsListController.$inject = ['$lang'];

    function projectsListController ($lang) {

        var vm = this;

        vm.lang = $lang;
        vm.increaseLimit = increaseLimit;
        vm.projectsLimit = 6;

        vm.sortByDate = sortByDate;

        // vm.$onInit = function() {
        //     console.log(vm.projectsListData);
        // };

        //
        //
        //

        function increaseLimit() {
            vm.projectsLimit += 3;
        }

        function sortByDate(project) {
            return new Date(project.updated_at);
        }

    }

})();

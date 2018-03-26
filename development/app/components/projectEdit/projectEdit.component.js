/**
 * Created by Oza on 13.12.2016.
 */

(function () {
    'use strict';

    angular.module('App').component('projectEdit', {
        templateUrl: '/app/components/projectEdit/projectEdit.html',
        bindings: {
            projectData: '<'
        },
        controllerAs: 'vm',
        controller: projectEditController
    });

    projectEditController.$inject = ['$lang'];

    function projectEditController($lang) {
        var vm = this;

        vm.lang = $lang;

        vm.submitUrl = '/projects/edit';

        //
        //
        //

    }

})();





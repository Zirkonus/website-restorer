/**
 * Created by Oza on 03.04.2017.
 */


(function () {
    'use strict';

    angular.module('App').component('editor', {
        templateUrl: '/app/components/editor/editor.html',
        bindings: {
            projectData: '<',
            projectId: '<'
        },
        controllerAs: 'vm',
        controller: editorController
    });

    editorController.$inject = ['$lang'];

    function editorController($lang) {
        var vm = this;

        vm.lang = $lang;


        //
        //
        //

    }

})();




/**
 * Created by German Kushch on 25.04.2017.
 */


(function () {

    'use strict';


    angular.module('App').component('paginationTpl',{
        templateUrl: '/appAdmin/common/pagination/pagination.template.html',
        bindings: {
            pageNum: '=',
            usersCount: '='
        },
        controller: paginationController,
        controllerAs: 'vm'
    });

    paginationController.$inject = ['$lang', '$scope']

    function paginationController ($lang, $scope) {

        var vm = this;

        vm.lang = $lang;

        vm.paginationModel = [];

        vm.clickArrow = clickArrow;

        vm.$onInit = function () {
            formModel();
        };

        $scope.$watch(function () {
            return vm.usersCount;
        }, function () {
            formModel();
        });


        function formModel () {
            vm.paginationModel = [];
            var pageCount = Math.ceil(vm.usersCount / 10);
            for (var i = 1; i <= pageCount; i++)
                vm.paginationModel.push(i);
        }


        function clickArrow (val) {
            if (vm.pageNum + val > vm.paginationModel.length) {
                vm.pageNum = vm.paginationModel.length;
                return;
            }
            else if (vm.pageNum + val < 1) {
                vm.pageNum = 1;
                return
            }

            vm.pageNum += val;
        }

    };





})();
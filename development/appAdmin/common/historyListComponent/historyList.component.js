/*
 History list component
 created by Oza / 28-03-2017
 */

(function () {
    'use strict';

    angular.module('App').component('historyList', {
        templateUrl: '/appAdmin/common/historyListComponent/historyList.html',
        bindings: {
            // listData: '=',
            userInfo: '=',
            // userId: '<'
        },
        controller: historyListController,
        controllerAs: 'vm'
    });

    historyListController.$inject = ['$lang', '$sce', 'transport', 'loadData', '$stateParams'];

    function historyListController($lang, $sce, transport, loadData, $stateParams) {
        var vm = this;

        vm.lang = $lang;
        vm.sce = $sce;
        vm.listData = {};
        vm.listData.history = [];
        vm.listData.is_more = null;

        vm.currPaginationPage = 0;

        vm.listLoadMore = listLoadMore;
        // vm.listLoadMore();

        vm.$onInit = function () {
            vm.listLoadMore();
            console.log($stateParams.userId);
        };

        //
        //
        //

        function listLoadMore() {
            transport
                .go('admin/user/history', {
                    page: vm.currPaginationPage + 1,
                    id: parseInt($stateParams.userId)
                })
                .then(loadData.success, loadData.error)
                .then(function (data) {
                    vm.currPaginationPage++;
                    // if (vm.listData.length === 0)
                    vm.listData.history = vm.listData.history.concat(data.history);
                    vm.listData.is_more = data.is_more;
                })
                .catch(function (err) {

                });
        }

    }
})();


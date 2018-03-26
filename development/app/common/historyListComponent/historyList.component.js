/*
 History list component
 created by Oza / 28-03-2017
 */

(function () {
    'use strict';

    angular.module('App').component('historyList', {
        templateUrl: '/app/common/historyListComponent/historyList.html',
        bindings: {
            listData: '='
        },
        controller: historyListController,
        controllerAs: 'vm'
    });

    historyListController.$inject = ['$lang', '$sce', 'transport', 'loadData'];

    function historyListController($lang, $sce, transport, loadData) {
        var vm = this;

        vm.lang = $lang;
        vm.sce = $sce;

        vm.currPaginationPage = 1;

        vm.listLoadMore = listLoadMore;

        //
        //
        //

        function listLoadMore() {
            transport
                .go('/history/list', {page: vm.currPaginationPage + 1})
                .then(loadData.success, loadData.error)
                .then(function (data) {
                    vm.currPaginationPage++;
                    vm.listData.history = vm.listData.history.concat(data.history);
                    vm.listData.is_more = data.is_more;
                })
                .catch(function (err) {});
        }

    }
})();


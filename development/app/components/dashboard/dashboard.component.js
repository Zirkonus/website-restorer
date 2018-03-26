/**
 * Created by Oza on 13.12.2016.
 */


(function () {
    'use strict';

    angular.module('App').component('dashboard', {
        templateUrl: '/app/components/dashboard/dashboard.html',
        bindings: {
            projectsListData: '<',
            historyListData: '<'
        },
        controllerAs: 'vm',
        controller: dashboardController
    });

    dashboardController.$inject = ['$lang', '$state', '$document', '$timeout'];

    function dashboardController ($lang, $state, $document, $timeout) {

        var vm = this;

        vm.lang = $lang;

        angular.element(initHistoryScroll);


        //
        //
        //

        function initHistoryScroll() {

            var offset = 50;
            var duration = 300;
            var delay = 300;

            $timeout(function() {

                if ($state.params['#']) {
                    var element = angular.element($document[0].getElementById($state.params['#']));

                    $document.scrollToElementAnimated(element, offset, duration);
                }

            }, delay);

        }

    }

})();

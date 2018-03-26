/*
 Language system service
 created by Oza / 28-12-2016
 */

(function () {
    'use strict';

    angular.module('App').factory('notificationChecker', notificationChecker);

    notificationChecker.$inject = ['$interval', 'transport', 'loadData'];

    function notificationChecker ($interval, transport, loadData) {

        var notificationCheckDelay = 5000;
        var notificationCheckCron = null;

        var service = {
            notificationFlag: false,
            startCron:  startCron,
            stopCron: stopCron,
            forceSet: forceSet,
            forceClear: forceClear
        };

        return service;

        //
        //
        //

        function startCron() {

            notificationCheckCron = $interval(function() {

                return transport
                    .go('/history/check')
                    .then(loadData.success, loadData.error)
                    .then(function(data) {
                        // console.log(data);
                        service.notificationFlag = data;

                    });

            }, notificationCheckDelay);

            return notificationCheckCron;

        }

        function stopCron() {
            return $interval.cancel(notificationCheckCron);
        }

        function forceSet() {
            service.notificationFlag = true;
        }

        function forceClear() {
            service.notificationFlag = false;
        }

    }

})();
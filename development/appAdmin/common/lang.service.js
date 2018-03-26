/*
 Language system service
 created by Oza / 28-12-2016
 */

(function () {
    'use strict';

    angular.module('App').factory('$lang', langService);

    langService.$inject = ['$window'];

    function langService ($window) {
        // var self = this;

        var localeData = $window.localeData;

        var service = {
            get: get,
            init: init
        };

        return service;

        //
        //
        //

        function get(key) {
            if (angular.isDefined(localeData[key])) {
                return localeData[key];
            }
            else {
                return key;
            }
        }

        function init(obj) {
            // localeData = Object.assign(localeData, obj);
            // localeData = obj;

            for (var prop in obj) {
                if (obj.hasOwnProperty(prop)) {
                    localeData[prop] = obj[prop];
                }
            }
        }

    }

})();
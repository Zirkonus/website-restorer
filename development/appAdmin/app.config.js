/*
  Main app module config
  created by German / 11-12-2016
*/

(function (window, angular) {
  'use strict';

  angular.module('App')
    .config([
        '$interpolateProvider',
        function ($interpolateProvider) {

            $interpolateProvider.startSymbol("[[");
            $interpolateProvider.endSymbol("]]");

        }
    ]);

})(window, window.angular);
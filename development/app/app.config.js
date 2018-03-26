/*
  Main app module config
  created by German / 11-12-2016
*/

(function (window, angular) {
  'use strict';

  angular.module('App')
    .config([
        '$interpolateProvider',
        '$anchorScrollProvider',
        'ngDialogProvider',
        function ($interpolateProvider, $anchorScrollProvider, ngDialogProvider) {

            $interpolateProvider.startSymbol("[[");
            $interpolateProvider.endSymbol("]]");

            $anchorScrollProvider.disableAutoScrolling();

            ngDialogProvider.setDefaults({
                className: 'ngdialog-theme-default',
                plain: false,
                showClose: true,
                closeByNavigation: true,
                closeByDocument: true,
                closeByEscape: true
            });
        }
    ]);

})(window, window.angular);
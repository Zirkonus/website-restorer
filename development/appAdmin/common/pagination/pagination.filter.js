/**
 * Created by German Kushch on 25.04.2017.
 */


(function () {

    'use strict';


    angular.module('App').filter('paginationFilter', function () {
        return function (arr, pageNum) {

            var filtered = [];

            var boundary = 2;

            var bL = pageNum - boundary,
                bR = pageNum + boundary;

            if (bL < arr[0]) {
                bR += boundary
                bL = 1;
            } else if (bR > arr[arr.length - 1]) {
                bL -= boundary;
                bR = arr.length;
            }

            if (!pageNum)
                return arr;

            for (var i = 0; i < arr.length; i++) {
                if (arr[i] >= bL && arr[i] <= bR) {
                    filtered.push(arr[i]);
                }
            }

            if (pageNum > 5) {
                filtered.unshift('..');
                filtered.unshift(arr[0]);
            }
            if (pageNum < arr.length - 4) {
                filtered.push('..');
                filtered.push(arr[arr.length - 1]);
            }


            return filtered;
        };
    });


})();
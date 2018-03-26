/*
 Language system service
 created by Oza / 28-12-2016
 */

(function() {
    'use strict';

    angular.module('App').factory('loadData', loadData);

    loadData.$inject = [];

    function loadData() {
        // var self = this;

        var service = {
            success: success,
            errorResponse: errorResponse,
            error: error
        };

        return service;

        //
        //
        //

        function success(response) {
            if (+response.data.errors.status !== 0) {
                errorResponse(response.data.errors);
            }

            console.dir(response.data.data);

            return response.data.data;
        }

        function error(response) {
            console.error(response);
        }

        function errorResponse(error) {
            console.error(error.message);
            throw new Error(error)
        }

    }

})();
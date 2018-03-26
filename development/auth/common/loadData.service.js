/*
 Language system service
 created by Oza / 28-12-2016
 */

(function () {
    'use strict';

    angular.module('App').factory('loadData', loadData);

    // loadData.$inject = ['errorDialogControl'];
    loadData.$inject = [];

    // function loadData (errorDialogControl) {
    function loadData () {

        var service = {
            success: success,
            successNoAlert: successNoAlert,
            error: error
        };

        CustomError.prototype = Object.create(Error.prototype);

        return service;

        //
        //
        //

        function success(response) {
            if (+response.data.errors.status !== 0) {
                errorData(response.data.errors, true);
            }

            console.log(response.data.data);

            return response.data.data;
        }

        function successNoAlert(response) {
            if (+response.data.errors.status !== 0) {
                errorData(response.data.errors, false);
            }

            console.log(response.data.data);

            return response.data.data;
        }

        function error(response) {

            errorDialogControl.configure(true, {
                title: 'Error Server response',
                message: response.status + ' ' + response.statusText,
                type: 'error',
                confirmButtonEnable: true,
                confirmButtonText: 'Ok',
                rejectButtonEnable: false
            });

            throw new CustomError(response.status + ' ' + response.statusText, 'server');
        }

        function errorData(error, alertOn) {

            if (alertOn) {
                errorDialogControl.configure(true, {
                    title: 'Error Response data',
                    message: error.message,
                    type: 'error',
                    confirmButtonEnable: true,
                    confirmButtonText: 'Ok',
                    rejectButtonEnable: false
                });
            }

            // console.log(customError.errorType);

            throw new CustomError(error.message, 'data');
        }

        // Custom Event constructor
        function CustomError(message, errorType) {
            this.name = "CustomError";

            this.errorType = errorType;
            this.message = message;

            if (Error.captureStackTrace) {
                Error.captureStackTrace(this, CustomError);
            } else {
                this.stack = (new Error()).stack;
            }

        }

    }

})();
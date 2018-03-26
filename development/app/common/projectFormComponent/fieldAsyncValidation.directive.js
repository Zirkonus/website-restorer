/*
 Runs callback at click outside element
 created by Oza / 12-01-2016
 */


(function () {
    'use strict';

    angular
        .module('App')
        .directive('fieldAsyncValidation', fieldAsyncValidation);

    fieldAsyncValidation.$inject = ['transport'];

    function fieldAsyncValidation(transport) {
        return {
            restrict: 'A',
            scope: {
            },
            require: "ngModel",
            link: function (scope, elem, attr, ngModelController) {

                ngModelController.$asyncValidators.usernameAvailable = function(value) {

                    console.log(value);

                    return transport
                        .go('projects/check', {domain: value})
                        .then(
                            function success(response) {
                                console.log(response);
                                if (response.data.errors.status === 1) {
                                    return new Error(response.data.errors);
                                }
                            }
                        )

                }
            }
        }
    }

})();
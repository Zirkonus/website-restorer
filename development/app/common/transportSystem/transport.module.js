/*
 Transport system module
 created by German / 09-12-2016
 */

(function () {
    'use strict';

    angular.module('yaTransport', [])
        .constant('STATES', {
            OK: 1,
            ScenarioError: 2,
            ServerAccessError: 3,
            TechnicalWorks: 4,
            FatalTechnical: 5,
            ServerOverload: 6
        })
        .constant('METHODS', [
            'http',
            'websocket'
        ])
        .factory('transport', ['$http', 'STATES', 'METHODS', function ($http, STATES, METHODS) {

            var self = this;

            self.STATES = STATES;

            self.methods = METHODS;

            var currentMethod = 'http';

            //state of website and transport service (private field)
            var currentState = null;

            // main function for request sending
            self.go = function (URL, params) {

                if (isHttp()) {
                    return goHttp(URL, params);
                } else if (isSocket()) {
                    return goSocket(URL, params);
                }

                // self.currentMethod();
            };

            self.getCurrentMethod = function () {
                return currentMethod;
            };

            //function which check the conncection and working state
            self.checkInternetConnection = function () {
                $http.jsonp('https://www.google.com.ua/')
                    .success(function (data, status) {
                        console.log("Success");
                        console.log(status);
                    })
                    .error(function (data, status) {
                        console.log("Error");
                        console.log(status);
                    });
            };

            function goHttp(URL, params) {
                var config = {
                    method: "POST",
                    url: URL,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    data: params
                };

                return $http(config);
            }

            function goSocket(URL, params) {
                console.log('there is no socket');
            }

            function getHttpMethod() {
                //...
                return "POST";
            };

            function isHttp() {
                return currentMethod === "http";
            };

            function isSocket() {
                return currentMethod === "socket";
            };

            return self;
        }]);

})();
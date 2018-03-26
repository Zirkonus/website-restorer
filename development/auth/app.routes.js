/*
 Main app module config
 created by Oza / 27-03-2017
 */

(function () {
    'use strict';

    angular.module('App')
        .config([
            '$stateProvider',
            '$locationProvider',
            '$urlRouterProvider',
            function ($stateProvider, $locationProvider, $urlRouterProvider) {

                var states = [
                    {
                        name: 'login',
                        url: '/login',
                        component: 'auth',
                        resolve: {
                            tpl: function () { return 'login'; }
                        }
                    },
                    {
                        name: 'registration',
                        url: '/registration',
                        component: 'auth',
                        resolve: {
                            tpl: function () { return 'registration'; }
                        }
                    },
                ];

                // Loop over the state definitions and register them
                states.forEach(function(state) {
                    $stateProvider.state(state);
                });
                // Default route
                $urlRouterProvider.otherwise("/login");
                // Set hashbang prefix
                $locationProvider.hashPrefix('');

            }

        ]);

})();
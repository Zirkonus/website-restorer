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
                        name: 'dashboard',
                        url: '/dashboard',
                        component: 'dashBoard',
                        resolve: {
                            generalInfo: function (loadData, transport) {
                                return transport.go('admin/general-info')
                                    .then(loadData.success, loadData.error);
                            }
                        }
                    },
                    {
                        name: 'userList',
                        url: '/user-list',
                        component: 'userList',
                        resolve: {
                            // userList: function (transport, loadData) {
                            //     return transport
                            //         .go('admin/user-list')
                            //         .then(loadData.success, loadData.error);
                            // }
                        }
                    },
                    {
                        name: 'user',
                        url: '/user/:userId',
                        component: 'userHistory',
                        resolve: {
                            userInfo: function (transport, loadData, $stateParams) {
                                return transport
                                    .go('admin/user', {
                                        id: $stateParams.userId
                                    })
                                    .then(loadData.success, loadData.error);
                            }
                        }
                    }
                ];

                // Loop over the state definitions and register them
                states.forEach(function(state) {
                    $stateProvider.state(state);
                });

                // Default route
                $urlRouterProvider.otherwise("/dashboard");

                // Set hashbang prefix
                $locationProvider.hashPrefix('');

            }

        ]);

})();
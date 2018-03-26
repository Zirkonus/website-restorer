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
                    url: '/',
                    component: 'dashboard'
                },
                {
                    name: 'projects',
                    url: '/projects',
                    component: 'projects',
                    resolve: {
                        projectsListData: ['transport', 'loadData', function(transport, loadData) {
                            return transport
                                .go('/projects/list', '')
                                .then(loadData.success, loadData.error);
                        }],
                        historyListData: ['transport', 'loadData', function(transport, loadData) {
                            return transport
                                .go('/history/list')
                                .then(loadData.success, loadData.error);
                        }]
                    }
                },
                {
                    name: 'projectAdd',
                    url: '/project-add',
                    component: 'projectAdd'
                },
                {
                    name: 'projectEdit',
                    url: '/project-edit/{id:int}',
                    component: 'projectEdit',
                    resolve: {
                        projectData: ['$stateParams', 'transport', 'loadData', function ($stateParams, transport, loadData) {
                            return transport
                                .go('/projects/get', {id: $stateParams.id})
                                .then(loadData.success, loadData.error);
                        }]
                    }
                },
                {
                    abstract: true,
                    name: 'project',
                    url: '/project/{id:int}',
                    component: 'project',
                    resolve: {
                        projectData: ['$stateParams', 'transport', 'loadData', function ($stateParams, transport, loadData) {
                            return transport
                                .go('/projects/get', {id: $stateParams.id})
                                .then(loadData.success, loadData.error);
                        }]
                    }
                },
                {
                    name: 'project.all',
                    url: '/',
                    component: "versionsList",
                    resolve: {
                        versionsData: ['$stateParams', 'transport', 'loadData', function($stateParams, transport, loadData) {
                            return transport
                                .go('/versions/list', {id: $stateParams.id})
                                .then(loadData.success, loadData.error);
                        }],
                        emptyListMessage: ['$lang', function($lang) {
                            return $lang.get('project_versions_list_all_empty_message');
                        }]
                    }
                },
                {
                    name: 'project.inProgress',
                    url: '/in-progress/',
                    component: "versionsList",
                    resolve: {
                        versionsData: ['$stateParams', 'transport', 'loadData', function($stateParams, transport, loadData) {
                            return transport
                                .go('/versions/list/in_progress', {id: $stateParams.id})
                                .then(loadData.success, loadData.error);
                        }],
                        emptyListMessage: ['$lang', function($lang) {
                            return $lang.get('project_versions_list_in_progress_empty_message')
                        }]
                    }
                },
                {
                    name: 'project.restored',
                    url: '/restored/',
                    component: "versionsList",
                    resolve: {
                        versionsData: ['$stateParams', 'transport', 'loadData', function($stateParams, transport, loadData) {
                            return transport
                                .go('/versions/list/restored', {id: $stateParams.id})
                                .then(loadData.success, loadData.error);
                        }],
                        emptyListMessage: ['$lang', function($lang) {
                            return $lang.get('project_versions_list_restored_empty_message')
                        }]
                    }
                },
                {
                    abstract: true,
                    name: 'editor',
                    url: '/editor/{projectId:int}/{versionId:int}',
                    component: "editor",
                    resolve: {
                        projectData: ['$stateParams', 'transport', 'loadData', function($stateParams, transport, loadData) {
                            return transport
                                .go('/projects/get', {id: $stateParams.projectId})
                                .then(loadData.success, loadData.error);
                        }],
                        projectId: ['$stateParams', function($stateParams) {
                            return $stateParams.projectId
                        }]
                    }
                },
                {
                    name: 'editor.layout',
                    url: '/',
                    component: 'editorLayout',
                    resolve: {
                        layoutListData: ['$stateParams', 'transport', 'loadData', function($stateParams, transport, loadData) {
                            return transport
                                .go('/versions/pages', {id: $stateParams.versionId})
                                .then(loadData.success, loadData.error);
                        }]
                    }
                },
                {
                    name: 'editor.images',
                    url: '/images',
                    component: 'editorGallery',
                    resolve: {
                        imagesListData: ['$stateParams', 'transport', 'loadData', function($stateParams, transport, loadData) {
                            return transport
                                .go('/versions/images', {id: $stateParams.versionId})
                                .then(loadData.success, loadData.error);
                        }]
                    }
                },
                {
                    name: 'visualEditor',
                    url: '/visual-editor/{projectId:int}/{versionId:int}/{pageId:int}/',
                    component: 'visualEditor',
                    resolve: {
                        projectData: ['$stateParams', 'transport', 'loadData', function($stateParams, transport, loadData) {
                            return transport
                                .go('/projects/get', {id: $stateParams.projectId})
                                .then(loadData.success, loadData.error);
                        }],
                        pageCode: ['$stateParams', '$http', 'loadData', function($stateParams, $http, loadData) {
                            return $http
                                .get('getpage=' + $stateParams.pageId)
                                .then(
                                    function(response) {
                                        return response.data;
                                    },
                                    loadData.error);
                        }]
                    }
                },
                {
                    name: 'account',
                    url: '/account',
                    component: 'account',
                    resolve: {
                        userData: ['$stateParams', 'transport', 'loadData', function ($stateParams, transport, loadData) {
                            return transport
                                .go('/user/get')
                                .then(loadData.success, loadData.error);
                        }]
                    }
                }

            ];

            // Loop over the state definitions and register them
            states.forEach(function(state) {
                $stateProvider.state(state);
            });

            // Default route
            $urlRouterProvider.otherwise("/");

            // Set hashbang prefix
            $locationProvider.hashPrefix('');

        }

    ]);

})();
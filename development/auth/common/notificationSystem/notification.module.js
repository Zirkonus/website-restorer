/*
  Transport system module
  created by German / 11-12-2016
*/

(function (window, angular) {
  'use strict';

  angular.module('yaNotification', [])
    .constant('Events', {
      messageRecieved: "notication: messageRecieved",
      serverError: "notication: serverError",
      serverAccesError: "notification: serverAccesError",
      technicalWorks: "notification: technicalWorks"
    })
    .factory('$notification', ['$rootScope', 'Events', function ($rootScope, Events) {
      var self = this;

      self.Events = Events;

      self.messageRecieved = function (payload) {
        $rootScope.$broadcast(self.Events.messageRecieved, payload);
      };

      self.serverError = function (payload) {
        $rootScope.$broadcast(self.Events.serverError, payload);
      };

      self.serverAccesError = function (payload) {
        $rootScope.$broadcast(self.Events.serverAccesError, payload);
      };

      self.technicalWorks = function (payload) {
        $rootScope.$broadcast(self.Events.technicalWorks, payload);
      };

      return self;
    }]);

})(window, window.angular);
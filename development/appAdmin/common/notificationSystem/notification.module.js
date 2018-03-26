/*
  Transport system module
  created by German / 11-12-2016
*/

(function (window, angular) {
  'use strict';

  angular.module('yaNotification', [])
    .constant('Events', {
      userCreated: "notification: userCreated",
    })
    .factory('$notification', ['$rootScope', 'Events', function ($rootScope, Events) {
      var self = this;

      self.Events = Events;

      self.userCreated = function (payload) {
        $rootScope.$broadcast(self.Events.userCreated, payload);
      };

      return self;
    }]);

})(window, window.angular);
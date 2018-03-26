/*
 Main app module run config
 created by Oza / 08-01-2017
 */

(function () {
    'use strict';

    angular.module('App').run(runInitialization);


    runInitialization.$inject = ['$rootScope', '$window', '$document', '$transitions'];

    function runInitialization($rootScope, $window, $document, $transitions) {

        var screenType = detectScreenType();

        $rootScope.scrollFreeze = false;
        $rootScope.hasVScroll = $document[0].body.clientHeight > $window.innerHeight;

        angular.element($window).bind('resize', resizeHandler);

        $rootScope.isScreenMobile = function () {
            return screenType === 'mobile';
        };

        $rootScope.isScreenTablet = function () {
            return screenType === 'tablet';
        };

        $rootScope.isScreenLaptop = function () {
            return screenType === 'laptop';
        };

        $rootScope.isScreenDesktop = function () {
            return screenType === 'desktop';
        };

        // $rootScope.$on('$stateChangeSuccess', function () {
        //     $document[0].body.scrollTop = $document[0].documentElement.scrollTop = 0;
        // });

        $transitions.onSuccess({}, function () {
            $document[0].body.scrollTop = $document[0].documentElement.scrollTop = 0;
        });

        $rootScope.$on('setScrollFreeze', function (event, data) {
            $rootScope.scrollFreeze = data;
        });

        $rootScope.$watch(function(){return $document[0].body.clientHeight}, function () {
            $rootScope.hasVScroll = $document[0].body.clientHeight > $window.innerHeight;
        });


        //
        //
        //

        function detectScreenType() {
            if ($window.innerWidth < 768) return 'mobile';
            if ($window.innerWidth < 1024) return 'tablet';
            if ($window.innerWidth < 1200) return 'laptop';

            return 'desktop';

        }

        function resizeHandler() {
            var currScreenType = detectScreenType();

            console.log($rootScope.hasVScroll);

            if (currScreenType !== screenType) {
                screenType = currScreenType;
                $rootScope.$digest();
            }

        }

    }

})();
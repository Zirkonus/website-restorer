/*
 Runs callback at click outside element
 created by Oza / 12-01-2016
 */


(function () {
    'use strict';

    angular
        .module('App')
        .directive('ckEditor', ckEditor);

    ckEditor.$inject = ['$timeout'];

    function ckEditor($timeout) {
        return {
            require: '?ngModel',
            restrict: 'A',
            scope: {
                editorHeight: '=',
                loadInternal: '&'
            },
            link: function (scope, elm, attr, model) {
                var isReady = false;
                var data = [];
                var ck = CKEDITOR.replace(elm[0], {
                    fullPage: true,
                    extraPlugins: 'docprops',
                    // Disable content filtering because if you use full page mode, you probably
                    // want to  freely enter any HTML content in source mode without any limitations.
                    allowedContent: true
                });

                function setData() {
                    if (!data.length) { return; }

                    var d = data.splice(0, 1);
                    ck.setData(d[0] || '<span></span>', function () {
                        setData();
                        isReady = true;
                    });
                }

                ck.on('instanceReady', function (e) {
                    e.editor.resize("100%", scope.editorHeight);
                    if (model) { setData(); }

                    $timeout(function() {
                        scope.loadInternal()({
                            resetDirty: e.editor.resetDirty.bind(e.editor),
                            checkDirty: e.editor.checkDirty.bind(e.editor)
                        });

                        e.editor.resetDirty();

                    }, 500);

                });

                elm.bind('$destroy', function () {
                    ck.destroy(false);
                });

                if (model) {
                    ck.on('change', function () {
                        scope.$apply(function () {
                            var data = ck.getData();
                            if (data == '<span></span>') {
                                data = null;
                            }
                            model.$setViewValue(data);
                        });
                    });

                    model.$render = function (value) {
                        if (model.$viewValue === undefined) {
                            model.$setViewValue(null);
                            model.$viewValue = null;
                        }

                        data.push(model.$viewValue);

                        if (isReady) {
                            isReady = false;
                            setData();
                        }
                    };
                }

            }
        };
    }

})();
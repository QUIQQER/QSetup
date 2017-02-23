window.addEvent("domready", function () {
    "use strict";

    require(['qui/controls/buttons/Button'], function () {
    });


    require(['bin/js/Setup'], function (Setup) {
        var SetupControl = new Setup();

        SetupControl.load();
    });

    document.getElement('#huh').addEvent('click', function (event) {
        event.stop();

        new Request({
            url      : '/ajax/test.php',
            data     : {test: "testValue", testVariable: "test variable ohne Ende"},
            onSuccess: function (response) {
                console.log(response);
                document.getElement('.loading').set('html', response);
            },
            onRequest: function () {
                document.getElement('.loading').set('html', '<h3>Loading...</h3>');
            },
            onError  : function () {
                console.log("BIG error");
            }

        }).send();

    });
});
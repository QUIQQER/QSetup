window.addEvent("domready", function () {
    "use strict";

    require(['qui/controls/buttons/Button'], function () {
    });


    require(['bin/js/Setup'], function (Setup) {
        var SetupControl = new Setup();

        SetupControl.load();
    });
});
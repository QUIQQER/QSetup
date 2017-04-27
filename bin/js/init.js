window.addEvent("domready", function () {
    "use strict";
    require(['bin/js/Setup'], function (Setup) {
        var SetupControl = new Setup();
        SetupControl.systemCheck();
    });
});
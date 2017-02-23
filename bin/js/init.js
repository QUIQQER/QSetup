window.addEvent("domready", function () {
    "use strict";

    require(['qui/controls/buttons/Button'], function () {
    });


    require(['bin/js/Setup'], function (Setup) {
        var SetupControl = new Setup();

        SetupControl.load();
    });

    /*var regNumber   = new RegExp(/^\d+$/);

    document.getElement('input[type="number"]').addEvent('keydown', function (event) {
        var inputNumber = document.getElement('input[type="number"]');
        if (regNumber.test(document.getElement('input[type="number"]').value)) {
            console.log("number!");
            return;
        }
        console.log("not number...");
        inputNumber.slice(0, inputNumber.length - 1);
    });*/
});
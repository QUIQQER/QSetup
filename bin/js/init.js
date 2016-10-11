window.addEvent("domready", function () {
    "use strict";

    require([

        'qui/controls/buttons/Button'

    ], function(QUIControl){});



    (function() {
        moofx(document.getElements('.fa')).animate({
            opacity: 0
        })
    }).delay(2000);

});
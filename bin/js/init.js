window.addEvent("domready", function () {
    "use strict";

    require(['qui/controls/buttons/Button'], function(){});

    var nextStep = document.getElement('.next-button');
    var backStep = document.getElement('.back-button');
    var stepsList = document.getElement('.steps-list');
    var listElement = document.getElements('.step');
    var listElementWidth = document.getElement('.step').getSize().x;


    var currentPos, pos;

    nextStep.addEvent('click', function() {
        currentPos = stepsList.getStyle('left').toInt();

        if (!currentPos == ((listElement.length-1) * listElementWidth )) {
            return;
        }

        pos = currentPos - listElementWidth;

        moofx(stepsList).animate({
            left: pos
        },{
            duration: 500
        });
    });

    backStep.addEvent('click', function() {
        currentPos = stepsList.getStyle('left').toInt();

        if (currentPos == 0) {
            return;
        }
        pos = currentPos + listElementWidth;

        moofx(stepsList).animate({
             left: pos
         },{
             duration: 500
         });
    });


});
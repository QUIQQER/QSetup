window.addEvent("domready", function () {
    "use strict";

    require(['qui/controls/buttons/Button'], function(){});



    require(['bin/js/Setup'], function(Setup) {
        var SetupControl = new Setup();

        SetupControl.load();
    });
    // require(['bin/js/test'], function(Test) {
    //     new Test();
    // });

    // var nextStep = document.getElement('.next-button');
    // var backStep = document.getElement('.back-button');
    // var stepsList = document.getElement('.steps-list');
    // var listElement = document.getElements('.step');
    // var listElementWidth = document.getElement('.step').getSize().x;
    // var navList = document.getElement('.nav-list');
    // var liElm = navList.getElements('li');
    // var fa = navList.getElements('.fa');
    //
    // var step = 0;
    //
    // backStep.disabled = true;
    // var currentPos, pos;
    //
    // nextStep.addEvent('click', function() {
    //     currentPos = stepsList.getStyle('left').toInt();
    //
    //     console.log(1);
    //     if (currentPos == ((listElement.length-1) * -listElementWidth )) {
    //         console.log("es gibt nichts mehr");
    //         return;
    //     }
    //     // nav icons
    //     fa[step].removeClass('fa-square-o');
    //     fa[step].addClass('fa-check-square-o');
    //
    //     // nav color
    //     liElm[step].removeClass('step-active');
    //     liElm[step].addClass('step-done');
    //     liElm[step + 1].addClass('step-active');
    //
    //     step++;
    //
    //     if (step > 0) {
    //         backStep.disabled = false;
    //     }
    //
    //     console.log(step);
    //     var pos = currentPos - listElementWidth;
    //
    //     moofx(stepsList).animate({
    //         left: pos
    //     },{
    //         duration: 500
    //     });
    // });
    //
    // backStep.addEvent('click', function() {
    //     currentPos = stepsList.getStyle('left').toInt();
    //
    //     if (currentPos == 0) {
    //         return;
    //     }
    //
    //     // nav icons
    //     fa[step - 1].removeClass('fa-check-square-o');
    //     fa[step - 1].addClass('fa-square-o');
    //
    //     // nav color
    //     liElm[step].removeClass('step-active');
    //     liElm[step -1 ].removeClass('step-done');
    //     liElm[step - 1].addClass('step-active');
    //     step--;
    //     console.log(step);
    //     pos = currentPos + listElementWidth;
    //
    //     moofx(stepsList).animate({
    //          left: pos
    //      },{
    //          duration: 500
    //      });
    // });
    //
    //
    // // QUI
    // require(['qui/QUI'], function(QUI){
    //     window.addEvent('resize', QUI.debounce(function() {
    //         var w = window.outerWidth;
    //         var h = window.outerHeight;
    //         var txt = "Window size: width=" + w + ", height=" + h;
    //         console.log(txt);
    //         console.log(1);
    //     }));
    // });


// resize später
    /*window.addEvent('resize', function () {
        var w = window.outerWidth;
        var h = window.outerHeight;
        var txt = "Window size: width=" + w + ", height=" + h;
        console.log(txt);
        console.log(1);
    })*/


});
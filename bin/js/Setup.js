/**
 * Setup
 *
 * @module bin/js/Setup
 * @author www.pcsg.de (Michael Danielczok)
 *
 */

define('bin/js/Setup', [
    // 'qui/controls/Control'
], function (QUIControl) {
    "use strict";

    return new Class({

        // Extends: QUIControl,

        Type  : 'bin/js/Setup',
        Binds : [
            'load',
            'next',
            '$onImport'
        ],

        initialize: function () {
            console.log("Funktion: initialize");

            this.nextStep = null;
            this.backStep = document.getElement('.back-button');
            this.stepsList = document.
            ('.steps-list');
            this.listElement = document.getElements('.step');
            this.listElementWidth = document.getElement('.step').getSize().x;

            console.warn("nach addEvents");
        },

        // $onImport: function()
        // {
        //     console.log(111);
        //
        //     console.log(this.getElm());
        // },

        /**
         * event : on load
         */
        load: function () {

            this.nextStep = document.getElement('.next-button');

            console.log( document.getElement('.next-button'));

            this.nextStep.addEvent('click', this.next);
        },

        next: function () {
            console.log("Funktion: next");
            var currentPos = this.stepsList.getStyle('left').toInt();

            if (currentPos == ((this.listElement.length-1) * -this.listElementWidth )) {
                return;
            }

            var pos = currentPos - this.listElementWidth;

            moofx(this.stepsList).animate({
                 left: pos
             },{
                 duration: 500
             });

        },

        back: function () {
            console.log("Funktion: back");
            var currentPos = this.stepsList.getStyle('left').toInt();

            if (currentPos == 0) {
                return;
            }
            var pos = currentPos + this.listElementWidth;

            moofx(this.stepsList).animate({
                 left: pos
             },{
                 duration: 500
             });
        }.bind(this)
    });
});
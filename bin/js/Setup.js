/**
 * Setup
 *
 * @module bin/js/Setup
 * @author www.pcsg.de (Michael Danielczok)
 *
 */

define('bin/js/Setup', [
    'qui/utils/Functions'
    // 'qui/controls/Control'
], function (QUIFunctionUtils) {
    "use strict";

    return new Class({

        // Extends: QUIControl,

        Type  : 'bin/js/Setup',
        Binds : [
            'load',
            'next',
            'back',
            'recalc',
            'show'
        ],

        initialize: function () {
            this.nextStep = null;
            this.backStep = null;
            this.stepsList = null;
            this.listElement = null;
            this.listElementWidth = null;

            this.navList = null;
            this.liElm = null;
            this.fa = null;

            this.activeHeader = null;

            this.step = 0;
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
            this.backStep = document.getElement('.back-button');
            this.stepsList = document.getElement('.steps-list');
            this.listElement = document.getElements('.step');
            this.listElementWidth = document.getElement('.step').getSize().x;

            this.navList = document.getElement('.nav-list');
            this.liElm = this.navList.getElements('li');
            this.fa = this.navList.getElements('.fa');

            this.activeHeader = document.getElements('.header-list li');

            this.step = 0;

            this.nextStep.addEvent('click', this.next);
            this.backStep.addEvent('click', this.back);
            window.addEvent(
                'resize',
                QUIFunctionUtils.debounce(this.recalc, 20)
            );

            moofx(this.activeHeader[this.step]).animate({
                opacity: 1
            }, {
                duration: 500
            });

            moofx(this.stepsList).animate({
                opacity: 1
            }, {
                duration: 500
            });
        },

        /**
         * next step
         */
        next: function () {
            var currentPos = this.stepsList.getStyle('left').toInt();

            if (currentPos == ((this.listElement.length-1) * -this.listElementWidth )) {
                return;
            }

            // nav icons
            this.fa[this.step].removeClass('fa-square-o');
            this.fa[this.step].addClass('fa-check-square-o');

            // nav color
            this.liElm[this.step].removeClass('step-active');
            this.liElm[this.step].addClass('step-done');
            this.liElm[this.step + 1].addClass('step-active');

            this.step++;

            // header
            moofx(this.activeHeader).animate({
                opacity: 0
            }, {
                duration: 250,
                equation: 'ease-in-out',
                callback: function() {
                    this.show(this.step)
                }.bind(this)
            });

            if (this.step > 0) {
                this.backStep.disabled = false;
            }

            var pos = currentPos - this.listElementWidth;

            moofx(this.stepsList).animate({
                 left: pos
            }, {
                duration: 500,
                equation: 'ease-in-out'
            });

        },

        /**
         * back step
         */
        back: function () {
            console.log("Funktion: back");
            var currentPos = this.stepsList.getStyle('left').toInt();

            if (currentPos == 0) {
                return;
            }

            // nav icons
            this.fa[this.step - 1].removeClass('fa-check-square-o');
            this.fa[this.step - 1].addClass('fa-square-o');

            // nav color
            this.liElm[this.step].removeClass('step-active');
            this.liElm[this.step -1 ].removeClass('step-done');
            this.liElm[this.step - 1].addClass('step-active');

            this.step--;

            moofx(this.activeHeader).animate({
                opacity: 0
            }, {
                duration: 250,
                equation: 'ease-in-out',
                callback: function() {
                     this.show(this.step)
                }.bind(this)
            });

            var pos = currentPos + this.listElementWidth;

            moofx(this.stepsList).animate({
                 left: pos
             },{
                 duration: 300
             });
        },

        /**
         * recalc the listElement width
         */
        recalc: function() {
            this.listElementWidth = document.getElement('.step').getSize().x;
            /*for (var i = 0; i < this.listElement.length; i++) {
                this.listElement[i].setStyle('width', this.listElementWidth);
            }*/

            /*this.stepsList.setStyle(
                'left',
                this.step * -this.listElementWidth
            )*/

            this.stepsList.setStyle('opacity', 0);
            var newPos = this.step * -this.listElementWidth;

            moofx(this.stepsList).animate({
                left: newPos,
                opacity: 1
            }, {
                duration: 500
            });

            /*var pos = currentPos - this.listElementWidth;
            moofx(this.stepsList).animate({
                left: pos
            },{
                duration: 500
            });*/
            // return this.listElementWidth;
        },

        /**
         * show header
         *
         * @param step
         */
        show: function (step) {
            moofx(this.activeHeader[step]).animate({
                                                       opacity: 1
                                                   }, {
                                                       duration: 250
                                                   });
        }
    });
});
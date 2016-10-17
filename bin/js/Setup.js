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
            'show',
            'getHeaderHeight',
            'setHeaderHeight',
            'testF'
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

            this.headerList = null;
            this.headerLogoContainer = null;

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

            this.headerList = document.getElement('.header-list');
            this.headerLogoContainer = document.getElement('.header-logo-container');

            this.navList = document.getElement('.nav-list');
            this.liElm = this.navList.getElements('li');
            this.fa = this.navList.getElements('.fa');

            this.activeHeader = document.getElements('.header-list li');

            this.step = 0;

            this.nextStep.addEvent('click', this.next);
            this.backStep.addEvent('click', this.back);

            /*window.addEvents({
                resize: function() {
                    QUIFunctionUtils.debounce(this.recalc, 20);
                }
            });*/

            this.setHeaderHeight(this.getHeaderHeight());

            // wie mehrere Events hinzufügen?
            window.addEvent(
                'resize',
                QUIFunctionUtils.debounce(this.recalc, 20)
            );
            window.addEvent(
                'resize',
                QUIFunctionUtils.debounce(this.testF, 20)
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
            this.fa[this.step].removeClass('fa-circle-o');
            this.fa[this.step].addClass('fa-check-circle-o');

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
            this.fa[this.step - 1].removeClass('fa-check-circle-o');
            this.fa[this.step - 1].addClass('fa-circle-o');

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
                duration: 300,
                equation: 'ease-in-out'
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
         *
         * @returns {*}
         */
        getHeaderHeight: function() {
            var arr = [];
            var liElm = this.headerList.getElements('li');
            for (var i = 0; i < this.liElm.length; i++) {
                arr[i] = liElm[i].getSize().y;
            }

            // Was wird hier zurückgegeben? was bedeutet {*}?
            return Math.max.apply(false,arr).toInt();
        },

        setHeaderHeight: function(headerHeight) {
            /*this.headerLogoContainer.setStyle('min-height', headerHeight);
            this.headerList.setStyle('min-height', headerHeight);*/

            moofx(this.headerList).animate({
                minHeight: headerHeight,
                opacity: 1
            }, {
                duration: 500
            })

            moofx(this.headerLogoContainer).animate({
                minHeight: headerHeight
            }, {
                duration: 500
            })
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
        },

        testF: function () {
            this.setHeaderHeight(this.getHeaderHeight());
        }
    });
});
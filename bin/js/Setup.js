/**
 * Setup
 *
 * @module bin/js/Setup
 * @author www.pcsg.de (Michael Danielczok)
 *
 */

define('bin/js/Setup', [
    'qui/QUI',
    'qui/utils/Functions'
    // 'qui/controls/Control'
], function (QUI, QUIFunctionUtils) {
    "use strict";

    return new Class({

        // Extends: QUIControl,

        Type  : 'bin/js/Setup',
        Binds : [
            'load',
            'next',
            'back',
            'recalc',
            'showCurrentHeader',
            'getHeaderHeight',
            'setHeaderHeight',
            'testF',
            'countInputs'
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

            this.inputs = null;

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

            this.inputs = this.countInputs();

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

            this.$hideOnResize = false;

            window.addEvent('resize', function() {
                if (this.$hideOnResize) {
                    return;
                }

                this.$hideOnResize = true;
                this.stepsList.setStyle('opacity', 0);

                // @todo this.$hideOnResize auf false

            }.bind(this));

            QUI.addEvent('onResize', function() {
                this.recalc();
                this.testF();
            }.bind(this));

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
            /*this.fa[this.step].removeClass('fa-circle-o');
            this.fa[this.step].addClass('fa-check-circle-o');*/

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
                    this.showCurrentHeader(this.step)
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
            /*this.fa[this.step - 1].removeClass('fa-check-circle-o');
            this.fa[this.step - 1].addClass('fa-circle-o');*/

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
                     this.showCurrentHeader(this.step)
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

            this.$hideOnResize = false;

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
            moofx(this.headerList).animate({
                minHeight: headerHeight,
                opacity: 1
            }, {
                duration: 500
            });

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
        showCurrentHeader: function (step) {
            moofx(this.activeHeader[step]).animate({
                opacity: 1
            }, {
                duration: 250
            });
        },

        /*checkStep: function () {
          var radioButtons = document.getElement
        },*/

        testF: function () {
            this.setHeaderHeight(this.getHeaderHeight());
        },

        countInputs: function () {
            var inputs = document.getElements('input');
            var names = [];

            for (var i = 0; i < inputs.length; i++) {
                console.log(inputs[i].name);
                if (!namen.includes(inputs[i].name)) {
                    names.push(inputs[i].name);
                }

            }

            return names.length;
        },

        checkProgress: function (x) {

        }
    });
});


/*
var step=0;
step++;

var elEm = document.getElement('li.step-' + step);

var button = document.getElement('.next-button');

button.addEvent('click', function(e) {
    check(e);
});


function check(event) {

    console.log('--------> ' + event);

    var radioB = elEm.getElements('input[type="radio"]:checked');
    console.warn(radioB.length);
    if (radioB.length == 0) {
        event.preventDefault();
        console.warn(radioB);
        console.info('nichts ausgewählt');

        return;
    }

}

    */
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
            'countInputs',
            'checkProgress',
            'changeProgressBar',
            'checkPassword'
        ],

        initialize: function () {
            this.NextStep = null;
            this.BackStep = null;
            this.StepsList = null;
            this.ListElement = null;
            this.listElementWidth = null;

            this.NavList = null;
            this.liElm = null;
            this.fa = null;

            this.headerList = null;
            this.headerLogoContainer = null;

            this.inputs = null;
            this.selects = null;
            this.allInputs = null;
            this.progressBarDone = null;
            this.progressbarText = null;
            this.textColorGrey = null;

            this.userInputs = null;
            this.passwordAgain = null;
            this.passwordConfirmed = null;

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

            this.NextStep = document.getElement('.next-button');
            this.BackStep = document.getElement('.back-button');
            this.StepsList = document.getElement('.steps-list');
            this.ListElement = document.getElements('.step');
            this.listElementWidth = document.getElement('.step').getSize().x;

            this.headerList = document.getElement('.header-list');
            this.headerLogoContainer = document.getElement('.header-logo-container');

            this.NavList = document.getElement('.nav-list');
            this.liElm = this.NavList.getElements('li');
            this.fa = this.NavList.getElements('.fa');

            this.inputs = document.getElements('input');
            this.textInputs = document.getElements('input[type="text"], input[type="password"]');
            this.selects = document.getElements('select');
            this.allInputs = document.getElements('input, select');
            this.progressBarDone = document.getElement('.progress-bar-done');
            this.progressbarText = document.getElement('.progress-bar-text');
            this.textColorGrey = true;

            this.userInputs = document.getElements('.input-text-user');
            this.passwordAgain = document.getElement('.input-text-user[name="user.userpassword.again"]');
            this.passwordConfirmed = true;

            this.activeHeader = document.getElements('.header-list li');

            this.step = 0;

            this.NextStep.addEvent('click', this.next);
            this.BackStep.addEvent('click', this.back);

            /*window.addEvents({
                resize: function() {
                    QUIFunctionUtils.debounce(this.recalc, 20);
                }
            });*/

            this.setHeaderHeight(this.getHeaderHeight());
            this.checkProgress();


            this.$hideOnResize = false;

            window.addEvent('resize', function() {
                if (this.$hideOnResize) {
                    return;
                }

                this.$hideOnResize = true;
                this.StepsList.setStyle('opacity', 0);

            }.bind(this));

            this.passwordAgain.addEvent('change', this.checkPassword);
            this.inputs.addEvent('change', this.checkProgress);
            this.selects.addEvent('change', this.checkProgress);
            this.userInputs.addEvent('change', this.changeUserIcon);


            QUI.addEvent('onResize', function() {
                this.recalc();
                this.testF();
            }.bind(this));


            moofx(this.activeHeader[this.step]).animate({
                opacity: 1
            }, {
                duration: 500
            });

            moofx(this.StepsList).animate({
                opacity: 1
            }, {
                duration: 500
            });
        },

        /**
         * next step
         */
        next: function () {
            var currentPos = this.StepsList.getStyle('left').toInt();

            if (currentPos == ((this.ListElement.length-1) * -this.listElementWidth )) {
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
                this.BackStep.disabled = false;
            }

            var pos = currentPos - this.listElementWidth;

            moofx(this.StepsList).animate({
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
            var currentPos = this.StepsList.getStyle('left').toInt();

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

            moofx(this.StepsList).animate({
                left: pos
            },{
                duration: 300,
                equation: 'ease-in-out'
            });
        },

        /**
         * recalc the ListElement width
         */
        recalc: function() {
            this.listElementWidth = document.getElement('.step').getSize().x;
            this.$hideOnResize = false;

            this.StepsList.setStyle('opacity', 0);
            var newPos = this.step * -this.listElementWidth;

            moofx(this.StepsList).animate({
                left: newPos,
                opacity: 1
            }, {
                duration: 500
            });
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

        /**
         *
         * @param headerHeight
         */
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
         * @param step {Number}
         */
        showCurrentHeader: function (step) {
            moofx(this.activeHeader[step]).animate({
                opacity: 1
            }, {
                duration: 250
            });
        },

        testF: function () {
            this.setHeaderHeight(this.getHeaderHeight());
        },

        /**
         * Count all inputs fields
         * Each group of fields (i.e. radio buttons) will be count as 1
         *
         * @returns {Number}
         */
        countInputs: function () {
            for (var i = 0; i < this.selects.length; i++) {
                this.allInputs.push(+ this.selects[i]);
            }
            var names = [];

            for (var i = 0; i < this.allInputs.length; i++) {
                if (!names.include(this.allInputs[i].name)) {
                    names.push(this.allInputs[i].name);
                }
            }
            return names.length;
        },

        checkProgress: function () {

            if (!this.passwordConfirmed) {
                return;
            }

            var progress;
            var inputsDone = 0;

            // check radio and checkbox
            for (var i = 0; i < this.inputs.length; i++) {
                if (this.inputs[i].checked) {
                    inputsDone++
                }
            }

            // check select
            for (var i = 0; i < this.selects.length; i++) {
                if (this.selects[i].value) {
                    inputsDone++;
                }
            }

            // check text and password input
            for (var i = 0; i < this.textInputs.length; i++) {
                if (this.textInputs[i].value) {
                    inputsDone++
                }
            }

            progress = (inputsDone / this.countInputs() * 100).toString();
            var arr = progress.split('.');
            progress = arr[0];

            this.progressbarText.innerHTML = progress + "%";

            if (progress == 0) {
                progress = 1;
            }

            this.changeProgressBar(progress);
        },

        /**
         * Set the width and color of the progress bar
         *
         * @param x {Number}
         */
        changeProgressBar: function (x) {
            moofx(this.progressBarDone).animate ({
                width: x + "%"
            }, {
                duration: 500,
                equation: 'ease-in-out'
            });

            if (x > 50 && this.textColorGrey) {
                moofx(this.progressbarText).animate({
                    color: '#ffffff'
                }, {
                    duration: 500,
                    equation: 'ease-in-out'
                });
                this.textColorGrey = false;
                return;
            }

            if (x <= 50 && this.textColorGrey == false) {
                moofx(this.progressbarText).animate({
                    color: '#999999'
                }, {
                    duration: 500,
                    equation: 'ease-in-out'
                });
                this.textColorGrey = true;
            }
        },

        changeUserIcon: function () {
            var Elm = this;
            var Icon = Elm.getParent().getElement('.fa');

            if (Elm.value) {
                moofx(Icon).animate({
                    color: '#555555'
                }, {
                    duration: 500
                });
                return;
            }

            moofx(Icon).animate({
                color: '#dddddd'
            }, {
                duration: 500
            });
        },

        /**
         *
         * @returns {boolean}
         */
        checkPassword: function() {
            var Pw1 = document.getElement('.input-text-user[name="user.userpassword"]');
            var Pw2 = document.getElement('.input-text-user[name="user.userpassword.again"]');
            var MooFx= moofx(document.getElement('.user-info'));

            if (Pw1.value != Pw2.value) {
                Pw1.style.outline = "1px solid #ff0000";
                Pw2.style.outline = "1px solid #ff0000";
                MooFx.animate({
                    top: 0,
                    opacity: 1
                }, {
                    duration: 500,
                    equation: 'ease-in-out'
                });
                return this.passwordConfirmed = false;
            }

            Pw1.style.outline = "none";
            Pw2.style.outline = "none";
            MooFx.animate({
                  top: -60,
                  opacity: 0
              }, {
                  duration: 500,
                  equation: 'ease-in-out'
              })
            return this.passwordConfirmed = true;
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
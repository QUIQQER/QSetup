/**
 * Setup
 *
 * @module bin/js/Setup
 * @author www.pcsg.de (Michael Danielczok)
 *
 */

define('bin/js/Setup', [
    'qui/QUI',
    'qui/utils/Functions',
    'qui/utils/Form',
    'qui/Locale'
    // 'qui/controls/Control'
], function (QUI, QUIFunctionUtils, QUIFormUtils, QUILocale) {
    "use strict";

    QUILocale.setCurrent(CURRENT_LOCALE);

console.log(CURRENT_LOCALE);
console.log(LOCALE_TRANSLATIONS);

    return new Class({

        // Extends: QUIControl,

        Type : 'bin/js/Setup',
        Binds: [
            'load',
            'next',
            'back',
            'recalc',
            'showCurrentHeader',
            'getHeaderHeight',
            'setHeaderHeight',
            /*'testF',*/
            'countInputs',
            'checkProgress',
            'changeProgressBar',
            'checkPassword',
            'checkPassword2',
            '$exeInstall'
        ],

        initialize: function () {
            this.NextStep         = null;
            this.BackStep         = null;
            this.StepsList        = null;
            this.ListElement      = null;
            this.listElementWidth = null;

            this.NavList = null;
            this.liElm   = null;
            this.fa      = null;

            this.headerList          = null;
            this.headerLogoContainer = null;

            this.inputs          = null;
            this.selects         = null;
            this.allInputs       = null;
            this.progressBarDone = null;
            this.progressbarText = null;
            this.textColorGrey   = null;

            this.userInputs        = null;
            this.passwordAgain     = null;
            this.passwordConfirmed = null;
            this.buttonInstall     = false;


            this.activeHeader = null;

            this.step = 1;

            this.goNext = true;
        },

        /**
         * event : on load
         */
        load: function () {

            this.FormSetup = document.getElement('#form-setup');

            this.NextStep         = document.getElement('#next-button');
            this.BackStep         = document.getElement('#back-button');
            this.StepsList        = document.getElement('.steps-list');
            this.ListElement      = document.getElements('.step');
            this.listElementWidth = document.getElement('.step').getSize().x;

            this.headerList          = document.getElement('.header-list');
            this.headerLogoContainer = document.getElement('.header-logo-container');

            this.NavList = document.getElement('.nav-list');
            this.liElm   = this.NavList.getElements('li');
            this.fa      = this.NavList.getElements('.fa');

            this.inputs          = document.getElements('input');
            this.textInputs      = document.getElements('input[type="text"], input[type="password"]');
            this.selects         = document.getElements('select');
            this.allInputs       = document.getElements('input, select');
            this.progressBarDone = document.getElement('.progress-bar-done');
            this.progressbarText = document.getElement('.progress-bar-text');
            this.textColorGrey   = true;

            this.userInputs        = document.getElements('.input-text-user');
            this.passwordAgain     = document.getElement('.input-text-user[name="userPasswordAgain"]');
            this.passwordConfirmed = true;

            this.activeHeader = document.getElements('.header-list li');


            this.NextStep.addEvent('click', this.next);
            this.BackStep.addEvent('click', this.back);

            window.addEvents({
                resize: function () {
                    QUIFunctionUtils.debounce(this.recalc, 2000);
                }
            });

            this.setHeaderHeight(this.getHeaderHeight());

            // check progress erst nach NEXT click
            // this.checkProgress();
            this.changeProgressBar(1);


            this.$hideOnResize = false;

            window.addEvent('resize', function () {
                if (this.$hideOnResize) {
                    return;
                }

                this.$hideOnResize = true;
                this.StepsList.setStyle('opacity', 0);

            }.bind(this));

            this.passwordAgain.addEvent('change', this.checkPassword2);
            // check progress erst nach NEXT click
            /*this.inputs.addEvent('change', this.checkProgress);
             this.selects.addEvent('change', this.checkProgress);*/
            this.userInputs.addEvent('change', this.changeUserIcon);


            QUI.addEvent('onResize', function () {
                this.recalc();
                this.setHeaderHeight(this.getHeaderHeight());
            }.bind(this));


            moofx(this.activeHeader[this.step - 1]).animate({
                opacity: 1
            }, {
                duration: 500
            });

            moofx(this.StepsList).animate({
                opacity: 1
            }, {
                duration: 500
            });

            console.log(QUIFormUtils.getFormData(this.FormSetup));
        },

        /**
         * next step
         */
        next: function () {

            var FuncExecuteNext = function () {
                console.info("next function");

                if (this.step == this.ListElement.length) {
                    this.$exeInstall();
                    return;
                }
                // nav icons
                /*this.fa[this.step].removeClass('fa-circle-o');
                 this.fa[this.step].addClass('fa-check-circle-o');*/

                // nav color
                this.liElm[this.step - 1].removeClass('step-active');
                this.liElm[this.step - 1].addClass('step-done');
                this.liElm[this.step].addClass('step-active');

                this.step++;

                // header
                moofx(this.activeHeader).animate({
                    opacity: 0
                }, {
                    duration: 250,
                    equation: 'ease-in-out',
                    callback: function () {
                        this.showCurrentHeader(this.step)
                    }.bind(this)
                });

                // enable back button
                if (this.step > 0) {
                    this.BackStep.disabled = false;
                }
                var currentPos = this.StepsList.getStyle('left').toInt();
                var pos        = currentPos - this.listElementWidth;

                moofx(this.StepsList).animate({
                    left: pos
                }, {
                    duration: 500,
                    equation: 'ease-in-out'
                });

                // change button text from "next" to "install"
                if (this.step == 7) {
                    this.buttonInstall = true;
                    this.NextStep.set('html', 'Installieren');
                }

                this.checkProgress();
            }.bind(this);

            // data base check
            if (this.step + 1 == 5) {
                this.checkDataBase().then(function () {
                    console.log("data base Prüfung war ok");
                    FuncExecuteNext();
                }, function (error) {

                    QUI.getMessageHandler().then(function (MH) {
                        var errorDecoded = JSON.decode(error),
                            message      = 'Fehler bei der Verbindung zu Datenbank. <br /><br />';


                        message += errorDecoded.code + '<br />';
                        message += errorDecoded.message + '<br />';

                        MH.setAttribute('displayTimeMessages', 8000);
                        MH.addError(message);
                    })
                });

                return;
            }

            FuncExecuteNext();
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
            this.liElm[this.step - 1].removeClass('step-active');
            this.liElm[this.step - 1].removeClass('step-done');
            this.liElm[this.step - 2].addClass('step-active');

            this.step--;

            // disable back button
            if (this.step == 1) {
                this.BackStep.disabled = true;
            }

            moofx(this.activeHeader).animate({
                opacity: 0
            }, {
                duration: 250,
                equation: 'ease-in-out',
                callback: function () {
                    this.showCurrentHeader(this.step)
                }.bind(this)
            });

            var pos = currentPos + this.listElementWidth;

            moofx(this.StepsList).animate({
                left: pos
            }, {
                duration: 300,
                equation: 'ease-in-out'
            });

            // change button text from "install" to "next"
            if (this.buttonInstall) {
                this.NextStep.set('html', 'Fortfahren');
            }

            this.checkProgress();
        },

        /**
         * recalc the ListElement width
         */
        recalc: function () {
            this.listElementWidth = document.getElement('.step').getSize().x;
            this.$hideOnResize    = false;

            this.StepsList.setStyle('opacity', 0);
            var newPos = (this.step - 1) * -this.listElementWidth;

            moofx(this.StepsList).animate({
                left   : newPos,
                opacity: 1
            }, {
                duration: 500
            });
        },

        /**
         * get header height
         *
         * @returns {*}
         */
        getHeaderHeight: function () {
            var arr   = [];
            var liElm = this.headerList.getElements('li');
            for (var i = 0; i < this.liElm.length; i++) {
                arr[i] = liElm[i].getSize().y;
            }

            // Was wird hier zurückgegeben? was bedeutet {*}?
            return Math.max.apply(false, arr).toInt();
        },

        /**
         * set header height
         *
         * @param headerHeight
         */
        setHeaderHeight: function (headerHeight) {
            moofx(this.headerList).animate({
                minHeight: headerHeight,
                opacity  : 1
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
            moofx(this.activeHeader[step - 1]).animate({
                opacity: 1
            }, {
                duration: 250
            });
        },

        /*testF: function () {
         this.setHeaderHeight(this.getHeaderHeight());
         },*/

        /**
         * Count all inputs fields -> for progress bar
         * Each group of fields (i.e. radio buttons) will be count as 1
         *
         * @returns {Number}
         */
        /*countInputs: function () {
         for (var i = 0; i < this.selects.length; i++) {
         this.allInputs.push(+this.selects[i]);
         }
         var names = [];

         for (var i = 0; i < this.allInputs.length; i++) {
         if (!names.include(this.allInputs[i].name)) {
         names.push(this.allInputs[i].name);
         }
         }

         console.log(names.length);
         console.log(Object.getLength(QUIFormUtils.getFormData(this.FormSetup)));

         return names.length;
         },*/

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

            var inputsNumber = Object.getLength(QUIFormUtils.getFormData(this.FormSetup));

            progress = (inputsDone / inputsNumber * 100).toString();
            var arr  = progress.split('.');
            progress = arr[0];

            this.progressbarText.innerHTML = progress + "%";

            if (progress == 0) {
                progress = 1;
            }

            this.changeProgressBar(progress);

            // default values
            switch (this.step) {
                case 2:
                    if (!document.getElements('[name="version"]:checked').length) {
                        document.getElements('[name="version"]')[0].checked = true;
                    }
                    break;
                case 3:
                    if (!document.getElements('[name="vorlage"]:checked').length) {
                        document.getElements('[name="vorlage"]')[0].checked = true;
                    }
                    break;
                case 4:
                    this.fillTestData(4);
                    break;
                case 5:
                    this.fillTestData(5);
                    break;
            }
        },

        /**
         * Set the width and color of the progress bar
         *
         * @param x {Number} - % change
         */
        changeProgressBar: function (x) {
            moofx(this.progressBarDone).animate({
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
            var Elm  = this;
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
        checkPassword: function () {
            var Pw1   = document.getElement('.input-text-user[name="userPassword"]');
            var Pw2   = document.getElement('.input-text-user[name="userPasswordAgain"]');
            var MooFx = moofx(document.getElement('.user-info'));

            if (Pw1.value != Pw2.value) {
                Pw1.style.outline = "1px solid #ff0000";
                Pw2.style.outline = "1px solid #ff0000";
                MooFx.animate({
                    top    : 0,
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
                top    : -60,
                opacity: 0
            }, {
                duration: 500,
                equation: 'ease-in-out'
            });
            return this.passwordConfirmed = true;
        },

        checkPassword2: function () {
            var pass1 = document.getElement('.input-text-user[name="userPassword"]');
            var pass2 = document.getElement('.input-text-user[name="userPasswordAgain"]');

            console.log(pass1);
            console.log(pass2);
            if (pass1.value != pass2.value) {
                pass2.setCustomValidity("Passwords Don't Match");
            } else {
                //empty string means no validation error
                pass2.setCustomValidity('');
            }

        },

        /**
         *
         * @returns {Promise}
         */
        checkDataBase: function () {
            var Form = QUIFormUtils.getFormData(this.FormSetup);

            return new Promise(function (resolve, reject) {
                // database request
                new Request({
                    url      : '/ajax/checkDatabase.php',
                    noCache  : true,
                    data     : {
                        driver  : Form.databaseDriver,
                        host    : Form.databaseHost,
                        port    : Form.databasePort,
                        user    : Form.databaseUser,
                        password: Form.databasePassword,
                        name    : Form.databaseName
                    },
                    onSuccess: function (responseText) {
                        if (responseText == 'true') {
                            console.log("bin in true");
                            resolve();
                            return;
                        }

                        reject(responseText);
                    },
                    onFailure: function () {
                        // console.log("bin in false");
                        reject();
                    }
                }).send();
            });
        },

        /**
         * install -> send data
         * (test)
         */
        $exeInstall: function () {
            console.info(QUIFormUtils.getFormData(this.FormSetup))
        },

        /**
         * inputs ausfüllen mit beispiel Daten
         * (test)
         */
        fillTestData: function (step) {

            switch (step) {
                case 4:
                    document.getElement('select[name="databaseDriver"]').options[1].selected = true;
                    document.getElement('input[name="databaseHost"]').value                  = 'localhost';
                    document.getElement('input[name="databaseName"]').value                  = 'QUIQQERTest';
                    document.getElement('input[name="databaseUser"]').value                  = 'root';
                    document.getElement('input[name="databasePassword"]').value              = 'root';
                    document.getElement('input[name="databasePort"]').value                  = '3306';
                    break;
                case 5:
                    document.getElement('input[name="userName"]').value          = 'admin';
                    document.getElement('input[name="userPassword"]').value      = 'admin';
                    document.getElement('input[name="userPasswordAgain"]').value = 'admin';

            }
        }.bind(this)
    });
});
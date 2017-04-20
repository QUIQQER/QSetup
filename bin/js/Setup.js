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
    'qui/Locale',
    'qui/controls/windows/Popup',
    'qui/controls/windows/Confirm',

    'text!bin/js/Setup.TemplateForm.html'
    // 'qui/controls/Control'
], function (QUI, QUIFunctionUtils, QUIFormUtils, QUILocale, QUIPopup, QUIConfirm, templateForm) {
    "use strict";

    QUILocale.setCurrent(CURRENT_LOCALE);

// console.log(CURRENT_LOCALE);
//     console.log(LOCALE_TRANSLATIONS);

    return new Class({

        // Extends: QUIControl,

        Type : 'bin/js/Setup',
        Binds: [
            'load',
            'next',
            'nextExecute',
            'back',
            'recalc',
            'showCurrentHeader',
            'getHeaderHeight',
            'setHeaderHeight',
            'countInputs',
            'checkProgress',
            'changeProgressBar',
            'checkDatabase',
            'checkUserAndPassword',
            'disableAllTab',
            'activeTabForThisStep',
            'getPreset',
            'openPopup',
            '$getAvailableTemplates',
            '$exeInstall',
            'showPassword'
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

            this.inputRadioCheckbox = null;
            this.inputText          = null;
            this.inputSelect        = null;
            this.allInputs          = null;
            this.progressBarDone    = null;
            this.progressbarText    = null;
            this.textColorGrey      = null;
            this.licenseCheckbox    = null;
            this.licenseLabel       = null;

            this.userInputs    = null;
            this.buttonInstall = false;

            this.activeHeader = null;

            this.step = 1;
        },

        /**
         * event : on load
         */
        load: function () {

            // javascript is active
            document.getElement('.script-is-on').setStyle('display', 'block');

            this.FormSetup = document.getElement('#form-setup');

            // console.log(this.FormSetup);
            // console.log(QUIFormUtils.getFormData(this.FormSetup));

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

            this.inputRadioCheckbox = document.getElements('input[type="radio"], input[type="checkbox"]');
            this.inputText          = document.getElements('input[type="text"], input[type="password"], input[type="number"]');
            this.inputSelect        = document.getElements('select');
            this.allInputs          = document.getElements('input, select');
            this.progressBarDone    = document.getElement('.progress-bar-done');
            this.progressbarText    = document.getElement('.progress-bar-text');
            this.textColorGrey      = true;
            this.licenseCheckbox    = document.getElement('.license-checkbox');
            this.licenseLabel       = document.getElement('.license-label');

            this.userInputs = document.getElements('.input-text-user, .input-text-password');

            this.activeHeader = document.getElements('.header-list li');

            this.NextStep.addEvent('click', this.next);
            this.BackStep.addEvent('click', this.back);

            // diasble all TAB key
            this.disableAllTab();

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

            var self = this;
            // step 3 template settings button
            document.getElements('.step-3-settings-button').addEvent('click', function (event) {
                var Target = event.target;

                new QUIConfirm({
                    maxWidth : 440,
                    maxHeight: 520,
                    titleicon: false,
                    icon     : false,
                    title    : Target.getAttribute('data-attr'),
                    events   : {
                        onOpen: self.openPopup,

                        onClose: function () {
                            // chrome render bug
                            document.body.setStyle('transform', 'translateZ(0)');
                            (function () {
                                document.body.setStyle('transform', null);
                            }).delay(100);
                        },

                        onSubmit: function (Win) {
                            var Content = Win.getContent(),
                                Form    = Content.getElement('form');

                            console.log(QUIFormUtils.getFormData(Form));
                        }
                    }
                }).open();
            });

            // change icon in input field (user step)
            this.userInputs.addEvent('change', function () {
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
            });

            // show password (user step)
            this.showPasswordIcon = document.getElement('.show-password');
            this.showPasswordIcon.addEvent('click', function (event) {
                event.stop();
                var passwords = document.getElements('.input-text-password'),
                    input     = this.showPasswordIcon.getParent().getElement('input');


                if (input.getAttribute('type') == 'password') {
                    this.showPasswordIcon.removeClass('fa-eye-slash');
                    this.showPasswordIcon.addClass('fa-eye');
                    passwords.forEach(function (Elm) {
                        Elm.setAttribute('type', 'text');
                    });
                    return;
                }

                this.showPasswordIcon.removeClass('fa-eye');
                this.showPasswordIcon.addClass('fa-eye-slash');
                passwords.forEach(function (Elm) {
                    Elm.setAttribute('type', 'password');
                });
            }.bind(this));

            document.getElements('.host-and-url-info').addEvent('click', function (event) {
                event.stop();
                var Target = event.target;

                var Popup = new QUIPopup({
                    maxWidth       : 420,
                    maxHeight      : 340,
                    title          : Target.getAttribute('title'),
                    closeButtonText: 'schließen',
                    content        : Target.getAttribute('data-attr')
                });
                Popup.open();
            });

            // licence checkbox
            this.licenseCheckbox.addEvent('change', function () {
                if (this.licenseCheckbox.checked) {
                    this.licenseLabel.setStyle('color', 'inherit');
                    this.licenseCheckbox.getParent().removeClass('error');
                    this.checkProgress();
                    return;
                }

                this.checkProgress();
            }.bind(this));


            // show label by inputs
            var inputChange = function (event) {
                var Target = event.target;
                if (Target) {
                    if (Target.value === '') {
                        Target.getParent().removeClass('show-label');
                    } else {
                        Target.getParent().addClass('show-label');
                    }
                    return;
                }

                if (event.value === '') {
                    event.getParent().removeClass('show-label');
                } else {
                    event.getParent().addClass('show-label');
                }
            };

            this.inputText.addEvents({
                change: inputChange,
                keyup : inputChange
            });

            this.inputSelect.addEvents({
                change: inputChange,
                keyup : inputChange
            });

            // host - fill placeholder
            var domain     = document.getElement('input[name="domain"]'),
                rootPath   = document.getElement('input[name="rootPath"]'),
                urlSubPath = document.getElement('input[name="URLsubPath"]'),
                subPath    = '/';

            if (window.location.pathname != '/') {
                subPath = window.location.pathname + '/';
            }
            domain.placeholder     = window.location.origin;
            rootPath.placeholder   = ROOT_PATH + '/';
            urlSubPath.placeholder = subPath;

            console.log(QUIFormUtils.getFormData(this.FormSetup));
        },

        /**
         * next step
         * check, if the next step can be executed
         */
        next: function () {

            this.NextStep.blur();
            var self = this;

            // data base check
            if (this.step == 4) {
                this.checkDataBase().then(function () {
                    self.nextExecute();
                }, function (error) {
                    QUI.getMessageHandler().then(function (MH) {
                        var errorDecoded = JSON.decode(error.response),
                            message      = LOCALE_TRANSLATIONS['exception.validation.database.connection'] +
                                '.<br /><br />';

                        message += errorDecoded.code + '<br />';
                        message += errorDecoded.message + '<br />';

                        MH.setAttribute('displayTimeMessages', 8000);
                        MH.addError(message);
                    })
                });

                return;
            }

            // user step
            if (this.step == 5) {
                this.checkUserAndPassword().then(function () {
                    self.nextExecute();
                }, function (error) {
                    QUI.getMessageHandler().then(function (MH) {

                        MH.setAttribute('displayTimeMessages', 5000);
                        MH.addError(error.response);
                    })
                });
                return;
            }

            // host step
            if (this.step == 6) {
                var stepHost = document.getElement('.step-6'),
                    inputs   = stepHost.getElements('input');

                inputs.forEach(function (Elm) {
                    if (Elm.value == '') {
                        Elm.value = Elm.placeholder;
                    }
                });

            }

            this.nextExecute();
        },

        /**
         * go to the next step if anything ok
         *
         */
        nextExecute: function () {

            // last step --> install QUIQQER
            if (this.step == this.ListElement.length) {
                if (!this.licenseCheckbox.checked) {
                    this.licenseLabel.setStyle('color', '#cc0000');
                    this.licenseCheckbox.getParent().addClass('error');
                    return;
                }

                this.$exeInstall();
                return;
            }

            // nav color
            this.liElm[this.step - 1].removeClass('step-active');
            this.liElm[this.step - 1].addClass('step-done');
            this.liElm[this.step].addClass('step-active');

            this.step++;

            // active TAB key in next step
            this.activeTabForThisStep(this.step);

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
                this.NextStep.set('html', LOCALE_TRANSLATIONS['setup.web.content.button.install']);
            }

            this.checkProgress();
        }
        ,

        /**
         * back step
         */
        back: function () {
            this.BackStep.blur();
            var currentPos = this.StepsList.getStyle('left').toInt();

            if (currentPos == 0) {
                return;
            }

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
                this.NextStep.set('html', LOCALE_TRANSLATIONS['setup.web.content.button.next']);
            }

            this.checkProgress();
        }
        ,

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
        }
        ,

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

            return Math.max.apply(false, arr).toInt();
        }
        ,

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
        }
        ,

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
        }
        ,

        /**
         * Count all inputs fields -> for progress bar
         * Each group of fields (i.e. radio buttons) will be count as 1
         *
         * @returns {Number}
         */
        countInputs: function () {
            for (var i = 0; i < this.inputSelect.length; i++) {
                this.allInputs.push(+this.inputSelect[i]);
            }
            var names = [];

            for (var j = 0; j < this.allInputs.length; j++) {
                if (!names.include(this.allInputs[j].name)) {
                    names.push(this.allInputs[j].name);
                }
            }

            // console.log(names.length);
            // console.log(Object.getLength(QUIFormUtils.getFormData(this.FormSetup)));

            return names.length;
        }
        ,

        /**
         * check the progress
         * take all input fields and show
         * how much of them are filled
         */
        checkProgress: function () {

            var progress;
            var inputsDone = 0;

            // check radio and checkbox
            for (var i = 0; i < this.inputRadioCheckbox.length; i++) {
                if (this.inputRadioCheckbox[i].checked) {
                    inputsDone++;
                }
            }

            // check select
            for (var i = 0; i < this.inputSelect.length; i++) {
                if (this.inputSelect[i].value) {
                    inputsDone++;
                }
            }

            // check text, password and number input
            for (var i = 0; i < this.inputText.length; i++) {
                if (this.inputText[i].value) {
                    inputsDone++;
                }
            }

            // "-1" because one input field is not required (db_prefix)
            var inputsNumber = this.countInputs() - 1;

            progress = (inputsDone / inputsNumber * 100).toString();
            var arr  = progress.split('.');
            progress = arr[0];

            this.progressbarText.innerHTML = progress + "%";

            if (progress == 0) {
                progress = 1;
            }

            // animate the progress bar
            this.changeProgressBar(progress);

            // default values for some fields
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
                case 4: // only test
                    this.fillTestData(4);
                    break;
                case 5: //only test
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
        }
        ,

        /**
         * Check the data base credentials
         *
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
                        port    : 3306,
                        user    : Form.databaseUser,
                        password: Form.databasePassword,
                        name    : Form.databaseName,
                        lang    : CURRENT_LOCALE
                    },
                    onSuccess: function () {
                        resolve();
                    },
                    onFailure: function (response) {
                        reject(response);
                    }
                }).send();
            });
        }
        ,

        /**
         * Check, if user is not empty,
         * password is strong enough,
         * the second password matches
         *
         * @returns {Promise}
         */
        checkUserAndPassword: function () {
            var Form = QUIFormUtils.getFormData(this.FormSetup);

            return new Promise(function (resolve, reject) {
                // check user and password
                new Request({
                    url    : '/ajax/checkUserAndPassword.php',
                    noCache: true,
                    data   : {
                        userName          : Form.userName,
                        userPassword      : Form.userPassword,
                        userPasswordRepeat: Form.userPasswordRepeat,
                        lang              : CURRENT_LOCALE
                    },

                    onSuccess: function (response) {
                        if (response == 'true') {
                            resolve();
                            return;
                        }

                        reject(response);
                    },
                    onFailure: function (response) {
                        reject(response);
                    }
                }).send();
            });
        },

        /**
         * disable TAB key on all inputs
         */
        disableAllTab: function () {
            this.allInputs.forEach(function (Elm) {
                Elm.setAttribute('tabindex', '-1')
            });
        },

        /**
         * active TAB key only in actual step
         *
         * @param step
         */
        activeTabForThisStep: function (step) {
            var cssClass = '.step-' + step;
            document.getElement(cssClass).getElements('input, select').forEach(function (Elm) {
                Elm.setAttribute('tabindex', '2');
            });
        },

        /**
         * opens the popup in step template
         *
         * @param Win
         */
        openPopup: function(Win) {

            var Content = Win.getContent();

            Content.set('html', templateForm);

            Content.getElement('.project-title').set(
                'html',
                LOCALE_TRANSLATIONS['setup.web.popup.project-title']
            );
            Content.getElement('.project-language').set(
                'html',
                LOCALE_TRANSLATIONS['setup.web.popup.project-language']
            );
            Content.getElement('.project-template').set(
                'html',
                LOCALE_TRANSLATIONS['setup.web.popup.project-template']
            );

            Win.Loader.show();

            this.getPreset().then(function(response){
               console.log(response);
            });

            this.getAvailableTemplates(Content).then(function() {
                Win.Loader.hide();
            })


        },

        /**
         * get preset
         *
         * @returns {Promise}
         */
        getPreset: function() {
            return new Promise(function(resolve, reject) {
                new Request({
                    url      : '/ajax/getPreset.php',
                    noCache  : true,
                    data : {
                        presetName : 'blog'
                    },

                    onSuccess: function (response) {
                        resolve(JSON.decode(response));
                    },
                    onFailure: function (response) {
                        reject(response);
                    }
                }).send();
            })
        },

        /**
         * get all available templates
         *
         * @returns {Promise}
         */
        getAvailableTemplates: function(Content) {
            return new Promise(function (resolve, reject) {
                // check user and password
                new Request({
                    url      : '/ajax/getAvailableTemplates.php',
                    noCache  : true,

                    onSuccess: function (response) {
                        var output;
                        JSON.decode(response).forEach(function(Elm) {
                            output += '<option value=' + Elm + '>' + Elm + '</option>';
                        });
                        Content.getElement('#project-template').set(
                            'html',
                            output
                        );
                        resolve();
                    },
                    onFailure: function (response) {
                        reject(response);
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
                    document.getElement('input[name="databasePassword"]').value              = '548?Q_Xggg-v$';
                    break;
                case 5:
                    document.getElement('input[name="userName"]').value           = 'admin';
                    document.getElement('input[name="userPassword"]').value       = 'admin';
                    document.getElement('input[name="userPasswordRepeat"]').value = 'admin';

            }
        }.bind(this)
    });
});
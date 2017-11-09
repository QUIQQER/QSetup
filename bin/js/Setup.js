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
    'qui/controls/loader/Loader',

    'text!bin/js/Setup.TemplateForm.html',
    // 'qui/controls/Control'
], function (
    QUI,
    QUIFunctionUtils,
    QUIFormUtils,
    QUILocale,
    QUIPopup,
    QUIConfirm,
    QUILoader,
    templateForm
) {
    "use strict";

    QUILocale.setCurrent(CURRENT_LOCALE);

    return new Class({

        // Extends: QUIControl,

        Type : 'bin/js/Setup',
        Binds: [
            'systemCheck',
            'load',
            'checkRequirements',
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
            'setDefaultValues',
            'checkDatabase',
            'checkUserAndPassword',
            'disableAllTab',
            'activeTabForThisStep',
            'openSystemCheckPopup',
            'createTemplatePopup',
            'openTemplatePopup',
            'checkProjectName',
            'getPreset',
            'setPresetDataLang',
            'getAvailableTemplates',
            'setPresetDataTemplate',
            'checkPopupForm',
            'validatePresetData',
            'updatePreset',
            'checkPassword',
            'languageButtons',
            '$exeInstall',
            'parseFormData',
            'showPassword'
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

            this.inputRadioCheckbox = null;
            this.inputText = null;
            this.inputSelect = null;
            this.allInputs = null;
            this.progressBarDone = null;
            this.progressbarText = null;
            this.textColorGrey = null;
            this.licenseCheckbox = null;
            this.licenseLabel = null;

            this.userInputs = null;
            this.pass1 = null;
            this.pass2 = null;
            this.buttonInstall = false;

            this.activeHeader = null;

            this.step = 1;

            // javascript is active
            document.getElement('.script-is-on').setStyle('display', 'block');


        },

        /**
         * switch between install and error div,
         * if the checkRequirements returns an array with failed test names
         */
        systemCheck: function () {
            var self            = this,
                ButtonContainer = document.getElement('.system-check-button-container');
            this.languageButtons();

            this.checkRequirements().then(function (response) {

                var systemCheck       = document.getElement('.system-check'),
                    stepsContainer    = document.getElement('.steps-container'),
                    Response          = JSON.decode(response),
                    icon              = ButtonContainer.getElement('.icon-placeholder'),
                    systemCheckButton = document.getElement('#system-check');

                ButtonContainer.addClass(Response.status);
                icon.addClass(Response.icon);

                if (Response.testsFailed) {

                    var htmlContent = '',
                        htmlHeader  = '',
                        nextButton  = document.getElement('#next-button');


                    htmlHeader += '<h1>' + LOCALE_TRANSLATIONS['setup.web.system.check.header'] + '</h1>';
                    htmlHeader += '<p>' + LOCALE_TRANSLATIONS['setup.web.system.check.header.desc'] + '</p>';

                    htmlContent += '<div class="system-check-error-container">';
                    htmlContent += '<p>' + LOCALE_TRANSLATIONS['setup.web.system.check.desc'] + '</p>';

                    htmlContent += Response.htmlResult;
                    htmlContent += '</div>';

                    document.getElement('.system-check-error-wrapper').set(
                        'html', htmlContent
                    );

                    document.getElement('.header-right').set(
                        'html', htmlHeader
                    );

                    nextButton.set(
                        'html', LOCALE_TRANSLATIONS['setup.web.system.check.button']
                    );

                    systemCheckButton.setAttribute(
                        'title', LOCALE_TRANSLATIONS['setup.web.system.check.button']
                    );

                    var reload = function () {
                        window.location.reload();
                    };

                    nextButton.addEvent('click', reload);
                    systemCheckButton.addEvent('click', reload);
                    return;
                }

                // everything is ok, setup goes on

                document.getElement('#back-button').setStyle('display', 'inline-block');
                systemCheck.setStyle('display', 'none');
                stepsContainer.setStyles({
                    display   : 'block',
                    visibility: 'visible',
                    opacity   : 1
                });

                systemCheck.destroy();

                document.getElement('.first-step-menu').addClass('step-active');
                self.load();


            });
        },

        /**
         * Check the system --> ajax
         *
         * @returns {Promise}
         */
        checkRequirements: function () {
            return new Promise(function (resolve, reject) {
                new Request({
                    url      : '/ajax/checkRequirements.php',
                    noCache  : true,
                    data     : {
                        lang: CURRENT_LOCALE
                    },
                    onSuccess: function (response) {
                        resolve(response);
                    },
                    onFailure: function (response) {
                        reject(response);
                    }
                }).send();
            });
        },

        /**
         * event : on load
         */
        load: function () {

            this.FormSetup = document.getElement('#form-setup');

            this.NextStep = document.getElement('#next-button');
            this.BackStep = document.getElement('#back-button');
            this.StepsList = document.getElement('.steps-list');
            this.ListElement = document.getElements('.step');
            this.listElementWidth = document.getElement('.step').getSize().x;

            this.headerList = document.getElement('.header-list');
            this.headerLogoContainer = document.getElement('.header-logo-container');

            this.NavList = document.getElement('.nav-list');
            this.liElm = this.NavList.getElements('li');
            this.fa = this.NavList.getElements('.fa');

            this.inputRadioCheckbox = document.getElements('input[type="radio"], input[type="checkbox"]');
            this.inputText = document.getElements('input[type="text"], input[type="password"], input[type="number"]');
            this.inputSelect = document.getElements('select');
            this.allInputs = document.getElements('input, select');
            this.progressBarDone = document.getElement('.progress-bar-done');
            this.progressbarText = document.getElement('.progress-bar-text');
            this.textColorGrey = true;
            this.licenseCheckbox = document.getElement('.license-checkbox');
            this.licenseLabel = document.getElement('.license-label');

            this.userInputs = document.getElements('.input-text-user, .input-text-password');
            this.pass1 = document.getElement('input[name="userPassword"]');
            this.pass2 = document.getElement('input[name="userPasswordRepeat"]');

            this.activeHeader = document.getElements('.header-list li');

            this.NextStep.addEvent('click', this.next);
            this.BackStep.addEvent('click', this.back);

            // diasble all TAB key
            this.disableAllTab();


            window.addEvents({
                resize: function () {
                    QUIFunctionUtils.debounce(this.recalc, 20);
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
                self.createTemplatePopup(Target);
            });

            // open system check popup
            var buttonSystemCheck = document.getElement('#system-check');
            if (buttonSystemCheck) {
                console.log(1)
                buttonSystemCheck.addEvent('click', function () {
                    self.openSystemCheckPopup();
                })
            }

            // change icon in input field (user step)
            this.userInputs.addEvent('change', function () {
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
            });

            /**
             * check password strength
             */
            require(['bin/js/StrongPass'], function (StrongPass) {
                new StrongPass("userPassword", {
                    injectTarget   : document.getElement('.strong-pass-meter'),
                    injectPlacement: 'bottom',
                    minChar        : 3,
                    colors         : [
                        '#ccc',
                        '#aa0033',
                        '#ffcc33',
                        '#6699cc',
                        '#008000',
                        '#40e240'
                    ]
                });
            });

            // check password
            this.pass2Filled = false;
            this.pass1.addEvent('keyup', function () {
                this.checkPassword();
            }.bind(this));

            this.pass2.addEvent('keyup', function () {
                if (self.pass2.value != '') {
                    self.pass2Filled = true;
                    self.checkPassword();
                }
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
            domain.placeholder = window.location.origin;
            domain.value = window.location.origin;
            rootPath.placeholder = ROOT_PATH + '/';
            rootPath.value = ROOT_PATH + '/';
            urlSubPath.placeholder = subPath;
            urlSubPath.value = subPath;

        },

        /**
         * next step
         * check, if the next step can be executed
         */
        next: function () {

            this.NextStep.blur();
            var self = this;

            // exist the project name?
            if (this.step == 3) {
                this.checkProjectName().then(function () {
                    self.nextExecute();
                }).catch(function (target) {
                    self.createTemplatePopup(target);
                });

                return;
            }

            // data base check
            if (this.step == 4) {

                var Form = QUIFormUtils.getFormData(this.FormSetup);

                if (Form.databaseDriver == '' ||
                    Form.databasePort == '' ||
                    Form.databaseHost == '' ||
                    Form.databaseUser == '' ||
                    Form.databasePassword == '' ||
                    Form.databaseName == '') {
                    QUI.getMessageHandler().then(function (MH) {
                        var message = LOCALE_TRANSLATIONS['setup.web.error.fill.all.fields'];

                        MH.setAttribute('displayTimeMessages', 8000);
                        MH.addError(message);
                    });
                    return;
                }

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
            var pos = currentPos - this.listElementWidth;

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
            this.setDefaultValues();
        },

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
            this.setDefaultValues();
        },

        /**
         * recalc the ListElement width
         */
        recalc: function () {
            this.listElementWidth = document.getElement('.step').getSize().x;
            this.$hideOnResize = false;

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
            var arr   = [],
                liElm = this.headerList.getElements('li'),
                i, len;
            for (i = 0, len = this.liElm.length; i < len; i++) {
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
            var i, len;

            for (i = 0, len = this.inputSelect.length; i < len; i++) {
                this.allInputs.push(+this.inputSelect[i]);
            }
            var names = [];

            for (i = 0, len = this.allInputs.length; i < len; i++) {
                if (!names.include(this.allInputs[i].name)) {
                    names.push(this.allInputs[i].name);
                }
            }

            return names.length;
        },

        /**
         * check the progress
         * take all input fields and show
         * how much of them are filled
         */
        checkProgress: function () {

            var progress,
                inputsDone = 0,
                i, len;

            // check radio and checkbox
            for (i = 0, len = this.inputRadioCheckbox.length; i < len; i++) {
                if (this.inputRadioCheckbox[i].checked) {
                    inputsDone++;
                }
            }

            // check select
            for (i = 0, len = this.inputSelect.length; i < len; i++) {
                if (this.inputSelect[i].value) {
                    inputsDone++;
                }
            }

            // check text, password and number input
            for (i = 0, len = this.inputText.length; i < len; i++) {
                if (this.inputText[i].value) {
                    inputsDone++;
                }
            }

            // "-1" because one input field is not required (db_prefix)
            var inputsNumber = this.countInputs() - 1;

            progress = (inputsDone / inputsNumber * 100).toString();
            var arr = progress.split('.');
            progress = arr[0];

            this.progressbarText.innerHTML = progress + "%";

            if (progress == 0) {
                progress = 1;
            }

            // animate the progress bar
            this.changeProgressBar(progress);
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

        /**
         * Set default values for some fields
         */
        setDefaultValues: function () {
            switch (this.step) {
                case 2:
                    if (!document.getElements('[name="version"]:checked').length) {
                        document.getElements('[name="version"]')[0].checked = true;
                    }
                    break;
                case 3:
                    if (!document.getElements('[name="template"]:checked').length) {
                        document.getElements('[name="template"]').each(function (Input) {
                            if (Input.value === "default") {
                                Input.checked = true;
                            }
                        });

                        if (!document.getElements('[name="template"]:checked').length) {
                            document.getElements('[name="template"]')[0].checked = true;
                        }
                    }
                    break;
                case 4: // test only
                    // this.fillTestData(4);
                    break;
                case 5: // test only
                    // this.fillTestData(5);
                    break;
            }
        },

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
                        port    : Form.databasePort,
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
         * Open system check popup
         */
        openSystemCheckPopup: function () {
            var self = this;

            var Popup = new QUIPopup({
                maxWidth       : 600,
                maxHeight      : 700,
                title          : 'System Check!',
                closeButtonText: 'schließen',
                events         : {
                    onOpen: function (Win) {
                        var Content = Win.getContent();
                        self.checkRequirements().then(function (response) {
                            var ResponseObj = JSON.decode(response);
                            Content.set('html', ResponseObj.htmlResult);

                        }).catch(function () {
                            Content.set('html', 'System check konnte nicht ausgeführt werden');
                        });

                    }
                }

            });
            Popup.open();

        },


        /**
         * Create template popup
         *
         * @param Target
         */
        createTemplatePopup: function (Target) {

            var self = this;
            new QUIConfirm({
                class             : 'template-settings',
                maxWidth          : 460,
                maxHeight         : 540,
                titleicon         : false,
                icon              : false,
                // title             : Target.getAttribute('data-attr-name'),
                title             : LOCALE_TRANSLATIONS['setup.web.popup.title'],
                preset            : Target.getAttribute('data-attr-preset'),
                autoclose         : false,
                ok_button         : {
                    text     : LOCALE_TRANSLATIONS['setup.web.popup.save-button'],
                    textimage: 'icon-ok fa fa-check'
                },
                cancel_button     : false,
                backgroundClosable: false,
                closeButt         : false,
                titleCloseButton  : false,
                events            : {
                    onOpen : function (Win) {
                        var preset = Target.getAttribute('data-attr-preset');
                        self.openTemplatePopup(Win, preset);
                    },
                    onClose: function () {
                        // needed because of chrome render bug
                        document.body.setStyle('transform', 'translateZ(0)');
                        (function () {
                            document.body.setStyle('transform', null);
                        }).delay(100);
                    },

                    onSubmit: function (Win) {
                        var Content     = Win.getContent(),
                            Form        = Content.getElement('form'),
                            data        = QUIFormUtils.getFormData(Form),
                            presetName  = data['preset-name'],
                            de          = '',
                            en          = '',
                            lang        = [],
                            defaultLang = QUIFormUtils.getFormData(self.FormSetup)['project-language'];
                        var presetData = {
                            "project" : {
                                "name"     : data['project-title'],
                                "languages": {
                                    "de": data['project-lang-de'],
                                    "en": data['project-lang-en']
                                }
                            },
                            "template": {
                                "name"   : data['project-template'],
                                "version": "dev-master"
                            }
                        };

                        self.checkPopupForm(Form, data, defaultLang).then(function () {
                            // take form inputs again
                            data = QUIFormUtils.getFormData(Form);

                            // new preset data
                            presetData = {
                                "project" : {
                                    "name"     : data['project-title'],
                                    "languages": {
                                        "de": data['project-lang-de'],
                                        "en": data['project-lang-en']
                                    }
                                },
                                "template": {
                                    "name"   : data['project-template'],
                                    "version": "dev-master"
                                }
                            };

                            console.log('teraz validate preset')
                            return self.validatePresetData(presetData);
                        }).then(function () {
                            return self.updatePreset(presetData, presetName)
                        }).then(function () {
                            Win.close();
                        }).catch(function (error) {
                            console.log(error)
                            Form.getElement(error).setStyle('borderColor', 'red');
                        });

                    }
                }
            }).open();
        },

        /**
         * open the popup in step template
         *
         * @param Win
         * @param preset - the template (preset) for witch the window pops up
         */
        openTemplatePopup: function (Win, preset) {
            var Content    = Win.getContent(),
                self       = this,
                presetName = preset;

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


            this.getPreset(preset).then(function (response) {
                var templateName = response.template.name;

                self.setPresetDataLang(Content, response, presetName);

                self.getAvailableTemplates().then(function (templates) {
                    self.setPresetDataTemplates(Content, templates, templateName)
                }).then(function () {
                    Win.Loader.hide();
                });
            });
        },

        checkProjectName: function () {

            var self = this;

            return new Promise(function (resolve, reject) {
                // open popup - the user should give the project name
                var radios = document.getElements('input[name="template"]'),
                    parent = null,
                    target = null,
                    preset = null;

                radios.each(function (radio) {
                    if (radio.checked) {
                        parent = radio.getParent();
                        preset = radio.value;
                    }
                });

                if (!parent) {
                    parent = radios[0].getParent();
                    preset = radios[0].value;
                }


                self.getPreset(preset).then(function (presetData) {
                    if (presetData['project']['name']) {
                        resolve(true);
                        return;
                    }

                    target = parent.getElement('.step-3-settings-button');
                    reject(target);
                }).catch(function () {
                    console.warn("Wrong preset name. Preset with the name '" + preset + "' couldn't be found");
                })
            });
        },

        /**
         * get the given preset data
         *
         * @param preset - the template (preset) for witch the window opens
         *
         * @returns Promise
         */
        getPreset: function (preset) {
            var presetName = preset.toLowerCase();
            return new Promise(function (resolve, reject) {
                new Request({
                    url    : '/ajax/getPreset.php',
                    noCache: true,
                    data   : {
                        presetName: presetName
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

        setPresetDataLang: function (Content, preset, presetName) {
            Content.getElement('input[name="project-title"]').set(
                'placeholder', preset.project['name']
            );
            Content.getElement('input[name="project-title"]').set(
                'value', preset.project['name']
            );
            Content.getElement('input[name="preset-name"]').set(
                'value', presetName
            );


            var Langs    = preset.project.languages,
                htmlLang = '';


            // loop through the object
            for (var prop in Langs) {
                var langVar = 'setup.web.content.lang.' + prop,
                    lang    = LOCALE_TRANSLATIONS[langVar],
                    checked = '';

                if (Langs[prop] == "true") {
                    checked = 'checked="checked"';
                }
                htmlLang +=
                    '<div class="button-checkbox-wrapper">' +
                    '<input id="project-lang-' + prop + '" class="button-checkbox" ' + checked +
                    'name="project-lang-' + prop + '" type="checkbox" required="required"/>' +
                    '<label for="project-lang-' + prop + '" class="button-checkbox-wrapper-label">' +
                    '<span class="license-label">' + lang + '</span>' +
                    '</label>' +
                    '</div>';
            }

            Content.getElement('.form-input-checkbox-container').set(
                'html', htmlLang
            );

        },

        /**
         * get all available templates
         *
         * @returns {Promise}
         */
        getAvailableTemplates: function () {
            return new Promise(function (resolve, reject) {
                new Request({
                    url    : '/ajax/getAvailableTemplates.php',
                    noCache: true,

                    onSuccess: function (response) {
                        resolve(response);
                    },
                    onFailure: function (response) {
                        reject(response);
                    }
                }).send();
            });
        },

        setPresetDataTemplates: function (Content, templates, templateName) {
            var output,
                selected = '';
            JSON.decode(templates).forEach(function (Elm) {

                if (Elm == templateName) {
                    selected = 'selected';
                }
                output += '<option value=' + Elm + ' ' + selected + '>' + Elm + '</option>';
                selected = '';
            });

            Content.getElement('#project-template').set(
                'html',
                output
            );
        },

        /**
         * Popup data check.
         * If one of the checks failures, return an error.
         *
         * @param Form
         * @param formData
         * @param defaultLang
         * @returns {Promise}
         */
        checkPopupForm: function (Form, formData, defaultLang) {
            return new Promise(function (resolve, reject) {

                // project name
                if (!formData['project-title'] || formData['project-title'] == '') {
                    reject(Form['project-title']);
                    return;
                }

                // lang
                var checked    = false,
                    noChecked  = false,
                    Checkboxes = Form.getElements('input[type="checkbox"'),
                    lang       = 'en';

                switch (defaultLang) {
                    case 'en':
                    case 'de':
                        lang = defaultLang;
                        break;
                }

                Checkboxes.forEach(function (Elm) {
                    if (Elm.checked) {
                        checked = true;
                        return;
                    }
                    noChecked = true;
                });

                // if no checkbox is checked --> check the default
                if (!checked && noChecked) {
                    var name = 'input[name="project-lang-' + lang + '"]';
                    Form.getElement(name).checked = true;
                }
                resolve();
            })
        },

        validatePresetData: function (data) {
            return new Promise(function (resolve, reject) {
                new Request({
                    url      : '/ajax/validatePresetData.php',
                    noCache  : true,
                    data     : {
                        data: data
                    },
                    onSuccess: function () {
                        resolve();
                    },
                    onFailure: function (response) {
                        reject(response);
                    }
                }).send();
            });
        },

        updatePreset: function (presetData, presetName) {

            // comma separated string from a lang array - needet for updatePreset.php
            //var lang = presetData.project.languages.join(',');
            return new Promise(function (resolve, reject) {
                new Request({
                    url      : '/ajax/updatePreset.php',
                    noCache  : true,
                    data     : {
                        presetname  : presetName,
                        projectname : presetData.project.name,
                        languages   : presetData.project.languages,
                        templatename: presetData.template.name

                    },
                    onSuccess: function () {
                        resolve();
                    },
                    onFailure: function (response) {
                        reject(response);
                    }
                }).send();
            });
        },

        /**
         * check password (equal? not to short?)
         */
        checkPassword: function () {

            if (!this.pass2Filled) {
                return;
            }

            var labels = document.getElements('.animated-label-error');

            if (this.pass1.value != this.pass2.value) {
                labels.forEach(function (Elm) {
                    Elm.setStyle('color', 'red');
                });
                return;
            }

            labels.forEach(function (Elm) {
                Elm.setStyle('color', '');
            })
        },

        /**
         * buttons to change the language
         */
        languageButtons: function () {
            // language button
            var url    = window.location.search.substr(1),
                Params = {};

            url = url.split('&');

            for (var i = 0, len = url.length; i < len; i++) {
                var parts = url[i].split('=');
                Params[parts[0]] = parts[1];
            }

            if (Params.hasOwnProperty("language")) {
                var langButtons = document.getElement('.change-language').getChildren();

                langButtons.forEach(function (button) {
                    var lang = button.getAttribute('data-attr-lang');
                    if (lang == Params['language']) {
                        button.addClass('active-lang')
                    }

                    button.addEvent('click', function () {
                        var link = window.location.origin + '/?language=' + lang;
                        window.location = link;
                    })
                })
            }
        },
        /**
         * execute the setup
         */
        $exeInstall    : function () {
            var Loader = new QUILoader({type: 'line-scale'});
            Loader.inject(document.getElement('body'));
            Loader.show();


            var data = this.parseFormData(QUIFormUtils.getFormData(this.FormSetup));

            new Request({
                url      : '/ajax/createSetupDataJson.php',
                noCache  : true,
                data     : {
                    data: data
                },
                onSuccess: function () {
                    window.location = window.location.origin + '/web-install.php';
                },
                onFailure: function (error) {
                    Loader.hide();
                    QUI.getMessageHandler(error).then(function (MH) {

                        MH.setAttribute('displayTimeMessages', 8000);
                        MH.addError(error['responseText']);
                    });
                }
            }).send();
        },

        /**
         * parse the form data to a JSON object
         *
         * @param formData
         * @returns object
         */
        parseFormData: function (formData) {
            return {
                lang    : formData['project-language'],
                version : formData['version'],
                preset  : formData['template'],
                database: {
                    driver: formData['databaseDriver'],
                    host  : formData['databaseHost'],
                    user  : formData['databaseUser'],
                    pw    : formData['databasePassword'],
                    name  : formData['databaseName'],
                    prefix: formData['databasePrefix'],
                    port  : formData['databasePort']
                },
                user    : {
                    name: formData['userName'],
                    pw  : formData['userPassword']
                },
                paths   : {
                    host       : formData['domain'],
                    cms_dir    : formData['rootPath'],
                    url_lib_dir: formData['rootPath'] + 'lib/',
                    usr_dir    : formData['rootPath'] + 'usr/',
                    url_dir    : formData['URLsubPath'],
                    url_bin_dir: formData['rootPath'] + 'bin/',
                    opt_dir    : formData['rootPath'] + 'packages/',
                    var_dir    : formData['rootPath'] + 'var/'
                }
            };
        }
    });
});
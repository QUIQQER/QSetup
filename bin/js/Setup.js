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
            'openPopup',
            'getPreset',
            'setPresetDataLang',
            'getAvailableTemplates',
            'setPresetDataTemplate',
            'checkPopupForm',
            'validatePresetData',
            'updatePreset',
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
                    maxWidth     : 460,
                    maxHeight    : 540,
                    titleicon    : false,
                    icon         : false,
                    title        : Target.getAttribute('data-attr-name'),
                    preset       : Target.getAttribute('data-attr-preset'),
                    autoclose    : false,
                    cancel_button: {
                        text     : LOCALE_TRANSLATIONS['setup.web.popup.cancel-button'],
                        textimage: 'icon-remove fa fa-remove'
                    },
                    ok_button    : {
                        text     : LOCALE_TRANSLATIONS['setup.web.popup.save-button'],
                        textimage: 'icon-ok fa fa-check'
                    },
                    events       : {
                        onOpen : function (Win) {
                            var preset = Target.getAttribute('data-attr-preset');
                            self.openPopup(Win, preset);
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

                                return self.validatePresetData(presetData);
                            }).then(function () {
                                return self.updatePreset(presetData, presetName)
                            }).then(function () {
                                Win.close();
                            }).catch(function (error) {
                                Form.getElement(error).setStyle('borderColor', 'red');
                            });

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
                    if (!document.getElements('[name="template"]:checked').length) {
                        document.getElements('[name="template"]')[0].checked = true;
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
         * @param preset - the template (preset) for witch the window pops up
         */
        openPopup: function (Win, preset) {
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

        /**
         * get the given preset data
         *
         * @param preset - the template (preset) for witch the window pops up
         *
         * @returns {*}
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

        checkPopupForm: function (Form, formData, defaultLang) {
            return new Promise(function (resolve, reject) {

                // project name
                if (!formData['project-title'] || formData['project-title'] == '') {
                    reject('input[name="project-title"]');
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
                    var name                      = 'input[name="project-lang-' + lang + '"]';
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
         * install -> send data
         * (test)
         */
        $exeInstall: function () {

            console.info(QUIFormUtils.getFormData(this.FormSetup));

            // daten setzen in "/setupdata.json";
            // daten prüfen
            // wenn alles ok, dann weiterleitung auf web-install

            /*
             window.location = window.location.origin + '/web-install.php';
             */
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
window.addEvent("domready", function () {
    "use strict";

    require(['qui/controls/buttons/Button'], function () {
    });


    require(['bin/js/Setup'], function (Setup) {
        var SetupControl = new Setup();

        checkRequirements()
            .then(function (response) {
                console.warn(response);

                if (response == "true") {
                    SetupControl.load();
                    return;
                }

                var errorContainer = document.getElement('.check-requirements-error'),
                    html = '';
                errorContainer.setStyle('display', 'block');
                document.getElement('.check-requirements-noError').setStyle('display', 'none');

                html += '<h1>Serveranfordernugen-Tests fehlgeschlagen</h1>';
                html += '<p>Die Servereinstellungen entsprechen nicht den Mindestanforderungen von QUIQQER. Bitte beheben Sie diese bevor Sie mit der Installation beginnen.</p>';
                html += '<div class="check-error-container">';
                html += response;
                html += '</div>';

                errorContainer.set(
                    'html',
                    html
                )
            })
            .catch(function () {
                document.write('Error by checking the server requirements');
            });
    });

    var checkRequirements = function () {
        return new Promise(function (resolve, reject) {
            new Request({
                url      : '/ajax/checkRequirements.php',
                noCache  : true,
                onSuccess: function (response) {
                    resolve(response);
                },
                onFailure: function () {
                    reject()
                }
            }).send();
        });
    }
});
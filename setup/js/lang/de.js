define('lang/de', [ 'Locale' ], function(Locale)
{

    Locale.set("de", "quiqqer/websetup", {

        "setup.btn.check.database"  : "Datenbank prüfen",
        "setup.btn.start"           : "QUIQQER installieren",
        "setup.btn.edit.paths"      : "Alle Pfade manuell bearbeiten",
        "setupfile.no.html5"        : "Ihr Browser unterstützt leider kein HTML5. Die Installation kann somit leider nicht über eine Installations-Datei erfolgen.",
        "setup.btn.check.setupfile" : "Installations-Datei einlesen",

        "setupfile.success"            : "Die Daten der Installations-Datei wurden erfolgreich eingelesen. Sie können die Installation nun ausführen.",
        "setupfile.error.incomplete"   : "Die Daten der Insallations-Datei wurden erfolgreich eingelesen. Es fehlen jedoch noch einige notwendige Angaben. Bitte füllen Sie das Formular komplett aus.",
        "setupfile.error.wrong.format" : "Die Installations-Datei konnte nicht gelesen werden.",

        "database.missing.credentials" : "Bitte füllen Sie zunächst die Felder für Treiber, Host, Name, Benutzer und Passwort aus.",
        "database.create.text"         : "Die Datenbank scheint nicht zu existieren. Soll QUIQQER versuchen, die Datenbank anzulegen?",
        "database.create.confirm"      : "Datenbank erstellen",
        "database.created"             : "Datenbank wurde erfolgreich erstellt.",
        "database.test.success"        : "Die Datenbank-Verbindung wurde erfolgreich überprüft und kann für die Installation verwendet werden."

    });

});
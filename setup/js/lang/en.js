define('lang/en', [ 'Locale' ], function(Locale)
{

    Locale.set("en", "quiqqer/websetup", {

        "setup.btn.check.database"  : "Check database",
        "setup.btn.start"           : "Install QUIQQER",
        "setup.btn.edit.paths"      : "Edit paths manually",
        "setupfile.no.html5"        : "Unfortunately, your browser does not support HTML5 and thus does not support installation via setup file.",
        "setup.btn.check.setupfile" : "Read setup file",

        "setupfile.success"            : "Setup information from the setup file has been read succesfully. You can start the setup now.",
        "setupfile.error.incomplete"   : "Setup information from the setup file has been read succesfully. Unfortunately, there are some missing fields. Please fill them out first before starting the setup.",
        "setupfile.error.wrong.format" : "The setup file could not be read.",

        "database.missing.credentials" : "Please fill out the fields for driver, host, name, user and password first.",
        "database.create.text"         : "It seems the database does not exist. Should QUIQQER try to create it?",
        "database.create.confirm"      : "Create database",
        "database.created"             : "Database has been created succesfully.",
        "database.test.success"        : "The database connection has been checked succesfully and can now be used for QUIQQER."

    });

});
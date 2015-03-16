<?php

/**
 * German
 *
 * @var $this->Locale \QUI\Locale
 */

$this->Locale->set('de', 'quiqqer/installer', array(

    'yes' => 'ja',
    'no'  => 'nein',

    // version
    'step.version.title'   => 'Versionswahl',
    'step.version.list'    => 'Folgende Versionen stehen zur Auswahl: ',
    'step.version.choice'  => 'Version',

    // db
    'step.2.title'         => 'Schritt 2 : Datenbank-Verbindung',
    'step.2.db.driver'     => 'Datenbanktreiber (mysql,sqlite) [mysql]: ',
    'step.2.db.new'        => 'Name der zu erstellenden Datenbank [quiqqer]: ',
    'step.2.db.old'        => 'Name der bestehenden Datenbank: ',
    'step.2.db.create.new' => 'Möchten Sie QUIQQER in eine bereits bestehende Datenbank installieren? [NEIN/ja] :',
    'step.2.db.prefix'     => 'Möchten Sie einen Prefix für Ihre Datenbanktabellen? Keine Eingabe heißt kein Prefix: ',

    // user
    'step.3.title'                 => 'Schritt 3 : Einen Administrator für QUIQQER festlegen',
    'step.3.error.user.exist'      => 'Die Benutzertabelle existiert bereits. QUIQQER kann nicht installiert werden.',
    'step.3.error.group.exist'     => 'Die Gruppentabelle existiert bereits. QUIQQER kann nicht installiert werden.',
    'step.3.enter.username'        => 'Bitte geben Sie einen Benutzernamen ein:',
    'step.3.enter.password'        => 'Bitte geben Sie ein Passwort ein:',
    'step.3.error.dbxml.not.exist' => 'Konnte database.xml für die gewählte Version nicht finden. QUIQQER kann nicht installiert werden.',

    // paths
    'step.4.title'          => 'Schritt 4 : Installationspfade fpr QUIQQER festlegen',
    'step.4.attention'      => 'Achtung: Wenn Sie nicht genau wissen was Sie hier einstellen sollen, lassen Sie bitte die standard Einstellungen.',
    'step.4.paths.change'   => 'Möchten Sie die Pfade von QUIQQER selbst festlegen und ändern? ',
    'step.4.paths.change.a' => '[NEIN/ja] :',

    'step.4.paths.q1' => 'Bitte geben Sie den CMS Pfad an - Der Hauptpfad beinhaltet das QUIQQER System',
    'step.4.paths.q2' => 'Bitte geben Sie den LIB Pfad an - Das LIB Verzeichnis beinhaltet die QUIQQER Bibliotheken',
    'step.4.paths.q3' => 'Bitte geben Sie den BIN Pfad an - Das BIN Verzeichnis beinhaltet die Dateien vom QUIQQER System die über den Webserver verfügbar sein müssen.',
    'step.4.paths.q4' => 'Bitte geben Sie den USR Pfad an - Das USR Verzeichnis beinhaltet die Projekt Dateien.',
    'step.4.paths.q5' => 'Bitte geben Sie den OPT Pfad an - Das OPT Verzeichnis beinhaltet die Plugins und Paket Dateien. Es ist das vendor Verzeichnis von Composer.',
    'step.4.paths.q6' => 'Bitte geben Sie den VAR Pfad an - Das VAR Verzeichnis beinhaltet alle temporären Dateien, wie Cache Dateien, Logs und vieles mehr.',
    'step.4.paths.q7' => 'Bitte geben Sie den Host an. Unter dieser URL / Domain ist Quiqqer erreichbar. (z.B.: http://www.my-domain.de)',

    // install & download
    'step.5.title'               => 'Download und Installation des Systems',
    'step.5.htaccess.exists'     => 'Eine .htaccess Datei besteht bereits. Bitte fügen Sie folgende Anweisungen der Datei hinzu:',
    'step.5.install.message'     => 'Die Installation von Composer und QUIQQER kann ein bißchen dauern ... Ich empfehle dir einen Kaffee zu hohlen ... ;-)',
    'step.5.download.successful' => 'Composer und QUIQQER wurde erfolgreich herrunter geladen',
    'step.5.cleanup'             => 'Starte Säuberung',
    'step.5.successful'          => 'Setup erfolgreich durchgeführt',

    // exceptions
    'config.not.writable' => 'Config ist nicht schreibbar'
));
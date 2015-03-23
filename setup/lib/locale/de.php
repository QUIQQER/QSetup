<?php

/**
 * German
 *
 * @var $this->Locale \QUI\Locale
 */

$this->Locale->set( 'de', 'quiqqer/websetup', array(

    'noscript' => 'JavaScript scheint in Ihrem Browser deaktiviert zu sein. Um QUIQQER zu installieren (und zu nutzen) aktivieren Sie bitte JavaScript.',
    'welcome'  => '<h1>
                    Willkommen bei der QUIQQER Installation
                   </h1>

                   <ul>
                       <li>Bitte füllen Sie das Installations-Formular aus, um QUIQQER auf Ihrem System zu installieren.</li>
                       <li>Sie haben Fragen oder benötigen Hilfe? Dann besuchen Sie <a href="http://www.quiqqer.com" target="_blank">www.quiqqer.com</a>.</li>
                   </ul>',

    'lang.title' => 'QUIQQER Sprache',
    'lang.label' => 'Sprache',
    'lang.desc'  => 'Bitte wählen Sie die Installationssprache. Dies ist die Sprache, die standardmäßig in Ihrem QUIQQER-System angelegt wird.' .
        ' Aber keine Sorge, Sie können in QUIQQER jederzeit weitere Sprachen anlegen.',
    'lang.en'    => 'English',
    'lang.de'    => 'Deutsch',

    'setupfile.title' => 'Installation aus Datei',
    'setupfile.desc'  => 'Sie können QUIQQER mit Hilfe einer vorgefertigten Installations-Datei installieren. Bitte laden Sie dazu eine entsprechende Datei hoch.',

    'setupfile.label' => 'Installations-Datei wählen (*.setup)',

    'version.title' => 'QUIQQER Version',
    'version.label' => 'Version',
    'version.desc'  => 'Bitte wählen Sie die QUIQQER Version, die Sie installieren möchten. Die \'master\' und \'dev\' Versionen sind Entwicklungs-Versionen und daher nicht immer stabil.',

    'database.title'          => 'Datenbank',
    'database.desc'           => 'Bitte füllen Sie alle Datenbank-Details aus. Stellen Sie sicher, dass der Benutzer, den Sie angeben, alle nötigen Rechte auf Ihrem Datenbank-Server hat.',
    'database.driver.label'   => 'Datenbank-Treiber',
    'database.host.label'     => 'Datenbank-Host',
    'database.name.label'     => 'Datenbank-Name',
    'database.user.label'     => 'Datenbank-Benutzer',
    'database.password.label' => 'Datenbank-Passwort',
    'database.prefix.label'   => 'Datenbank-Tabellen Präfix',
    'database.prefix.desc'    => 'Sie können ein Präfix bestimmen, welches allen Tabellen, die QUIQQER in Ihrer Datenbank anlegt, vorangestellt wird (z.B. "quiqqer" => "quiqqer_tabellenname").',

    'user.title'          => 'Root Benutzer',
    'user.desc'           => 'Bitte wählen Sie einen Root Benutzer für Ihr QUIQQER System. Dies ist der Benutzer, mit dem Sie sich das erste Mal in QUIQQER einloggen werden.',
    'user.name.label'     => 'Benutzername',
    'user.password.label' => 'Passwort',

    'paths.title'          => 'Host und Pfade',
    'paths.desc'           => 'Sie können alle für das QUIQQER System notwendigen Pfade manuell angeben oder es bei den Standard-Werten belassen.',
    'paths.host.label'     => 'Host',
    'paths.host.desc'      => 'Die ist die URL bzw. domain unter der QUIQQER erreichbar ist (lokal oder fern  - z.B..: http://www.my-domain.de).',
    'paths.url.label'      => 'URL Verzeichenis',
    'paths.url.desc'       => 'Dies ist das <b>relativ</b> zur Host-URL liegende Verzeichnis, unter welchem ihre QUIQQER-System liegt.',
    'paths.cms.label'      => 'CMS Verzeichnis',
    'paths.cms.desc'       => 'Dies ist der <b>absolute</b> Pfad zum Wurzelverzeichnis Ihres QUIQQER-Systems.',
    'paths.bin.label'      => 'BIN Verzeichnis',
    'paths.bin.desc'       => 'Dies ist das Verzeichnis, welches von Ihrem Web-Server aus erreichbar ist.',
    'paths.lib.label'      => 'LIB Verzeichnis',
    'paths.lib.desc'       => 'Dies ist das Verzeichnis für alle QUIQQER-Bibliotheken.',
    'paths.packages.label' => 'OPT Verzeichnis',
    'paths.packages.desc'  => 'Dies ist das Verzeichnis für alle QUIQQER-Pakete. <i>composer</i> benutzt dies als vendor-Verzeichnis.',
    'paths.usr.label'      => 'USR Verzeichnis',
    'paths.usr.desc'       => 'Dies ist das Verzeichnis für alle QUIQQER-Projekt-Templates und -Layouts.',
    'paths.var.label'      => 'VAR Verzeichnis',
    'paths.var.desc'       => 'In diesem Verzeichnis befinden sich alle temporären Dateien wie Cache-, Log- und temporäre Upload-Dateien.',

    'footer' => 'besuchen Sie uns auf <a href="http://www.quiqqer.com" target="_blank">www.quiqqer.com</a>',

    'missing.db.driver'       => 'Bitte legen Sie einen Datenbank-Treiber fest.',
    'missing.db.host'         => 'Bitte geben Sie einen Datenbank-Host an.',
    'missing.db.database'     => 'Bitte geben Sie einen Datenbank-Namen an.',
    'missing.db.user'         => 'Bitte geben Sie einen Datenbank-Benutzer an.',
    'missing.db.password'     => 'Bitte geben Sie ein Datenbank-Benutzer Passwort an.',
    'db.driver.not.supported' => 'Dieser Datenbank-Treiber wird von QUIQQER noch nicht unterstützt.',

    'missing.username' => 'Bitte geben Sie einen root-Benutzer an.',
    'missing.password' => 'Bitte geben Sie ein Passwort für den root-Benutzer an.',

    'missing.folder' => 'Bitte geben Sie einen [folder]-Pfad an.'

));

$this->Locale->set('de', 'quiqqer/database', array(

    "check.could.not.create" => "Die Datenbank konnte leider nicht erstellt werden. Bitte prüfen Sie, ob der angegebene Nutzer alle nötigen Rechte hat."

));

$this->Locale->set('de', 'quiqqer/installer', array(

    'yes' => 'ja',
    'no'  => 'nein',

    'json.error'           => 'Die übergebene Setup-Datei enthält kein korrektes JSON. Das Setup wird den Inhalt dieser Datei daher ignorieren.',

    // version
    'step.version.title'   => 'Versionswahl',
    'step.version.list'    => 'Sie können zwischen folgenden QUIQQER Versionen wählen: ',
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
    'step.3.error.dbxml.not.found' => 'Konnte database.xml für die gewählte Version nicht finden. Weiche auf database.xml der aktuellen master-Version aus.',
    'step.3.error.dbxml.not.exist' => 'Konnte keine database.xml finden. QUIQQER kann nicht installiert werden.',

    // paths
    'step.4.title'          => 'Schritt 4 : Installationspfade fpr QUIQQER festlegen',
    'step.4.attention'      => 'Achtung: Wenn Sie nicht genau wissen was Sie hier einstellen sollen, lassen Sie bitte die standard Einstellungen.',
    'step.4.paths.change'   => 'Möchten Sie die Pfade von QUIQQER selbst festlegen und ändern? ',
    'step.4.paths.change.a' => '[NEIN/ja] :',

    'step.4.paths.q0' => 'Bitte geben Sie den URL Pfad an - Dies ist der Pfad relativ zum Hauptpfad Ihres Webservers, unter dem das QUIQQER-System erreichbar sein wird',
    'step.4.paths.q1' => 'Bitte geben Sie den CMS Pfad an - Der Hauptpfad beinhaltet das QUIQQER System',
    'step.4.paths.q2' => 'Bitte geben Sie den LIB Pfad an - Das LIB Verzeichnis beinhaltet die QUIQQER Bibliotheken',
    'step.4.paths.q3' => 'Bitte geben Sie den BIN Pfad an - Das BIN Verzeichnis beinhaltet die Dateien vom QUIQQER System die über den Webserver verfügbar sein müssen.',
    'step.4.paths.q4' => 'Bitte geben Sie den USR Pfad an - Das USR Verzeichnis beinhaltet die Projekt Dateien.',
    'step.4.paths.q5' => 'Bitte geben Sie den PACKAGES Pfad an - Das PACKAGES Verzeichnis beinhaltet die Plugins und Paket Dateien. Es ist das vendor Verzeichnis von Composer.',
    'step.4.paths.q6' => 'Bitte geben Sie den VAR Pfad an - Das VAR Verzeichnis beinhaltet alle temporären Dateien, wie Cache Dateien, Logs und vieles mehr.',
    'step.4.paths.q7' => 'Bitte geben Sie den Host an. Unter dieser URL / Domain ist Quiqqer erreichbar. (z.B.: http://www.my-domain.de)',

    // install & download
    'step.5.title'               => 'Download und Installation des Systems',
    'step.5.htaccess.exists'     => 'Eine .htaccess Datei besteht bereits. Bitte fügen Sie folgende Anweisungen der Datei hinzu:',
    'step.5.install.message'     => 'Die Installation von Composer und QUIQQER kann ein bisschen dauern. Der perfekte Zeitpunkt für einen frischen Kaffee... ;-)',
    'step.5.download.successful' => 'Composer und QUIQQER wurden erfolgreich heruntergeladen',
    'step.5.cleanup'             => 'Starte Säuberung',
    'step.5.successful'          => 'Setup erfolgreich durchgeführt',

    // exceptions
    'config.not.writable' => 'Config ist nicht schreibbar',

    'create.projects' => 'Erstelle Projekte...',
    'start.tests'     => 'Führe QUIQQER System-Gesundheits-Check und Unit Tests aus...',
    'start.langs'     => 'Füge Sprachen hinzu...'
));
<?php

/**
 * English
 *
 * @var $this->Locale \QUI\Locale
 */



$this->Locale->set( 'en', 'quiqqer/websetup', array(
    'noscript' => 'JavaScript seems to be deactivated on your computer. In order to install (and use) QUIQQER, please activate JavaScript.',
    'welcome'  => '<h1>
                    Welcome to the QUIQQER setup
                </h1>

                <ul>
                    <li>Please fill out the setup form or upload a setup file to install QUIQQER on your system.</li>
                    <li>Do you have any questions or need help? Please visit <a href="http://www.quiqqer.com" target="_blank">www.quiqqer.com</a>!</li>
                </ul>',

    'lang.title' => 'Choose the QUIQQER language',
    'lang.label' => 'Language',
    'lang.desc'  => 'Please choose the setup language. This will also be the default language that is set up in your QUIQQER installation.' .
        ' But don\'t worry, you can add as many languages as you like later.',
    'lang.en'    => 'English',
    'lang.de'    => 'Deutsch',

    'setupfile.title' => 'Setup from file',
    'setupfile.desc'  => 'You can install QUIQQER via setup file. To do so, please upload a QUIQQER .setup file.',

    'setupfile.label' => 'Choose setup file (*.setup)',

    'version.title' => 'Choose the QUIQQER version',
    'version.label' => 'Version',
    'version.desc'  => 'Please choose the version of QUIQQER you want to install. The \'master\' and \'dev\' versions are development versions and not necessarily stable.',

    'database.title'          => 'Database setup',
    'database.desc'           => 'Please fill out the database credentials. Make sure the user you provide has the necessary rights on your database server.',
    'database.driver.label'   => 'Database driver',
    'database.host.label'     => 'Database host',
    'database.name.label'     => 'Database name',
    'database.user.label'     => 'Database user',
    'database.password.label' => 'Database password',
    'database.prefix.label'   => 'Database table prefix',
    'database.prefix.desc'    => 'You can choose a prefix for all table QUIQQER creates (e.g. \'quiqqer\' => \'quiqqer_tablename\'.',

    'user.title'          => 'Root user setup',
    'user.desc'           => 'Please choose a default root user for your QUIQQER system. That will be the user you login with first.',
    'user.name.label'     => 'Username',
    'user.password.label' => 'Password',

    'paths.title'          => 'Host and paths setup',
    'paths.desc'           => 'You can setup all essential paths for your QUIQQER system or you can leave the default values.',
    'paths.host.label'     => 'Host',
    'paths.host.desc'      => 'This is the URL/domain under which QUIQQER is accessed (local or remote - e.g.: http://www.my-domain.com).',
    'paths.url.label'      => 'URL directory',
    'paths.url.desc'       => 'This is the directory <b>relative</b> to your host URL where your QUIQQER system files are.',
    'paths.cms.label'      => 'CMS directory',
    'paths.cms.desc'       => 'This is the <b>absolute</b> path to the root folder of your QUIQQER system.',
    'paths.bin.label'      => 'BIN directory',
    'paths.bin.desc'       => 'This is the directory that is accessible from the web server.',
    'paths.lib.label'      => 'LIB directory',
    'paths.lib.desc'       => 'This is the directory where all QUIQQER libraries are.',
    'paths.packages.label' => 'Packages directory',
    'paths.packages.desc'  => 'This is the directory where all QUIQQER packages and plugins are. <i>composer</i> uses this as its vendor directory.',
    'paths.usr.label'      => 'USR directory',
    'paths.usr.desc'       => 'This is the directory for alle QUIQQER project templates and layouts.',
    'paths.var.label'      => 'VAR directory',
    'paths.var.desc'       => 'This directory contains all temp files, like the cache, temporary uploads, logs and many more.',

    'footer' => 'visit us at <a href="http://www.quiqqer.com" target="_blank">www.quiqqer.com</a>'

));

$this->Locale->set('de', 'quiqqer/database', array(

    "check.could.not.create" => "The database could not be created. Please check if the user you specified has the necessary rights."

));

$this->Locale->set('en', 'quiqqer/installer', array(

    'yes' => 'yes',
    'no'  => 'no',

    'json.error'           => 'The setup file you provided does not contain valid JSON. Thus the file contents will be ignored.',

    // version
    'step.version.title'   => 'Version select',
    'step.version.list'    => 'You can choose between the following QUIQQER versions: ',
    'step.version.choice'  => 'Version',

    // db
    'step.2.title'         => 'Step 2 Database connection',
    'step.2.db.prefix'     => 'Want you a prefix for your database tables? if no, leave it empty: ',
    'step.2.db.driver'     => 'Database driver (mysql,sqlite) [mysql]: ',
    'step.2.db.new'        => 'Name of new database [quiqqer]: ',
    'step.2.db.old'        => 'Name of existing database: ',
    'step.2.db.create.new' => 'Would you like to use an existing database? [NO/yes] :',

    // user
    'step.3.title'                 => 'Step 3 set a root / administrator user for QUIQQER',
    'step.3.error.user.exist'      => 'The user table already exist. You cannot install QUIQQER.',
    'step.3.error.group.exist'     => 'The group table already exist. You cannot install QUIQQER.',
    'step.3.enter.username'        => 'Please enter a username:',
    'step.3.enter.password'        => 'Please enter a password:',
    'step.3.error.dbxml.not.found' => 'Could not find database.xml file for selected version. Switch to database.xml of master version.',
    'step.3.error.dbxml.not.exist' => 'Could not find database.xml file. QUIQQER can not be installed.',

    // paths
    'step.4.title'          => 'Step 4 set the installation paths and the host of QUIQQER',
    'step.4.attention'      => 'Attention: If you dont\'t know what you do, please use the default settings.',
    'step.4.paths.change'   => 'Do you want to change the following installation path of quiqqer? ',
    'step.4.paths.change.a' => '[NO/yes] :',

    'step.4.paths.q0' => 'Please enter the url-dir - This is the directory relative to your web server root directory under which your QUIQQER system will be reachable.',
    'step.4.paths.q1' => 'Please enter the cms-dir - The main directory contains the whole QUIQQER system.',
    'step.4.paths.q2' => 'Please enter the lib-dir - The lib directory contains all the quiqqer libraries.',
    'step.4.paths.q3' => 'Please enter the bin-dir - The bin directory contains all the files you need to be accessible from the Web-Server.',
    'step.4.paths.q4' => 'Please enter the usr-dir - The usr directory contains all the project templates.',
    'step.4.paths.q5' => 'Please enter the opt-dir - The opt directory contains all plugins and packages. Its the vendor vendor-dir for composer.',
    'step.4.paths.q6' => 'Please enter the var-dir - The var directory contains all temp files, like the cache, temporary uploads, logs and many more.',
    'step.4.paths.q7' => 'Please enter the host - Under which url / domain is quiqqer accessed? (eq: http://www.my-domain.de)',

    // install & download
    'step.5.title'               => 'Downloading and installing the system',
    'step.5.htaccess.exists'     => 'A .htaccess file already exist. Please add the following to the htacess file:',
    'step.5.install.message'     => 'Installing Composer and QUIQQER can may take a little bit ... I suggest you drink a coffee ... ;-)',
    'step.5.download.successful' => 'Composer and quiqqer successful downloaded',
    'step.5.cleanup'             => 'Starting cleanup',
    'step.5.successful'          => 'Setup completed',

    // exceptions
    'config.not.writable' => 'Config is not writable',

    'create.projects' => 'Creating projects...',
    'start.tests'     => 'Executing QUIQQER Health Check and Unit Tests'

));
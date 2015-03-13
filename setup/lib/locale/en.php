<?php

/**
 * English
 *
 * @var $this->Locale \QUI\Locale
 */

$this->Locale->set('en', 'quiqqer/installer', array(

    'yes' => 'yes',
    'no'  => 'no',

    // db
    'step.2.title'         => 'Step 2 Database connection',
    'step.2.db.prefix'     => 'Want you a prefix for your database tables? if no, leave it empty: ',
    'step.2.db.driver'     => 'Database driver (mysql,sqlite) [mysql]: ',
    'step.2.db.new'        => 'Name of new database [quiqqer]: ',
    'step.2.db.old'        => 'Name of existing database: ',
    'step.2.db.create.new' => 'Would you like to use an existing database? [NO/yes] :',

    // user
    'step.3.title'             => 'Step 3 set a root / administrator user for QUIQQER',
    'step.3.error.user.exist'  => 'The user table already exist. You cannot install QUIQQER.',
    'step.3.error.group.exist' => 'The group table already exist. You cannot install QUIQQER.',
    'step.3.enter.username'    => 'Please enter a username:',
    'step.3.enter.password'    => 'Please enter a password:',

    // paths
    'step.4.title'          => 'Step 4 set the installation paths and the host of QUIQQER',
    'step.4.attention'      => 'Attention: If you dont\'t know what you do, please use the default settings.',
    'step.4.paths.change'   => 'Do you want to change the following installation path of quiqqer? ',
    'step.4.paths.change.a' => '[NO/yes] :',

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
    'config.not.writable' => 'Config is not writable'
));
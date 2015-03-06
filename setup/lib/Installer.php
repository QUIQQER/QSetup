<?php

/**
 * This file contains the \QUI\Installer class
 */

namespace QUI;

/**
 * Installs QUIQQER,
 * checks the database
 * create all needed folders and
 * download composer.phar to install QUIQQER
 *
 * @author www.pcsg.de (Henning Leutz)
 * @package com.pcsg.qui
 */

class Installer
{
    /**
     * config params from the user
     * @var array $_params
     */
    protected $_params;

    /**
     * the database object
     * @var \PDO $_PDO
     */
    protected $_PDO = null;

    /**
     * the cms user
     * @var String $_username
     */
    protected $_username = '';

    /**
     * the password for the cms user
     * @var String $_password
     */
    protected $_password = '';

    /**
     * no output flag, if it is true, than no output
     * @var Bool
     */
    protected $_no_output = false;

    public $Locale;

    /**
     * installer constructor
     *
     * @param array $params - installation params
     */
    public function __construct($params=array())
    {
        $this->_params = $params;
        $this->_step   = 0;

        require 'Locale.php';

        $this->Locale = new Locale();
    }

    /**
     * Starts the installer
     */
    public function start()
    {
        // set the paths
        $this->language();

        // database
        $this->database();

        // create user and group
        $this->user_and_group();

        // set the paths
        $this->paths();

        // create quiqqer - boooya
        $this->execute();
    }

    /**
     * Write an output line
     *
     * @param String $str
     */
    public function writeLn($str)
    {
        if ( $this->_no_output ) {
            return;
        }

        echo $str ."\n";
    }

    /**
     * Write an output
     *
     * @param String $str
     */
    public function write($str)
    {
        if ( $this->_no_output ) {
            return;
        }

        echo $str;
    }

    /**
     * Is installer on step §step?
     *
     * @param String $step
     * @return Bool
     */
    public function isOnStep($step)
    {
        return $this->_step == $step ? true : false;
    }

    /**
     * Set the no output flag to true
     */
    public function setNoOutput()
    {
        $this->_no_output = true;
    }

    /**
     * Steps
     */

    /**
     * Language step
     */
    public function language()
    {
        $this->writeLn( '=========================================' );
        $this->writeLn( 'Step 1 Language of QUIQQER' );
        $this->writeLn( '' );

        $this->writeLn( "Which language do you want to use? (de=German,en=English) [en]: " );
        $lang = trim( fgets( STDIN ) );

        if ( empty( $lang ) ) {
            $lang = 'en';
        }

        switch ( $lang )
        {
            case 'en':
            case 'de':
                include 'locale/'. $lang .'.php';

                $this->Locale->setCurrent( $lang );
            break;

            default:
                $this->writeLn( "Language not found ... " );
                $this->language();
            break;
        }
    }

    /**
     * Database step
     */
    public function database()
    {
        $this->_step = 'database';

        $this->writeLn( '=========================================' );
        $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.2.title' ) );
        $this->writeLn( '' );

        // driver
        if ( !isset( $this->_params['db_driver'] ) || empty( $this->_params['db_driver'] ) )
        {
            $this->write( $this->Locale->get( 'quiqqer/installer', 'step.2.db.question' ) );
            $this->_params['db_driver'] = trim( fgets( STDIN ) );

            if ( empty( $this->_params['db_driver'] ) ) {
                $this->_params['db_driver'] = 'mysql';
            }
        }

        // swtch to the right db installer
        switch ( $this->_params['db_driver'] )
        {
            case 'sqlite':
                require_once 'installer/SQLite.php';

                $result = installer\SQLite::database( $this->_params, $this );

            break;

            case 'mysql':
                require_once 'installer/DataBase.php';

                $result = installer\DataBase::database( $this->_params, $this );

            break;
        }

        $this->_PDO    = $result['PDO'];
        $this->_params = array_merge( $this->_params, $result['params'] );

        // database prefix
        if ( !isset( $this->_params['db_prefix'] ) )
        {
            $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.2.db.prefix' ) );
            $this->_params['db_prefix'] = trim( fgets( STDIN ) );
        }
    }

    /**
     * user and group installation
     */
    public function user_and_group()
    {
        $this->_step = 'paths';

        $this->writeLn( '=========================================' );
        $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.3.title' ) );

        $this->writeLn( '' );

        $this->_params['salt']       = md5( uniqid( rand(), true ) );
        $this->_params['saltlength'] = mt_rand( 0, 10 );

        $this->_params['rootuser'] = mt_rand( 100, 1000000000 );
        $this->_params['root']     = mt_rand( 1, 1000000000 );

        // check if a user exist
        $user_table  = $this->_params['db_prefix'] .'users';
        $group_table = $this->_params['db_prefix'] .'groups';
        $perm2group  = $this->_params['db_prefix'] .'permissions2groups';
        $sessions    = $this->_params['db_prefix'] .'sessions';

        // username
        if ( isset( $this->_params['username'] ) &&
             !empty( $this->_params['username'] ) )
        {
            $this->_username = $this->_params['username'];
        }

        if ( isset( $this->_params['password'] ) &&
             !empty( $this->_params['password'] ) )
        {
            $this->_password = $this->_params['password'];
        }

        while ( empty( $this->_username ) )
        {
            $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.3.enter.username' ) );
            $this->_username = trim( fgets( STDIN ) );
        }

        while ( empty( $this->_password ) )
        {
            $this->writeLn( '' );

            $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.3.enter.password' ) );
            $this->_password = trim( fgets( STDIN ) );
        }


        // exist user table ?
        $user_table_exist = count(
            $this->_PDO->query(
                'SHOW TABLES FROM `'. $this->_params['db_database'] .'` LIKE "'. $user_table .'"'
            )->fetchAll()
        );

        if ( $user_table_exist )
        {
            $this->writeLn(
                $this->Locale->get( 'quiqqer/installer', 'step.3.error.user.exist' )
            );

            exit;
        }

        // exist group table ?
        $group_table_exist = count(
            $this->_PDO->query(
                'SHOW TABLES FROM `'. $this->_params['db_database'] .'` LIKE "'. $group_table .'"'
            )->fetchAll()
        );

        if ( $group_table_exist )
        {
            $this->writeLn(
                $this->Locale->get( 'quiqqer/installer', 'step.3.error.group.exist' )
            );

            exit;
        }

        //
        // create the group table
        //
        $create_group_table = '
            CREATE TABLE IF NOT EXISTS `'. $group_table .'` (
              `id` int(11) NOT NULL,
              `name` varchar(50) NOT NULL,
              `admin` tinyint(2) NOT NULL,
              `parent` int(11) NOT NULL,
              `active` tinyint(1) NOT NULL,
              `toolbar` varchar(128),
              PRIMARY KEY (`id`),
              KEY `parent` (`parent`)
            ) CHARACTER SET utf8;
        ';

        $this->_PDO->query( $create_group_table );

        // create root group
        $Statement = $this->_PDO->prepare(
            'INSERT INTO '. $group_table .' (`id`, `name`, `admin`, `active`, `toolbar`)
                VALUES (:id, :gname, :admin, :active, :toolbar)'
        );

        $Statement->execute(array(
            ':id'      => $this->_params['root'],
            ':gname'   => 'root',
            ':admin'   => 1,
            ':active'  => 1,
            ':toolbar' => 'standard.xml'
        ));

        //
        // create the user table
        //
        $create_user_table = '
            CREATE TABLE IF NOT EXISTS `'. $user_table .'` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `username` varchar(50) NOT NULL DEFAULT \'\',
              `password` varchar(50) NOT NULL DEFAULT \'\',
              `usergroup` text NOT NULL,
              `email` varchar(50) DEFAULT NULL,
              `active` int(1) NOT NULL DEFAULT \'0\',
              `regdate` int(11) NOT NULL DEFAULT \'0\',
              `lastvisit` int(11) NOT NULL DEFAULT \'0\',
              `su` tinyint(1) NOT NULL,
              `expire` timestamp NULL DEFAULT \'0000-00-00 00:00:00\',
              `lastedit` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\',
              `user_agent` text NOT NULL,
              PRIMARY KEY (`id`),
              KEY `username` (`username`),
              KEY `password` (`password`)
            ) CHARACTER SET utf8;
        ';

        $this->_PDO->query( $create_user_table );

        // create user
        $Statement = $this->_PDO->prepare(
            'INSERT INTO '. $user_table .' (`id`, `username`, `password`, `usergroup`, `su`, `active`)
                VALUES (:id, :username, :password, :usergroup, :su, :active)'
        );

        // password salted
        $salt = substr( $this->_params['salt'], 0, $this->_params['saltlength'] );
        $pass = $salt . md5( $salt . $this->_password );

        $Statement->execute(array(
            ':username'  => $this->_username,
            ':password'  => $pass,
            ':id'        => $this->_params['rootuser'],
            ':usergroup' => $this->_params['root'],
            ':su'        => 1,
            ':active'    => 1
        ));

        //
        // set permissions to the root group
        //
        $create_group_perm_table = '
            CREATE TABLE IF NOT EXISTS `'. $perm2group .'` (
              `group_id` int(11) NOT NULL,
              `permissions` text
            ) CHARACTER SET utf8;
        ';

        $this->_PDO->query( $create_group_perm_table );

        $permissions = array(
            "quiqqer.admin.users.edit"   => true,
            "quiqqer.admin.groups.edit"  => true,
            "quiqqer.admin.users.view"   => true,
            "quiqqer.admin.groups.view"  => true,
            "quiqqer.system.cache"       => true,
            "quiqqer.system.permissions" => true,
            "quiqqer.system.update"      => true,
            "quiqqer.su"                 => true,
            "quiqqer.admin"              => true,
            "quiqqer.projects.create"    => true
        );

        // create user
        $Statement = $this->_PDO->prepare(
            'INSERT INTO '. $perm2group .' (`group_id`, `permissions`)
                VALUES (:group_id, :permissions)'
        );

        $Statement->execute(array(
            ':group_id'    => $this->_params['root'],
            ':permissions' => json_encode( $permissions )
        ));

//        // create session table
//        $this->_PDO->query(
//            "CREATE TABLE IF NOT EXISTS `{$sessions}` (
//              `session_id` varchar(255) NOT NULL,
//              `session_value` text NOT NULL,
//              `session_time` int(11) NOT NULL,
//              `session_lifetime` int(11) NOT NULL,
//              PRIMARY KEY (`session_id`)
//            ) CHARACTER SET utf8 ENGINE = MEMORY;"
//        );
        // create session table
        $this->_PDO->query(
            "CREATE TABLE IF NOT EXISTS `{$sessions}` (
              `session_id` varchar(255) NOT NULL,
              `session_value` text NOT NULL,
              `session_time` int(11) NOT NULL,
              `session_lifetime` int(11) NOT NULL,
              PRIMARY KEY (`session_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
    }

    /**
     * paths step
     */
    public function paths()
    {
        $this->writeLn( '=========================================' );
        $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.4.title' ) );

        if ( isset( $this->_params['cms'] ) && !empty( $this->_params['cms'] ) &&
             isset( $this->_params['var'] ) && !empty( $this->_params['var'] ) &&
             isset( $this->_params['lib'] ) && !empty( $this->_params['lib'] ) &&
             isset( $this->_params['bin'] ) && !empty( $this->_params['bin'] ) &&
             isset( $this->_params['opt'] ) && !empty( $this->_params['opt'] ) &&
             isset( $this->_params['usr'] ) && !empty( $this->_params['usr'] ) )
        {
            return;
        }

        $this->_step = 'paths';
        $cms_dir     = getcwd() .'/';

        $this->writeLn( '' );
        $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.4.attention' ) );

        $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.4.paths.change' ) );
        $this->writeLn( $cms_dir );
        $this->writeLn( '' );
        $this->write( $this->Locale->get( 'quiqqer/installer', 'step.4.paths.change.a' ) );

        $_edit_paths = trim( fgets( STDIN ) );
        $edit_paths  = false;

        if ( $_edit_paths == $this->Locale->get( 'quiqqer/installer', 'yes' ) ) {
            $edit_paths = true;
        }

        $needles = array(
            'cms' => array(
                'default'  => $cms_dir,
                'question' => $this->Locale->get( 'quiqqer/installer', 'step.4.paths.q1' )
            ),
            'lib' => array(
                'default'  => $cms_dir ."lib",
                'question' => $this->Locale->get( 'quiqqer/installer', 'step.4.paths.q2' )
            ),
            'bin' => array(
                'default'  => $cms_dir ."bin",
                'question' => $this->Locale->get( 'quiqqer/installer', 'step.4.paths.q3' )
            ),
            'usr' => array(
                'default'  => $cms_dir ."usr",
                'question' => $this->Locale->get( 'quiqqer/installer', 'step.4.paths.q4' )
            ),
            'opt' => array(
                'default'  => $cms_dir ."packages",
                'question' => $this->Locale->get( 'quiqqer/installer', 'step.4.paths.q5' )
            ),
            'var' => array(
                'default'  => $cms_dir ."var",
                'question' => $this->Locale->get( 'quiqqer/installer', 'step.4.paths.q6' )
            ),
            'host' => array(
                'default'  => "",
                'question' => $this->Locale->get( 'quiqqer/installer', 'step.4.paths.q7' )
            )
        );

        foreach ( $needles as $needle => $param )
        {
            $this->writeLn( '' );
            $this->writeLn( $param['question'] );
            $this->write( 'Value ['. $param['default'] .'] : '  );

            if ( $edit_paths || $needle == 'host' ) {
                $this->_params[ $needle ] = trim( fgets( STDIN ) );
            }

            $this->writeLn( '' );

            if ( !empty( $this->_params[ $needle ] ) ) {
                continue;
            }

            $this->_params[ $needle ] = $param['default'];
        }
    }

    /**
     * Create QUIQQER
     */
    public function execute()
    {
        $this->writeLn( '' );
        $this->writeLn( '=========================================' );
        $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.5.title' ) );

        $cms_dir = $this->_cleanPath( $this->_params['cms'] );
        $var_dir = $this->_cleanPath( $this->_params['var'] );
        $lib_dir = $this->_cleanPath( $this->_params['lib'] );
        $bin_dir = $this->_cleanPath( $this->_params['bin'] );
        $opt_dir = $this->_cleanPath( $this->_params['opt'] );
        $usr_dir = $this->_cleanPath( $this->_params['usr'] );

        $etc_dir = $cms_dir .'etc/';
        $tmp_dir = $var_dir .'temp/';

        utils\system\File::mkdir( $cms_dir );
        utils\system\File::mkdir( $etc_dir );
        utils\system\File::mkdir( $tmp_dir );
        utils\system\File::mkdir( $opt_dir );
        utils\system\File::mkdir( $usr_dir );

        $url_dir = "/";

        if ( !isset( $this->_params['httpshost'] ) ) {
            $this->_params['httpshost'] = '';
        }

        //
        // create the etc, the global config
        //
        $config = array(
            "globals" => array(
                "cms_dir" => $cms_dir,
                "lib_dir" => $lib_dir,
                "bin_dir" => $bin_dir,
                "var_dir" => $var_dir,
                "usr_dir" => $usr_dir,
                "sys_dir" => $cms_dir ."admin/",
                "opt_dir" => $opt_dir,
                "url_dir" => $url_dir,

                "salt"       => $this->_params['salt'],
                "saltlength" => $this->_params['saltlength'],
                "rootuser"   => $this->_params['rootuser'],
                "root"       => $this->_params['root'],

                "cache"       => 0,
                "host"        => $this->_params['host'],
                "httpshost"   => $this->_params['httpshost'],
                "development" => 1,
                "debug_mode"  => 0,
                "emaillogin"  => 0,
                "maintenance" => 0,

                "mailprotection" => 1
            ),

            "db" => array(
                "driver"   => $this->_params['db_driver'],
                "host"     => $this->_params['db_host'],
                "database" => $this->_params['db_database'],
                "user"     => $this->_params['db_user'],
                "password" => $this->_params['db_password'],
                "prfx"     => $this->_params['db_prefix']
            ),

            "auth" => array(
                "type" => "standard"
            ),

            "template" => array(
                "engine" => "smarty3"
            )
        );

        // needle inis
        mkdir( $etc_dir .'wysiwyg/' );
        mkdir( $etc_dir .'wysiwyg/toolbars/' );

        file_put_contents( $etc_dir .'conf.ini.php', '' );
        file_put_contents( $etc_dir .'plugins.ini.php', '' );
        file_put_contents( $etc_dir .'projects.ini.php', '' );
        file_put_contents( $etc_dir .'source.list.ini.php', '' );
        file_put_contents( $etc_dir .'wysiwyg/editors.ini.php', '' );
        file_put_contents( $etc_dir .'wysiwyg/conf.ini.php', '' );

        $this->_writeIni( $etc_dir .'conf.ini.php', $config );

        $this->_writeIni( $etc_dir .'source.list.ini.php', array(
            'packagist' => array(
                'active' => 1
            ),

            'http://update.quiqqer.com/' => array(
                'active' => 1,
                'type'   => "composer"
            ),

            'http://composer.quiqqer.com/' => array(
                'active' => 1,
                'type'   => "composer"
            )
        ));

        // wyiswyg editor
        $this->_writeIni( $etc_dir .'wysiwyg/conf.ini.php', array(
            'settings' => array(
                'standard' => 'ckeditor4'
            )
        ));

        // standard toolbar
        copy(
            dirname( __FILE__ ) .'/standardToolbar.xml',
            $etc_dir .'wysiwyg/toolbars/standard.xml'
        );




        //
        // create composer file
        //
        $composer_json = file_get_contents( dirname(__FILE__) .'/composer.tpl' );

        $composer_json = str_replace(
            '{$packages_dir}',
            $opt_dir,
            $composer_json
        );

        $composer_json = str_replace(
            '{$composer_cache_dir}',
            $var_dir .'composer/',
            $composer_json
        );

        file_put_contents( $cms_dir .'composer.json', $composer_json );

        // download composer file
        file_put_contents(
            $cms_dir ."composer.phar",
            fopen("https://getcomposer.org/composer.phar", 'r')
        );

        //
        // create the htaccess
        //
        $packageDir = str_replace( $cms_dir, '', $opt_dir );

        $htaccess = '' .
        '# QUIQQER htaccess rules'."\n".
        '<IfModule mod_rewrite.c>'."\n".
            'SetEnv HTTP_MOD_REWRITE On'."\n".
            "\n".
            'RewriteEngine On' ."\n".
            'RewriteBase '. $url_dir ."\n".
            'RewriteCond  %{REQUEST_FILENAME} !^.*bin/' ."\n".
            'RewriteRule ^.*lib/|^.*etc/|^.*var/|^.*'. $packageDir .'|^.*media/sites/ / [L]' ."\n".
            'RewriteRule  ^/(.*)     /$' ."\n".
            'RewriteCond %{REQUEST_FILENAME} !-f' ."\n".
            'RewriteCond %{REQUEST_FILENAME} !-d' ."\n".
            "\n".
            'RewriteRule ^(.*)$ index.php?_url=$1&%{QUERY_STRING}'.
        '</IfModule>';

        if ( file_exists( '.htaccess' ) )
        {
            $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.5.htaccess.exists' ) );
            $this->writeLn( '' );
            $this->writeLn( $htaccess );

        } else
        {
            file_put_contents( '.htaccess', $htaccess );
        }

        if ( !is_dir( $var_dir .'composer/' ) ) {
            mkdir( $var_dir .'composer/' );
        }

        // move composer.phar to composer var
        rename(
            $cms_dir .'composer.phar',
            $var_dir .'composer/composer.phar'
        );

        $this->writeLn( '' );
        $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.5.install.message' ) );
        $this->writeLn( '' );

        // installation
        $exec = 'COMPOSER_HOME="'. $var_dir .'composer/" '.
                'php '. $var_dir .'composer/composer.phar --working-dir="'. $cms_dir .'" install 2>&1';

        system( $exec, $retval );

        if ( strpos( $retval, 'RuntimeException' ) !== false ) {
            exit;
        }

        if ( strpos( $retval, 'RuntimeException' ) !== false ) {
            exit;
        }


        $this->writeLn( '' );
        $this->writeLn( 'Downloading QUIQQER' );

        $exec = 'COMPOSER_HOME="'. $var_dir .'composer/" '.
                'php '. $var_dir .'composer/composer.phar --working-dir="'. $cms_dir .'" '.
                'require "quiqqer/quiqqer:~1" 2>&1';

        system( $exec, $retval );


        // some composer versions have a bug, and dont install packages with require
        $exec = 'php '. $var_dir .'composer/composer.phar --working-dir="'. $cms_dir .'" update 2>&1';
        system( $exec, $retval );


        $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.5.download.successful' ) );

        //
        // execute the main setup from quiqqer
        // so, the tables have the actualy state
        //

        // translation
        system(
            'php '. $cms_dir .'quiqqer.php --username="'. $this->_username .'" '.
                                           '--password="'. $this->_password .'" '.
                                           '--tool="package:translator" '.
                                           '--newLanguage="'. $this->Locale->getCurrent() .'" '.
                                           '--noLogo'
        );

        chdir( $cms_dir );
        system( 'php '. $cms_dir .'quiqqer.php --username="'. $this->_username .'" --password="'. $this->_password .'" --tool="quiqqer:setup" --noLogo' );

        // translation
        system( 'php '. $cms_dir .'quiqqer.php --username="'. $this->_username .'" --password="'. $this->_password .'" --tool="package:translator" --noLogo' );


        $this->writeLn( '' );
        $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.5.cleanup' ) );

        // delete the setup
        if ( file_exists( 'quiqqer.zip' ) ) {
            unlink( 'quiqqer.zip' );
        }

        if ( file_exists( 'quiqqer.setup' ) ) {
            unlink( 'quiqqer.setup' );
        }

        if ( file_exists( 'composer.json' ) ) {
            unlink( 'composer.json' );
        }

        if ( file_exists( 'composer.lock' ) ) {
            unlink( 'composer.lock' );
        }


        // move dirs to temp
        $dirs = array( 'css', 'locale', 'js' );

        foreach ( $dirs as $dir )
        {
            if ( is_dir( $cms_dir . $dir ) )
            {
                rename(
                    $cms_dir . $dir,
                    $var_dir .'temp/'. $dir
                );
            }
        }

        $this->writeLn( '' );
        $this->writeLn( '=========================================' );

        // start quiqqer health
        system( 'php quiqqer.php --username="'. $this->_username .'" --password="'. $this->_password .'" --tool="quiqqer:health" --noLogo' );

        // start quiqqer tests
        system( 'php quiqqer.php --username="'. $this->_username .'" --password="'. $this->_password .'" --tool="quiqqer:tests" --noLogo' );


        $this->writeLn( '' );
        $this->writeLn( '=========================================' );

        // green color
        $this->write( "\033[0;32m" );

        $this->writeLn( '' );
        $this->writeLn( $this->Locale->get( 'quiqqer/installer', 'step.5.successful' ) );

        $this->writeLn("
                           ¶¶¶¶¶¶¶¶¶¶¶¶
                         ¶¶            ¶¶
           ¶¶¶¶¶        ¶¶                ¶¶
           ¶     ¶     ¶¶      ¶¶    ¶¶     ¶¶
            ¶     ¶    ¶¶       ¶¶    ¶¶      ¶¶
             ¶    ¶   ¶¶        ¶¶    ¶¶      ¶¶
              ¶   ¶   ¶                         ¶¶
            ¶¶¶¶¶¶¶¶¶¶¶¶                         ¶¶
           ¶            ¶    ¶¶            ¶¶    ¶¶
          ¶¶            ¶    ¶¶            ¶¶    ¶¶
         ¶¶   ¶¶¶¶¶¶¶¶¶¶¶      ¶¶        ¶¶     ¶¶
         ¶               ¶       ¶¶¶¶¶¶¶       ¶¶
         ¶¶              ¶                    ¶¶
          ¶   ¶¶¶¶¶¶¶¶¶¶¶¶                   ¶¶
          ¶¶           ¶  ¶¶                ¶¶
          ¶¶¶¶¶¶¶¶¶¶¶¶    ¶¶            ¶¶
                            ¶¶¶¶¶¶¶¶¶¶¶

        " );

        $this->write( "\033[0m" );
        $this->writeLn( '' );

        // create the first project
        // system( 'php quiqqer.php --username="'. $this->_username .'" --password="'. $this->_password .'" --tool="quiqqer:create-project"' );
    }

    /**
     * ensures that string is a correct path
     *
     * @param String $path
     * @return String
     */
    protected function _cleanPath($path)
    {
        return rtrim( $path, '/' ) .'/';
    }

    /**
     * Write an ini file
     *
     * @param String $filename - path to file
     * @param Array $options - ini options
     * @throws \Exception
     */
    protected function _writeIni($filename, $options)
    {
        if ( !is_writeable( $filename ) )
        {
            throw new \Exception(
                $this->Locale->get( 'quiqqer/installer', 'config.not.writable' )
            );
        }

        $tmp = '';

        foreach ( $options as $section => $values )
        {
            $tmp .= "[$section]\n";

            foreach ($values as $key => $val)
            {
                if ( is_array( $val ) )
                {
                    foreach ( $val as $k => $v ) {
                        $tmp .= "{$key}[$k] = \"$v\"\n";
                    }
                } else
                {
                    $tmp .= "$key = \"$val\"\n";
                }
            }

            $tmp .= "\n";
        }

        file_put_contents( $filename, $tmp );
    }
}

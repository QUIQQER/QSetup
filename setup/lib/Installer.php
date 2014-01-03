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
     * @var PDO $_PDO
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
     * installer constructor
     *
     * @param array $params - installation params
     */
    public function __construct($params=array())
    {
        $this->_params = $params;
        $this->_step   = 0;
    }

    /**
     * Starts the installer
     */
    public function start()
    {
        // database
        $this->database();

        // create user and group
        $this->user_and_group();

        // set the paths
        $this->paths();

        // create quiqqer - boooya
        $this->_create();
    }

    /**
     * Write an output line
     *
     * @param String $str
     */
    public function writeLn($str)
    {
        echo $str ."\n";
    }

    /**
     * Write an output
     *
     * @param String $str
     */
    public function write($str)
    {
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
     * Steps
     */

    /**
     * Database step
     */
    public function database()
    {
        $this->_step = 'database';

        $this->writeLn( '=========================================' );
        $this->writeLn( 'Step 1 Database connection' );
        $this->writeLn( '' );

        // driver
        $this->write( 'Database Driver (mysql,sqlite) [mysql]: ' );
        $this->_params['db_driver'] = trim( fgets( STDIN ) );

        if ( empty( $this->_params['db_driver'] ) ) {
            $this->_params['db_driver'] = 'mysql';
        }

        // swtch to the right db installer
        switch ( $this->_params['db_driver'] )
        {
            case 'sqlite':
                require_once 'installer/SQLite.php';

                $result = \QUI\Installer\SQLite::database(
                    $this->_params,
                    $this
                );

            break;

            case 'mysql':
                require_once 'installer/DataBase.php';

                $result = \QUI\Installer\DataBase::database(
                    $this->_params,
                    $this
                );

            break;
        }

        $this->_PDO    = $result['PDO'];
        $this->_params = array_merge( $this->_params, $result['params'] );

        // database prefix
        $this->writeLn( "Want you a prefix for your database tables? if no, leave it empty:" );
        $this->_params['db_prefix'] = trim( fgets( STDIN ) );
    }

    /**
     * user and group installation
     */
    public function user_and_group()
    {
        $this->_step = 'paths';

        $this->writeLn( '=========================================' );
        $this->writeLn( 'Step 2 set a root / administrator user for QUIQQER' );

        $this->writeLn( '' );

        $this->_params['salt']       = md5( uniqid( rand(), true ) );
        $this->_params['saltlength'] = mt_rand( 0, 10 );

        $this->_params['rootuser'] =  mt_rand( 100, 1000000000 );
        $this->_params['root']     = mt_rand( 1, 1000000000 );

        // check if a user exist
        $user_table  = $this->_params['db_prefix'] .'users';
        $group_table = $this->_params['db_prefix'] .'groups';
        $perm2group  = $this->_params['db_prefix'] .'permissions2groups';

        // database prefix
        while ( empty( $this->_username ) )
        {
            $this->writeLn( "Please enter a username:" );
            $this->_username = trim( fgets( STDIN ) );
        }

        $this->writeLn( '' );

        while ( empty( $this->_password ) )
        {
            $this->writeLn( "Please enter a password:" );
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
                'The user table already exist. You cannot install QUIQQER.'
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
                'The group table already exist. You cannot install QUIQQER.'
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
              PRIMARY KEY (`id`),
              KEY `parent` (`parent`)
            ) CHARACTER SET utf8;
        ';

        $this->_PDO->query( $create_group_table );

        // create root group
        $Statement = $this->_PDO->prepare(
            'INSERT INTO '. $group_table .' (`id`, `name`, `admin`, `active`)
                VALUES (:id, :gname, :admin, :active)'
        );

        $Statement->execute(array(
            ':id'     => $this->_params['root'],
            ':gname'  => 'root',
            ':admin'  => 1,
            ':active' => 1
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
            );
        ';

        $this->_PDO->query( $create_group_perm_table );

        $permissions = array(
            "quiqqer.admin.users.edit"   => true,
            "quiqqer.admin.groups.edit"  => true,
            "quiqqer.admin.users.view"   => true,
            "quiqqer.admin.groups.view"  => true,
            "quiqqer.admin.projects.create" => true,
            "quiqqer.admin.users.edit"      => true,
            "quiqqer.admin.users.view"      => true,
            "quiqqer.system.cache"       => true,
            "quiqqer.system.permissions" => true,
            "quiqqer.system.update"      => true,
            "quiqqer.su"    => true,
            "quiqqer.admin" => true
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
    }

    /**
     * paths step
     */
    public function paths()
    {
        $this->_step = 'paths';
        $cms_dir     = getcwd() .'/';

        $this->writeLn( '=========================================' );
        $this->writeLn( 'Step 3 set the installation paths and the host of QUIQQER' );

        $this->writeLn( '' );
        $this->writeLn( 'If you not know what you do, please use the default settings.' );

        $this->writeLn( 'Do you want to change the following installation path of quiqqer? ' );
        $this->writeLn( $cms_dir );
        $this->writeLn( '' );
        $this->write( '[NO/yes]: ' );

        $_edit_paths = trim( fgets( STDIN ) );
        $edit_paths  = false;

        if ( $_edit_paths == 'yes' ) {
            $edit_paths = true;
        }

        $needles = array(
            'cms' => array(
                'default'  => $cms_dir,
                'question' => "Please enter the cms-dir - The main directory contains the whole QUIQQER system."
            ),
            'lib' => array(
                'default'  => $cms_dir ."lib",
                'question' => "Please enter the lib-dir - The lib directory contains all the quiqqer libraries."
            ),
            'bin' => array(
                'default'  => $cms_dir ."bin",
                'question' => "Please enter the bin-dir - The bin directory contains all the files you need to be accessible from the Web-Server."
            ),
            'usr' => array(
                'default'  => $cms_dir ."usr",
                'question' => "Please enter the usr-dir - The usr directory contains all the project templates."
            ),
            'opt' => array(
                'default'  => $cms_dir ."packages",
                'question' => "Please enter the opt-dir - The opt directory contains all plugins and packages. Its the vendor vendor-dir for composer."
            ),
            'var' => array(
                'default'  => $cms_dir ."var",
                'question' => "Please enter the var-dir - The var directory contains all temp files, like the cache, temporary uploads, logs and many more."
            ),
            'host' => array(
                'default'  => "",
                'question' => "Please enter the host - Under which url / domain is quiqqer accessed? (eq: http://www.my-domain.de)"
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
    protected function _create()
    {
        $cms_dir = $this->_cleanPath( $this->_params['cms'] );
        $var_dir = $this->_cleanPath( $this->_params['var'] );
        $lib_dir = $this->_cleanPath( $this->_params['lib'] );
        $bin_dir = $this->_cleanPath( $this->_params['bin'] );
        $opt_dir = $this->_cleanPath( $this->_params['opt'] );
        $usr_dir = $this->_cleanPath( $this->_params['usr'] );

        $etc_dir = $cms_dir .'etc/';
        $tmp_dir = $var_dir .'temp/';

        \QUI\utils\system\File::mkdir( $cms_dir );
        \QUI\utils\system\File::mkdir( $etc_dir );
        \QUI\utils\system\File::mkdir( $tmp_dir );
        \QUI\utils\system\File::mkdir( $opt_dir );
        \QUI\utils\system\File::mkdir( $usr_dir );

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
        file_put_contents( $etc_dir .'conf.ini', '' );
        file_put_contents( $etc_dir .'plugins.ini', '' );
        file_put_contents( $etc_dir .'projects.ini', '' );
        file_put_contents( $etc_dir .'source.list.ini', '' );

        $this->_writeIni( $etc_dir .'conf.ini', $config );

        $this->_writeIni( $etc_dir .'source.list.ini', array(
            'packagist' => array(
                'active' => 1
            ),

            'http://update.quiqqer.com/' => array(
                'active' => 1,
                'type'   => "composer"
            )
        ));

        //
        // create composer file
        //
        $composer_json = file_get_contents( 'lib/composer.tpl' );

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
        system( 'curl -sS https://getcomposer.org/installer | php -- --install-dir='. $cms_dir );
        system( 'php composer.phar install' );

        //
        // create the htaccess
        //
        $htaccess = '' .
        'RewriteEngine On' ."\n".
        'RewriteBase '. $url_dir ."\n".
        'RewriteCond  %{REQUEST_FILENAME} !^.*bin/' ."\n".
        'RewriteRule ^.*lib/|^.*etc/|^.*var/|^.*opt/|^.*admin/index.php|^.*media/sites/ / [L]' ."\n".
        'RewriteRule  ^/(.*)     /$' ."\n".
        'RewriteCond %{REQUEST_FILENAME} !-f' ."\n".
        'RewriteCond %{REQUEST_FILENAME} !-d' ."\n".
        "\n".
        'RewriteRule ^(.*)$ index.php?_url=$1&%{QUERY_STRING}';

        if ( file_exists( '.htaccess' ) )
        {
            $this->writeLn( 'A .htaccess file already exist. Please at the following to the htacess file:' );
            $this->writeLn( '' );
            $this->writeLn( $htaccess );

        } else
        {
            file_put_contents( '.htaccess', $htaccess );
        }

        // move composer.phar to composer var
        rename(
            $cms_dir .'composer.phar',
            $var_dir .'composer/composer.phar'
        );
        //
        // execute the main setup from quiqqer
        // so, the tables have the actualy state
        //
        chdir( $cms_dir );
        system( 'php quiqqer.php --username="'. $this->_username .'" --password="'. $this->_password .'" --tool="quiqqer:setup"' );

        // delete the setup
        if ( file_exists( 'quiqqer.zip' ) ) {
            unlink( 'quiqqer.zip' );
        }

        if ( file_exists( 'composer.json' ) ) {
            unlink( 'composer.json' );
        }

        if ( file_exists( 'composer.lock' ) ) {
            unlink( 'composer.lock' );
        }


        // move dirs to temp
        $dirs = array( 'css' );

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
     */
    protected function _writeIni($filename, $options)
    {
        if ( !is_writeable( $filename ) ) {
            throw new \Exception( 'Config is not writable' );
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

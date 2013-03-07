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

        $needles = array(
            'db_driver' => array(
            	'default'  => "mysql",
                'question' => "Database Driver:"
            ),
            'db_host' => array(
            	'default'  => "localhost",
                'question' => "Database Host:"
            ),
            'db_database' => array(
            	'default'  => "",
                'question' => "Database:"
            ),
            'db_user' => array(
            	'default'  => "",
                'question' => "Database user:"
            ),
            'db_password' => array(
            	'default'  => "",
                'question' => "Database password:"
            )
        );

        foreach ( $needles as $needle => $param )
        {
            if ( isset( $this->_params[ $needle ] ) &&
                 !empty( $this->_params[ $needle ] ) )
            {
                continue;
            }

            $this->write( $param['question'] );

            if ( !empty( $param['default'] )) {
                $this->write( ' ['. $param['default'] .']' );
            }

            $this->write( ' ' );

            $this->_params[ $needle ] = trim( fgets( STDIN ) );


            if ( !empty( $this->_params[ $needle ] ) ) {
                continue;
            }

            if ( !empty( $param['default'] )) {
                $this->_params[ $needle ] = $param['default'];
            }
        }

        // check database connection
        try
        {
            $dsn = $this->_params['db_driver'] .
                   ':dbname='. $this->_params['db_database'] .
                   ';host='. $this->_params['db_host'];

            $PDO = new \PDO(
                $dsn,
                $this->_params['db_user'],
                $this->_params['db_password'],
                array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );

            $PDO->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

        } catch ( \PDOException $Exception )
        {
            $this->_params['db_driver']   = '';
            $this->_params['db_host']     = '';
            $this->_params['db_database'] = '';
            $this->_params['db_user']     = '';
            $this->_params['db_password'] = '';

            $this->writeLn( $Exception->getMessage() );

            $this->database();
        }
    }

    /**
     * user and group installation
     */
    public function user_and_group()
    {
        $this->_step = 'paths';

        $this->writeLn( '=========================================' );
        $this->writeLn( 'Step 2 set a user for QUIQQER' );

        $this->writeLn( '' );

        // @todo generate salt
        // @todo genrate salt length



    }

    /**
     * paths step
     */
    public function paths()
    {
        $this->_step = 'paths';

        $this->writeLn( '=========================================' );
        $this->writeLn( 'Step 3 set the installation paths and the host of QUIQQER' );

        $this->writeLn( '' );
        $this->writeLn( 'If you not know what you do, please use the default settings.' );

        $this->writeLn( 'Do you want to change the paths? [NO/yes] ' );
        $_edit_paths = trim( fgets( STDIN ) );

        $edit_paths = false;
        $cms_dir    = getcwd() .'/';

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

        // create the etc
        $config = array(
            "globals" => array(
                "cms_dir" => $cms_dir,
                "lib_dir" => $lib_dir,
                "bin_dir" => $bin_dir,
                "var_dir" => $var_dir,
                "usr_dir" => $usr_dir,
                "sys_dir" => $cms_dir ."admin/",
                "opt_dir" => $opt_dir,
                "url_dir" => "/",

                "salt"       => "",
                "saltlength" => "",
                "rootuser"   => "",
                "root"       => "",

                "cache"       => 0,
                "host"        => "http://hen",
                "httpshost"   => "",
                "development" => 0,
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
                "password" => $this->_params['db_password']
			),

            "auth" => array(
                "type" => "standard"
            )
        );

        file_put_contents( $etc_dir .'conf.ini', '' );
        $this->_writeIni( $etc_dir .'conf.ini', $config );

        // create composer file
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

?>
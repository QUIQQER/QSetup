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
 * @author  www.pcsg.de (Henning Leutz)
 * @author  www.pcsg.de (Patrick Müller)
 * @package com.pcsg.qui
 */

class Installer
{
    // setup file template with essential packages and repositories
    static public $setupData
        = array(
            'lang'         => '',
            'langs'        => array(),
            'database'     => array(
                'driver'   => '',
                'database' => '',
                'host'     => '',
                'username' => '',
                'password' => '',
                'prefix'   => ''
            ),
            'users'        => array(),
            'projects'     => array(),
            'host'         => '',
            'paths'        => array(
                'url'      => '',
                'cms'      => '',
                'packages' => '',
                'usr'      => '',
                'var'      => ''
            ),
            'packages'     => array(
                'php'                          => '>=5.3.2',
                'composer/composer'            => '1.0.0-alpha10',
                'robloach/component-installer' => '0.0.12',
                'quiqqer/utils'                => 'dev-dev',
                'tedivm/stash'                 => '0.11.6',
                'symfony/http-foundation'      => '2.6.4',
                'symfony/console'              => '2.5'
            ),
            'repositories' => array(
                array(
                    'packagist' => false
                ),
                array(
                    'type' => 'composer',
                    'url'  => 'http://update.quiqqer.com'
                ),
                array(
                    'type' => 'composer',
                    'url'  => 'http://composer.quiqqer.com'
                )
            )
        );

    /**
     * config params from the user
     *
     * @var array $_params
     */
    protected $_params;

    /**
     * internal database result
     *
     * @var array $_db_result
     */
    protected $_db_result;

    /**
     * the database object
     *
     * @var \PDO $_PDO
     */
    protected $_PDO = null;

    /**
     * the cms user
     *
     * @var String $_username
     */
    protected $_username = '';

    /**
     * the password for the cms user
     *
     * @var String $_password
     */
    protected $_password = '';

    /**
     * no output flag, if it is true, than no output
     *
     * @var Bool
     */
    protected $_no_output = false;

    /**
     * currently available QUIQQER versions
     *
     * @var array
     */
    protected $_versions = array();

    /**
     * setup data from setup file
     *
     * @var array
     */
    protected $_setup = array();

    protected $_setupProcess
        = array(
            'tables'  => false,
            'folders' => false,
        );

    public $Locale;

    /**
     * installer constructor
     *
     * @param string $setupFile (optional) - Setup-File (quiqqer.setup)
     */
    public function __construct($setupFile = null)
    {
        require_once dirname(__FILE__).'/classes/Locale.php';

        $this->Locale = new Locale();

        if (!is_null($setupFile) && file_exists($setupFile)) {
            $fileData = json_decode(file_get_contents($setupFile), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->writeLn(' ');
                $this->writeLn(
                    $this->Locale->get('quiqqer/installer', 'json.error')
                );

                $this->_setup = $this::$setupData;
            } else {
                $this->_setup = $this->_checkSetupFile($fileData);
            }
        } else {
            $this->_setup = $this::$setupData;
        }

        $this->_step = 0;
    }

    /**
     * Starts the installer
     */
    public function start()
    {
        // set the paths
        $this->language();

        // set the version
        $this->version();

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
        if ($this->_no_output) {
            return;
        }

        echo $str."\n";
    }

    /**
     * Write an output
     *
     * @param String $str
     */
    public function write($str)
    {
        if ($this->_no_output) {
            return;
        }

        echo $str;
    }

    /**
     * Is installer on step §step?
     *
     * @param String $step
     *
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
        $this->writeLn('=========================================');
        $this->writeLn('Step 1 Language of QUIQQER');
        $this->writeLn('');

        $this->writeLn("Which language do you want to use? (de=German,en=English) [en]: ");

        if (!isset($this->_setup['lang']) || empty($this->_setup['lang'])) {
            $lang = trim(fgets(STDIN));
        } else {
            $lang = $this->_setup['lang'];
        }

        switch ($lang) {
            case 'en':
            case 'de':
                include 'locale/'.$lang.'.php';

                $this->Locale->setCurrent($lang);
                break;

            default:
                $this->writeLn("Language not found ... ");
                $this->language();
                break;
        }

        $this->_setup['lang'] = $lang;
    }

    /**
     * Version step
     */
    public function version()
    {
        $versions = Utils\System\File::readDir(
            dirname(dirname(__FILE__)).'/versions/'
        );

        sort($versions);

        $this->_versions = $versions;

        $this->writeLn('=========================================');
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'step.version.title')
        );
        $this->writeLn('');
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'step.version.list')
        );
        $this->writeLn(implode(', ', $versions));
        $this->writeLn('');
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'step.version.choice').
            " [".current($versions)."] :"
        );

        if (!isset($this->_setup['packages']['quiqqer/quiqqer'])) {
            $this->_setup['packages']['quiqqer/quiqqer'] = trim(fgets(STDIN));
        }

        if (empty($this->_setup['packages']['quiqqer/quiqqer'])
            || !in_array($this->_setup['packages']['quiqqer/quiqqer'],
                $versions)
        ) {
            $this->_setup['packages']['quiqqer/quiqqer'] = current($versions);
        }

        $this->_params['version']
            = $this->_setup['packages']['quiqqer/quiqqer'];
    }

    /**
     * Database step
     */
    public function database()
    {
        $db = $this->_setup['database'];
        $db['driver'] = 'mysql';

        $this->_step = 'database';

        $this->writeLn('=========================================');
        $this->writeLn($this->Locale->get('quiqqer/installer', 'step.2.title'));
        $this->writeLn('');

        // driver
//        $this->writeLn(
//            $this->Locale->get('quiqqer/installer', 'step.2.db.driver')
//        );
//
//        if (!isset($db['driver'])) {
//            $db['driver'] = trim(fgets(STDIN));
//        }
//
//        if (empty($db['driver'])) {
//            $db['driver'] = 'mysql';
//        }

        // create new or use existent
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'step.2.db.create.new')
        );

        if (!isset($db['database']) || empty($db['database'])) {

            $createNewInput = trim(fgets(STDIN));

            $this->_params['db_new'] = false;

            if ($createNewInput == $this->Locale->get('quiqqer/installer',
                    'no')
            ) {
                $this->_params['db_new'] = true;
            }
        }

        $needles = array(
            'host'     => array(
                'default'  => "localhost",
                'question' => "Database host:"
            ),
            'username' => array(
                'default'  => "",
                'question' => "Database user:"
            ),
            'password' => array(
                'default'  => "",
                'question' => "Database password:"
            )
        );

        foreach ($needles as $needle => $param) {

            if (isset($db[$needle]) && !empty($db[$needle])) {
                continue;
            }

            $this->write($param['question']);

            if (!empty($param['default'])) {
                $this->write(' ['.$param['default'].']');
            }

            $this->write(' ');

            $db[$needle] = trim(fgets(STDIN));


            if (!empty($db[$needle])) {
                continue;
            }

            if (!empty($param['default'])) {
                $db[$needle] = $param['default'];
            }
        }

        // db name
        if (isset($this->_params['db_new'])) {

            if ($this->_params['db_new']) {
                $this->writeLn(
                    $this->Locale->get('quiqqer/installer', 'step.2.db.new')
                );

            } else {
                $this->writeLn(
                    $this->Locale->get('quiqqer/installer', 'step.2.db.old')
                );
            }

            $db['database'] = trim(fgets(STDIN));
        }

        if (empty($db['database'])) {
            $db['database'] = 'quiqqer';
        }

        // switch to the right db installer
        try {

            $this->_db_result = installer\DataBase::database($db, $this);

        } catch (Exception $Exception) {

            $this->writeLn('');
            $this->writeLn(
                $this->Locale->get('quiqqer/installer', 'database.error', array(
                    'error' => $Exception->getMessage()
                ))
            );
            $this->writeLn('');

            $this->database();
        }

        $this->writeLn('');
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'database.success')
        );

        $this->writeLn('');


        $this->_PDO = $this->_db_result['PDO'];
        $databaseParams = $this->_db_result['params'];

        // database prefix
        if (!isset($databaseParams['prefix'])) {

            $this->writeLn(
                $this->Locale->get('quiqqer/installer', 'step.2.db.prefix')
            );

            $databaseParams['prefix'] = trim(fgets(STDIN));
        }

        if (empty($databaseParams['prefix'])) {
            $databaseParams['prefix'] = '';
        }

        $this->_setup['database'] = $databaseParams;
    }

    /**
     * user and group installation
     */
    public function user_and_group()
    {
        $this->_step = 'paths';

        $this->writeLn('=========================================');
        $this->writeLn($this->Locale->get('quiqqer/installer', 'step.3.title'));

        $this->writeLn('');

        $this->_params['salt'] = md5(uniqid(rand(), true));
        $this->_params['saltlength'] = mt_rand(10, 20);

        $this->_params['root'] = mt_rand(10, 1000000000);

        // check if a user exist
        $user_table = $this->_setup['database']['prefix'].'users';
        $group_table = $this->_setup['database']['prefix'].'groups';
        $perm2group = $this->_setup['database']['prefix'].'permissions2groups';

        $username = '';
        $password = '';

        // admin user
        if (empty($this->_setup['users'])) {

            while (empty($username)) {
                $this->writeLn(
                    $this->Locale->get('quiqqer/installer',
                        'step.3.enter.username')
                );
                $username = trim(fgets(STDIN));
            }

            while (empty($password)) {
                $this->writeLn(
                    $this->Locale->get('quiqqer/installer',
                        'step.3.enter.password')
                );
                $password = trim(fgets(STDIN));
            }

            $this->_setup['users'][] = array(
                'name'      => $username,
                'password'  => $password,
                'superuser' => true
            );
        }

        $DB = installer\DataBase::getDatabase($this->_setup['database']);
        $ver = $this->_params['version'];
        $dbXML = dirname(dirname(__FILE__)).'/versions/'.$ver.'/database.xml';

        if (!file_exists($dbXML)) {

            $this->writeLn(
                $this->Locale->get(
                    'quiqqer/installer',
                    'step.3.error.dbxml.not.found'
                )
            );

            // Switch to master if version-specific database.xml not found
            $dbXML = dirname(dirname(__FILE__)).'/versions/master/database.xml';

            if (!file_exists($dbXML)) {

                $this->writeLn(
                    $this->Locale->get(
                        'quiqqer/installer',
                        'step.3.error.dbxml.not.exist'
                    )
                );

                exit;
            }
        }

        try {
            // create all tables
            installer\DataBase::importTables(
                $this->_setup['database'],
                Utils\XML::getDataBaseFromXml($dbXML)
            );

            // create root group
            $DB->insert(
                $group_table,
                array(
                    'id'      => $this->_params['root'],
                    'name'    => 'Administrator',
                    'admin'   => 1,
                    'active'  => 1,
                    'toolbar' => 'standard.xml'
                )
            );

            // create users
            $salt = substr($this->_params['salt'], 0,
                $this->_params['saltlength']);

            foreach ($this->_setup['users'] as $k => $user) {
                $id = mt_rand(100, 1000000000);
                $pass = $salt.md5($salt.$user['password']);
                $su = 0;

                if (isset($user['superuser']) && $user['superuser']) {
                    $su = 1;
                }

                // set first user als "root" user (just for safety, may be deprecated)
                if ($k === 0) {
                    $this->_username = $user['name'];
                    $this->_password = $user['password'];
                    $this->_params['rootuser'] = $id;
                }

                $DB->insert(
                    $user_table,
                    array(
                        'username'  => $user['name'],
                        'password'  => $pass,
                        'id'        => $id,
                        'usergroup' => $this->_params['root'],
                        'su'        => $su,
                        'active'    => 1,
                        'lang'      => $this->Locale->getCurrent() == 'de' ? 'de' : 'en'
                    )
                );
            }

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

            // create permissions
            $DB->insert(
                $perm2group,
                array(
                    'group_id'    => $this->_params['root'],
                    'permissions' => json_encode($permissions)
                )
            );

        } catch (Exception $Exception) {
            $this->_exitError(
                $this->Locale->get(
                    'quiqqer/installer',
                    'users.error',
                    array('error', $Exception->getMessage())
                )
            );
        }

        $this->writeLn('');
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'users.success')
        );

        $this->_setupProcess['tables'] = true;
    }

    /**
     * paths step (and host)
     */
    public function paths()
    {
        $this->writeLn('=========================================');
        $this->writeLn($this->Locale->get('quiqqer/installer', 'step.4.title'));

        if (!isset($this->_setup['host']) || empty($this->_setup['host'])) {

            $this->writeLn(
                $this->Locale->get('quiqqer/installer', 'step.4.paths.q7')
            );

            $this->write(" [localhost]:");

            $host = trim(fgets(STDIN));

            $this->writeLn('');

            if (empty($host)) {
                $host = dirname(dirname(__FILE__));
            }

            $this->_setup['host'] = $host;
        }

        if (isset($this->_setup['paths'])) {
            $p = $this->_setup['paths'];

            if (isset($p['url'])
                && !empty($p['url'])
                && isset($p['cms'])
                && !empty($p['cms'])
                && isset($p['var'])
                && !empty($p['var'])
                && isset($p['usr'])
                && !empty($p['usr'])
                && isset($p['packages'])
                && !empty($p['packages'])
            ) {
                return;
            }
        }

        $this->_step = 'paths';
        $cms_dir = getcwd().'/';

        $this->writeLn('');
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'step.4.attention')
        );
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'step.4.paths.change')
        );
        $this->writeLn($cms_dir);
        $this->writeLn('');
        $this->write(
            $this->Locale->get('quiqqer/installer', 'step.4.paths.change.a')
        );

        $edit_paths = false;

        if (!isset($this->_params['skip_setpath'])
            || !$this->_params['skip_setpath']
        ) {
            $_edit_paths = trim(fgets(STDIN));

            if ($_edit_paths == $this->Locale->get('quiqqer/installer', 'yes')
            ) {
                $edit_paths = true;
            }
        }

        $needles = array(
            'url'      => array(
                'default'  => "/",
                'question' => $this->Locale->get('quiqqer/installer',
                    'step.4.paths.q0')
            ),
            'cms'      => array(
                'default'  => $cms_dir,
                'question' => $this->Locale->get('quiqqer/installer',
                    'step.4.paths.q1')
            ),
            'usr'      => array(
                'default'  => $cms_dir."usr",
                'question' => $this->Locale->get('quiqqer/installer',
                    'step.4.paths.q4')
            ),
            'packages' => array(
                'default'  => $cms_dir."packages",
                'question' => $this->Locale->get('quiqqer/installer',
                    'step.4.paths.q5')
            ),
            'var'      => array(
                'default'  => $cms_dir."var",
                'question' => $this->Locale->get('quiqqer/installer',
                    'step.4.paths.q6')
            )
        );

        foreach ($needles as $needle => $param) {
            $this->writeLn('');
            $this->writeLn($param['question']);
            $this->write('Value ['.$param['default'].'] : ');

            if ($edit_paths) {
                $this->_setup['paths'][$needle] = trim(fgets(STDIN));
            }

            $this->writeLn('');

            if (!empty($this->_setup['paths'][$needle])) {
                continue;
            }

            $this->_setup['paths'][$needle] = $param['default'];
        }
    }

    /**
     * Create QUIQQER
     */
    public function execute()
    {
        $this->writeLn('');
        $this->writeLn('=========================================');
        $this->writeLn($this->Locale->get('quiqqer/installer', 'step.5.title'));

        $p = $this->_setup['paths'];

        $cms_dir = $this->_cleanPath($p['cms']);
        $var_dir = $this->_cleanPath($p['var']);
        $opt_dir = $this->_cleanPath($p['packages']);
        $usr_dir = $this->_cleanPath($p['usr']);
        $url_dir = $this->_cleanPath($p['url']);

        if (empty($url_dir)) {
            $url_dir = '/';
        }

        $etc_dir = $cms_dir.'etc/';
        $tmp_dir = $var_dir.'temp/';

        if (!Utils\System\File::mkdir($cms_dir)
            || !Utils\System\File::mkdir($etc_dir)
            || !Utils\System\File::mkdir($tmp_dir)
            || !Utils\System\File::mkdir($opt_dir)
            || !Utils\System\File::mkdir($usr_dir)
            || !Utils\System\File::mkdir($var_dir)
            || !Utils\System\File::mkdir($var_dir.'composer/')
        ) {
            $this->_exitError(
                $this->Locale->get('quiqqer/installer', 'paths.error')
            );
        }

        $this->writeLn('');
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'paths.success')
        );

        if (!isset($this->_params['httpshost'])) {
            $this->_params['httpshost'] = '';
        }

        $db = $this->_setup['database'];

        //
        // create the etc, the global config
        //
        $config = array(
            "globals"  => array(
                "cms_dir"        => $cms_dir,
                "var_dir"        => $var_dir,
                "usr_dir"        => $usr_dir,
                "opt_dir"        => $opt_dir,
                "url_dir"        => $url_dir,
                "url_lib_dir"    => $url_dir .'lib/',
                "url_bin_dir"    => $url_dir .'bin/',
                "url_sys_dir"    => $url_dir .'admin/',
                "salt"           => $this->_params['salt'],
                "saltlength"     => $this->_params['saltlength'],
                "rootuser"       => $this->_params['rootuser'],
                "root"           => $this->_params['root'],
                "cache"          => 0,
                "host"           => $this->_setup['host'],
                "httpshost"      => $this->_params['httpshost'],
                "development"    => 1,
                "debug_mode"     => 0,
                "emaillogin"     => 0,
                "maintenance"    => 0,
                "mailprotection" => 1
            ),
            "db"       => array(
                "driver"   => $db['driver'],
                "host"     => $db['host'],
                "database" => $db['database'],
                "user"     => $db['username'],
                "password" => $db['password'],
                "prfx"     => $db['prefix']
            ),
            "auth"     => array(
                "type" => "standard"
            ),
            "template" => array(
                "engine" => "smarty3"
            )
        );

        // needle inis
        if (!mkdir($etc_dir.'wysiwyg/')) {
            $this->_exitError(
                $this->Locale->get('quiqqer/installer', 'paths.error')
            );
        };

        if (!mkdir($etc_dir.'wysiwyg/toolbars/')) {
            $this->_exitError(
                $this->Locale->get('quiqqer/installer', 'paths.error')
            );
        };

        if (file_put_contents($etc_dir.'conf.ini.php', '') === false
            || file_put_contents($etc_dir.'plugins.ini.php', '') === false
            || file_put_contents($etc_dir.'projects.ini.php', '') === false
            || file_put_contents($etc_dir.'source.list.ini.php', '') === false
            ||
            file_put_contents($etc_dir.'wysiwyg/editors.ini.php', '') === false
            || file_put_contents($etc_dir.'wysiwyg/conf.ini.php', '') === false
        ) {
            $this->_exitError(
                $this->Locale->get(
                    'quiqqer/installer', 'inifiles.error'
                )
            );
        }

        try {
            $this->_writeIni($etc_dir.'conf.ini.php', $config);

            $this->_writeIni($etc_dir.'source.list.ini.php', array(
                'packagist'                    => array(
                    'active' => 1
                ),
                'https://update.quiqqer.com/'   => array(
                    'active' => 1,
                    'type'   => "composer"
                ),
                'https://composer.quiqqer.com/' => array(
                    'active' => 1,
                    'type'   => "composer"
                )
            ));

            // wyiswyg editor
            $this->_writeIni($etc_dir.'wysiwyg/conf.ini.php', array(
                'settings' => array(
                    'standard' => 'ckeditor4'
                )
            ));

        } catch (Exception $Exception) {

            $this->_exitError(
                $this->Locale->get('quiqqer/installer', 'inifiles.error')
            );
        }

        // standard toolbar
        copy(
            dirname(__FILE__).'/standardToolbar.xml',
            $etc_dir.'wysiwyg/toolbars/standard.xml'
        );

        //
        // create composer file
        //
        $composer_json = file_get_contents(dirname(__FILE__).'/composer.tpl');

        $composer = json_decode($composer_json, true);

        // set composer paths
        $composer['config']['vendor-dir'] = $opt_dir;
        $composer['config']['cache-dir'] = $var_dir.'composer/';
        $composer['config']['component-dir'] = $opt_dir.'bin/';
        $composer['config']['quiqqer-dir'] = $cms_dir;

        // set composer repositories
        $composer['repositories'] = $this->_setup['repositories'];

        // set composer packages
        $quiqqerPackage = $this->_setup['packages']['quiqqer/quiqqer'];
        unset($this->_setup['packages']['quiqqer/quiqqer']);

        $composer['require'] = $this->_setup['packages'];

        if (file_put_contents($cms_dir.'composer.json', json_encode($composer))
            === false
        ) {
            $this->_exitError(
                $this->Locale->get('quiqqer/installer', 'composer.json.error')
            );
        };

        // download composer file
        file_put_contents(
            $cms_dir."composer.phar",
            fopen("https://getcomposer.org/composer.phar", 'r')
        );

        if (!file_exists($cms_dir."composer.phar")) {
            $this->_exitError(
                $this->Locale->get('quiqqer/installer', 'composer.phar.error')
            );
        }

        //
        // create the htaccess
        //
//        $packageDir = str_replace($cms_dir, '', $opt_dir);

//        $htaccess = ''.
//            '# QUIQQER htaccess rules'."\n".
//            '<IfModule mod_rewrite.c>'."\n".
//            'SetEnv HTTP_MOD_REWRITE On'."\n".
//            "\n".
//            'RewriteEngine On'."\n".
//            'RewriteBase '.$url_dir."\n".
//            'RewriteCond  %{REQUEST_FILENAME} !^.*bin/'."\n".
//            'RewriteRule ^.*lib/|^.*etc/|^.*var/|^.*'.$packageDir
//            .'|^.*media/sites/ / [L]'."\n".
//            'RewriteRule  ^/(.*)     /$'."\n".
//            'RewriteCond %{REQUEST_FILENAME} !-f'."\n".
//            'RewriteCond %{REQUEST_FILENAME} !-d'."\n".
//            "\n".
//            'RewriteRule ^(.*)$ index.php?_url=$1&%{QUERY_STRING}'.
//            '</IfModule>';
//
//        if (file_exists('.htaccess')) {
//            $this->writeLn(
//                $this->Locale->get('quiqqer/installer',
//                    'step.5.htaccess.exists')
//            );
//            $this->writeLn('');
//            $this->writeLn($htaccess);
//
//        } else {
//            file_put_contents($cms_dir.'.htaccess', $htaccess);
//        }

        // move composer.phar to composer var
        rename(
            $cms_dir.'composer.phar',
            $var_dir.'composer/composer.phar'
        );

        if (!file_exists($var_dir.'composer/composer.phar')) {
            $this->_exitError(
                $this->Locale->get('quiqqer/installer', 'composer.phar.error')
            );
        }

        $this->writeLn('');
        $this->writeLn(
            $this->Locale->get(
                'quiqqer/installer',
                'step.5.install.message'
            )
        );
        $this->writeLn('');

        // lock server
        $Lockserver = new LOCKClient(array(
            'composerJsonFile' => $cms_dir.'composer.json'
        ));

        try {
            // generate composer.lock via lock server
            // faster installation
            file_put_contents(
                $cms_dir.'composer.lock',
                $Lockserver->getComposerLock()
            );

        } catch (Exception $Exception) {

            $this->writeLn('=============================');
            $this->writeLn('Lock Server Error');
            $this->writeLn($Exception->getMessage());
            $this->writeLn('=============================');
        }


        // installation
        $exec = 'COMPOSER_HOME="'.$var_dir.'composer/" '.
            'php '.$var_dir.'composer/composer.phar --working-dir="'.$cms_dir
            .'" --prefer-dist install 2>&1';

        system($exec, $retval);

        if (strpos($retval, 'RuntimeException') !== false) {
            exit;
        }

        $this->writeLn('');
        $this->writeLn('QUIQQER Download');

        $v = $quiqqerPackage;

        switch ($v) {
            case 'dev':
            case 'master':
                $v = 'dev-'.$v;
                break;
        }

        try {
            // add quiqqer to the composer.json
            $composer['require']['quiqqer/quiqqer'] = $v;

            file_put_contents($cms_dir.'composer.json', json_encode($composer));

            $Lockserver->setAttribute(
                'composerJsonFile',
                $cms_dir.'composer.json'
            );

            // generate new lock
            file_put_contents(
                $cms_dir.'composer.lock',
                $Lockserver->getComposerLock()
            );


            // install
            $exec = 'COMPOSER_HOME="'.$var_dir.'composer/" '.
                'php '.$var_dir.'composer/composer.phar --working-dir="'
                .$cms_dir
                .'" --prefer-dist install 2>&1';

            system($exec, $retval);

        } catch (Exception $Exception) {

            $this->writeLn('=============================');
            $this->writeLn('Lock Server Error');
            $this->writeLn($Exception->getMessage());
            $this->writeLn('=============================');

            $exec = 'COMPOSER_HOME="'.$var_dir.'composer/" '.
                'php '.$var_dir.'composer/composer.phar --working-dir="'
                .$cms_dir
                .'" require "quiqqer/quiqqer:'.$v.'" --prefer-dist 2>&1';

            system($exec, $retval);

            // some composer versions have a bug, and dont install packages with require
            $exec
                = 'php '.$var_dir.'composer/composer.phar --working-dir="'
                .$cms_dir
                .'" --prefer-dist update 2>&1';

            system($exec, $retval);
        }


        $this->writeLn(
            $this->Locale->get('quiqqer/installer',
                'step.5.download.successful')
        );

        // execute QUIQQER setup to create all necessary package tables
        chdir($cms_dir);


        // create file links
        file_put_contents(
            $cms_dir.'index.php',
            "<?php
            require 'bootstrap.php';
            require '{$opt_dir}quiqqer/quiqqer/index.php';"
        );

        file_put_contents(
            $cms_dir.'image.php',
            "<?php
            require 'bootstrap.php';
            require '{$opt_dir}quiqqer/quiqqer/image.php';"
        );

        file_put_contents(
            $cms_dir.'quiqqer.php',
            "<?php
            require 'bootstrap.php';
            require '{$opt_dir}quiqqer/quiqqer/quiqqer.php';"
        );

        file_put_contents(
            $cms_dir.'bootstrap.php',

            '<?php
            $etc_dir = dirname(__FILE__).\'/etc/\';

            if (!file_exists($etc_dir.\'conf.ini.php\')) {
                require_once \'quiqqer.php\';
                exit;
            }

            if (!defined(\'ETC_DIR\')) {
                define(\'ETC_DIR\', $etc_dir);
            }

            $boot = \''.$opt_dir.'quiqqer/quiqqer/bootstrap.php\';

            if (file_exists($boot)) {
                require $boot;
            }'
        );


        // create htaccess
        system(
            'php '.$cms_dir.'quiqqer.php '.
            '--username="'.$this->_username.'" '.
            '--password="'.$this->_password.'" '.
            '--tool="quiqqer:htaccess" --noLogo'
        );

        // translator setup
        system(
            'php '.$cms_dir.'quiqqer.php '.
            '--username="'.$this->_username.'" '.
            '--password="'.$this->_password.'" '.
            '--tool="package:translator" --setup --noLogo'
        );

        // complete quiqqer setup
        system('php '.$cms_dir.'quiqqer.php --username="'.$this->_username
            .'" --password="'.$this->_password
            .'" --tool="quiqqer:setup" --noLogo');

        // add translator languages
        $this->writeLn('');
        $this->writeLn('=========================================');
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'start.langs')
        );

        foreach ($this->_setup['langs'] as $lang) {
            system(
                'php '.$cms_dir.'quiqqer.php '.
                '--username="'.$this->_username.'" '.
                '--password="'.$this->_password.'" '.
                '--tool="package:translator" '.
                '--newLanguage="'.$lang.'" '.
                '--noLogo'
            );
        }

        if (!in_array($this->_setup['lang'], $this->_setup['langs'])) {
            system(
                'php '.$cms_dir.'quiqqer.php '.
                '--username="'.$this->_username.'" '.
                '--password="'.$this->_password.'" '.
                '--tool="package:translator" '.
                '--newLanguage="'.$this->_setup['lang'].'" '.
                '--noLogo'
            );
        }

        // execute setup again to import translation variables
        system('php '.$cms_dir.'quiqqer.php --username="'.$this->_username
            .'" --password="'.$this->_password
            .'" --tool="quiqqer:setup" --noLogo');

        // create translations
        system('php '.$cms_dir.'quiqqer.php --username="'.$this->_username
            .'" --password="'.$this->_password
            .'" --tool="package:translator" --noLogo');

        $this->writeLn('');
        $this->writeLn($this->Locale->get('quiqqer/installer',
            'step.5.cleanup'));

        // delete the setup
        if (file_exists('quiqqer.zip')) {
            unlink('quiqqer.zip');
        }

        if (file_exists('quiqqer.setup')) {
            unlink('quiqqer.setup');
        }

        if (file_exists('composer.json')) {
            rename(
                $cms_dir.'composer.json',
                $var_dir.'composer/composer.json'
            );
        }

        if (file_exists('composer.lock')) {
            rename(
                $cms_dir.'composer.lock',
                $var_dir.'composer/composer.lock'
            );
        }

        // move dirs to temp
        $dirs = array('css', 'locale', 'js', 'versions', 'setup_packages', 'bin', 'lib');

        foreach ($dirs as $dir) {
            if (is_dir($cms_dir.$dir)) {
                rename(
                    $cms_dir.$dir,
                    $var_dir.'temp/'.$dir
                );
            }
        }

        if (!empty($this->_setup['projects'])) {
            $this->writeLn('');
            $this->writeLn('=========================================');
            $this->writeLn(
                $this->Locale->get('quiqqer/installer', 'create.projects')
            );

            // create projects
            foreach ($this->_setup['projects'] as $project) {
                system(
                    'php '.$cms_dir.'quiqqer.php '.
                    '--username="'.$this->_username.'" '.
                    '--password="'.$this->_password.'" '.
                    '--tool="quiqqer:create-project"'.
                    '--projectname="'.$project['project'].'" '.
                    '--projectlangs="'.$project['langs'].'" '.
                    '--projectlang="'.$project['lang'].'" '.
                    '--template="'.$project['template'].'" --noLogo'
                );
            }
        }

        $this->writeLn('');
        $this->writeLn('=========================================');
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'start.tests')
        );

        // start quiqqer health
        system('php '.$cms_dir.'quiqqer.php --username="'.$this->_username
            .'" --password="'.$this->_password
            .'" --tool="quiqqer:health" --noLogo');

        // start quiqqer tests
        system('php '.$cms_dir.'quiqqer.php --username="'.$this->_username
            .'" --password="'.$this->_password
            .'" --tool="quiqqer:tests" --noLogo');


        $this->writeLn('');
        $this->writeLn('=========================================');

        // green color
        $this->write("\033[0;32m");

        $this->writeLn('');
        $this->writeLn(
            $this->Locale->get('quiqqer/installer', 'step.5.successful')
        );

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

        ");

        $this->write("\033[0m");
        $this->writeLn('');
    }

    /**
     * ensures that string is a correct path
     *
     * @param String $path
     *
     * @return String
     */
    protected function _cleanPath($path)
    {
        return rtrim($path, '/').'/';
    }

    /**
     * Write an ini file
     *
     * @param String $filename - path to file
     * @param Array  $options  - ini options
     *
     * @throws \Exception
     */
    protected function _writeIni($filename, $options)
    {
        if (!is_writeable($filename)) {
            throw new \Exception(
                $this->Locale->get('quiqqer/installer', 'config.not.writable')
            );
        }

        $tmp = '';

        foreach ($options as $section => $values) {
            $tmp .= "[$section]\n";

            foreach ($values as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $tmp .= "{$key}[$k] = \"$v\"\n";
                    }
                } else {
                    $tmp .= "$key = \"$val\"\n";
                }
            }

            $tmp .= "\n";
        }

        file_put_contents($filename, $tmp);
    }

    /**
     * checks the (generated) setup-file structure
     *
     * @param array $check
     *
     * @return array - correct setup data
     */
    protected function _checkSetupFile($check)
    {
        $valid = $this::$setupData;
        $result = array();

        // langs
        if (!isset($check['langs']) || !is_string($check['langs'])) {
            $result['langs'] = array('en');
        } else {
            $result['langs'] = explode(',', $check['langs']);
        }

        // lang
        if (!isset($check['lang'])) {
            $result['lang'] = current($result['langs']);
        } else {
            $result['lang'] = $check['lang'];
        }

        // database
        if (!isset($check['database'])) {
            $result['database'] = $valid['database'];
        } else {
            $result['database'] = $check['database'];
        }

        // users
        if (!isset($check['users'])) {
            $result['users'] = $valid['users'];
        } else {
            $result['users'] = $check['users'];
        }

        // projects
        if (!isset($check['projects'])) {
            $result['projects'] = $valid['projects'];
        } else {
            $result['projects'] = $check['projects'];
        }

        // host
        if (!isset($check['host'])) {
            $result['host'] = $valid['host'];
        } else {
            $result['host'] = $check['host'];
        }

        // paths
        if (!isset($check['paths'])) {
            $result['paths'] = $valid['paths'];
        } else {
            $result['paths'] = $check['paths'];
        }

        // packages
        if (!isset($check['paths'])) {
            $result['packages'] = $valid['packages'];
        } else {
            $result['packages'] = array_merge(
                $check['packages'],
                $valid['packages']
            );
        }

        // repositories
        if (!isset($check['repositories'])) {
            $result['repositories'] = $valid['repositories'];
        } else {
            $result['repositories'] = array_merge_recursive(
                $check['repositories'],
                $valid['repositories']
            );
        }

        return $result;
    }

    /**
     * @param String $msg
     */
    protected function _exitError($msg)
    {
        $this->writeLn('');
        $this->writeLn('');
        $this->write(
            $this->Locale->get(
                'quiqqer/installer',
                'critical.error'
            )
        );
        $this->writeLn('');
        $this->write($msg);
        $this->writeLn('');

        // @todo cleanup?
        exit(1);
    }

    /**
     *
     */
    protected function _rollback()
    {
        // @todo
    }
}

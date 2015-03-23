<?php namespace QUI; ?>

<!doctype html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1,maximum-scale=1" />

    <link rel="dns-prefetch" href="//fonts.googleapis.com" />
    <link href='//fonts.googleapis.com/css?family=Open+Sans:400,700,400italic' rel='stylesheet' type='text/css' />

    <title>QUIQQER Setup</title>

    <link rel="stylesheet" href="css/grid.css" />
    <link rel="stylesheet" href="js/qui/extend/elements.css" />
    <link rel="stylesheet" href="css/style.css" />

    <script src="js/qui/src/lib/mootools-core.js"></script>
    <script src="js/qui/src/lib/mootools-more.js"></script>
    <script src="js/qui/src/lib/moofx.js"></script>

    <link rel="shortcut icon" href="favicon.ico" />

    <?php
        $dir = getcwd();

        if ( $dir === false ) {
            $dir = dirname( dirname( __FILE__ ) );
        }

        $versions = Utils\System\File::readDir( dirname( dirname( __FILE__ ) ) . '/versions/' );
        sort( $versions );

        $Locale = new Locale();

        if ( isset( $lang ) )
        {
            $Locale->setCurrent( $lang );
        } else
        {
            $Locale->setCurrent( 'en' );
        }
    ?>

</head>
<body>

    <noscript>
        <div class="noscript-info">
            <?php echo $Locale->get( 'quiqqer/websetup', 'noscript' ); ?>
        </div>
    </noscript>

    <div id="wrapper">
        <div class="grid-container container" role="main">

            <section class="logo grid-100 mobile-grid-100 grid-parent">
                <img src="bin/quiqqer.png"
                    alt="QUIQQER"
                    title="QUIQQER"
                    class="logo"
                />
            </section>

            <form action="" method="POST" id="setup-form">

            <section class="welcome grid-100 mobile-grid-100">

                <?php echo $Locale->get( 'quiqqer/websetup', 'welcome' ); ?>

                <h2>
                    <?php echo $Locale->get( 'quiqqer/websetup', 'lang.title' ); ?>
                </h2>

                <p>
                    <?php echo $Locale->get( 'quiqqer/websetup', 'lang.desc' ); ?>
                </p><br/><br/>

                <label for="lang" class="grid-50 mobile-grid-100 grid-parent">
                    <?php echo $Locale->get( 'quiqqer/websetup', 'lang.label' ); ?>
                </label>
                <select name="lang" id="lang">
                    <option value="en"
                        <?php if ( isset( $lang ) && $lang === 'en' ) { echo ' selected="selected"'; } ?>
                        >
                        <?php echo $Locale->get( 'quiqqer/websetup', 'lang.en' ); ?>
                    </option>
                    <option value="de"
                        <?php if ( isset( $lang ) && $lang === 'de' ) { echo ' selected="selected"'; } ?>
                        >
                        <?php echo $Locale->get( 'quiqqer/websetup', 'lang.de' ); ?>
                    </option>
                </select>

            </section>

            <section class="step setupfile grid-100 mobile-grid-100 ">
                <h2>
                    <?php echo $Locale->get( 'quiqqer/websetup', 'setupfile.title' ); ?>
                </h2>

                <p>
                    <?php echo $Locale->get( 'quiqqer/websetup', 'setupfile.desc' ); ?>
                </p><br /><br />

                <label for="setupfile" class="grid-50 mobile-grid-100 grid-parent">
                    <?php echo $Locale->get( 'quiqqer/websetup', 'setupfile.label' ); ?>
                </label>
                <input type="file" name="setupfile" id="setupfile" />
                <p class="setupfile-btn">
                    <label class="grid-50 hide-on-mobile">&nbsp;</label>
                </p>
            </section>

            <section class="step version grid-100 mobile-grid-100 ">

                <h2>
                    <?php echo $Locale->get( 'quiqqer/websetup', 'version.title' ); ?>
                </h2>

                <p>
                    <?php echo $Locale->get( 'quiqqer/websetup', 'version.desc' ); ?>
                </p><br/><br/>

                <label for="version" class="grid-50 mobile-grid-100 grid-parent">
                    <?php echo $Locale->get( 'quiqqer/websetup', 'version.label' ); ?>
                </label>
                <select name="version" id="version">

                <?php

                    foreach ( $versions as $ver ) {
                        echo '<option value="' . $ver . '">' . $ver . '</option>';
                    }

                ?>

                </select>

            </section>

                <section class="step database grid-100 mobile-grid-100 ">
                    <h2>
                        <?php echo $Locale->get( 'quiqqer/websetup', 'database.title' ); ?>
                    </h2>

                    <p>
                        <?php echo $Locale->get( 'quiqqer/websetup', 'database.desc' ); ?>
                    </p>

                    <p>
                        <label for="db_driver" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'database.driver.label' ); ?>
                        </label>
                        <select name="db_driver"
                            id="db_driver"
                            class="grid-50 mobile-grid-100"
                            required="required"
                        >
                            <option value="mysql">MySQL</option>
                        </select>
                    </p>

                    <p>
                        <label for="db_host" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'database.host.label' ); ?>
                        </label>
                        <input type="text"
                            name="db_host"
                            id="db_host"
                            class="grid-50 mobile-grid-100"
                            value="localhost"
                            required="required"
                        />
                    </p>

                    <p>
                        <label for="db_database" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'database.name.label' ); ?>
                        </label>
                        <input type="text"
                            name="db_database"
                            id="db_database"
                            class="grid-50 mobile-grid-100"
                            required="required"
                        />
                    </p>

                    <p>
                        <label for="db_user" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'database.user.label' ); ?>
                        </label>
                        <input type="text"
                            name="db_user"
                            id="db_user"
                            class="grid-50 mobile-grid-100"
                            required="required"
                        />
                    </p>

                    <p>
                        <label for="db_password" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'database.password.label' ); ?>
                        </label>
                        <input type="password"
                            name="db_password"
                            id="db_password"
                            class="grid-50 mobile-grid-100"
                            required="required"
                        />
                    </p>

                    <p>
                        <label for="db_prefix" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'database.prefix.label' ); ?>
                            <span>
                                <?php echo $Locale->get( 'quiqqer/websetup', 'database.prefix.desc' ); ?>
                            </span>
                        </label>
                        <input type="text"
                               name="db_prefix"
                               id="db_prefix"
                               class="grid-50 mobile-grid-100"
                            />
                    </p>

                    <p class="database-btn">
                        <label class="grid-50 hide-on-mobile">&nbsp;</label>
                    </p>

                </section>

                <section class="step user_groups grid-100 mobile-grid-100">
                    <h2>
                        <?php echo $Locale->get( 'quiqqer/websetup', 'user.title' ); ?>
                    </h2>

                    <p>
                        <?php echo $Locale->get( 'quiqqer/websetup', 'user.desc' ); ?>
                    </p>

                    <p>
                        <label for="user_username" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'user.name.label' ); ?>
                        </label>
                        <input type="text"
                            name="user_username"
                            id="user_username"
                            class="grid-50 mobile-grid-100"
                            required="required"
                        />
                    </p>
                    <p>
                        <label for="user_password" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'user.password.label' ); ?>
                        </label>
                        <input type="password"
                            name="user_password"
                            id="user_password"
                            class="grid-50 mobile-grid-100"
                            required="required"
                        />
                    </p>

                </section>

                <section class="step paths grid-100 mobile-grid-100">
                    <h2>
                        <?php echo $Locale->get( 'quiqqer/websetup', 'paths.title' ); ?>
                    </h2>

                    <p>
                        <?php echo $Locale->get( 'quiqqer/websetup', 'paths.desc' ); ?>
                    </p>

                    <p>
                        <label for="host" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'paths.host.label' ); ?>
                            <span>
                                <?php echo $Locale->get( 'quiqqer/websetup', 'paths.host.desc' ); ?>
                            </span>
                        </label>
                        <input type="text"
                            name="host"
                            id="host"
                            class="grid-50 mobile-grid-100"
                            value=""
                        />
                    </p>

                    <p>
                        <label for="url-dir" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'paths.url.label' ); ?>
                            <span>
                                <?php echo $Locale->get( 'quiqqer/websetup', 'paths.url.desc' ); ?>
                            </span>
                        </label>
                        <input type="text"
                               name="url-dir"
                               id="url-dir"
                               class="grid-50 mobile-grid-100"
                               value="/"
                            />
                    </p>

                    <p>
                        <label for="cms-dir" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'paths.cms.label' ); ?>
                            <span>
                                <?php echo $Locale->get( 'quiqqer/websetup', 'paths.cms.desc' ); ?>
                            </span>
                        </label>
                        <input type="text"
                            name="cms-dir"
                            id="cms-dir"
                            class="grid-50 mobile-grid-100"
                            value="<?php echo $dir .'/'; ?>"
                        />
                    </p>

                    <div id="paths-extra-btn"></div>

                    <div id="paths-extra">
                    <p>
                        <label for="bin-dir" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'paths.bin.label' ); ?>
                            <span>
                                <?php echo $Locale->get( 'quiqqer/websetup', 'paths.bin.desc' ); ?>
                            </span>
                        </label>
                        <input type="text"
                            name="bin-dir"
                            id="bin-dir"
                            class="grid-50 mobile-grid-100"
                            value="<?php echo $dir .'/bin/'; ?>"
                        />
                    </p>
                    <p>
                        <label for="lib-dir" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'paths.lib.label' ); ?>
                            <span>
                                <?php echo $Locale->get( 'quiqqer/websetup', 'paths.lib.desc' ); ?>
                            </span>
                        </label>
                        <input type="text"
                            name="lib-dir"
                            id="lib-dir"
                            class="grid-50 mobile-grid-100"
                            value="<?php echo $dir .'/lib/'; ?>"
                        />
                    </p>
                    <p>
                        <label for="opt-dir" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'paths.packages.label' ); ?>
                            <span>
                                <?php echo $Locale->get( 'quiqqer/websetup', 'paths.packages.desc' ); ?>
                            </span>
                        </label>
                        <input type="text"
                            name="opt-dir"
                            id="opt-dir"
                            class="grid-50 mobile-grid-100"
                            value="<?php echo $dir .'/packages/'; ?>"
                        />
                    </p>
                    <p>
                        <label for="usr-dir" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'paths.usr.label' ); ?>
                            <span>
                                <?php echo $Locale->get( 'quiqqer/websetup', 'paths.usr.desc' ); ?>
                            </span>
                        </label>
                        <input type="text"
                            name="usr-dir"
                            id="usr-dir"
                            class="grid-50 mobile-grid-100"
                            value="<?php echo $dir .'/usr/'; ?>"
                        />
                    </p>
                    <p>
                        <label for="var-dir" class="grid-50 mobile-grid-100 grid-parent">
                            <?php echo $Locale->get( 'quiqqer/websetup', 'paths.var.label' ); ?>
                            <span>
                                <?php echo $Locale->get( 'quiqqer/websetup', 'paths.var.desc' ); ?>
                            </span>
                        </label>
                        <input type="text"
                            name="var-dir"
                            id="var-dir"
                            class="grid-50 mobile-grid-100"
                            value="<?php echo $dir .'/var/'; ?>"
                        />
                    </p>

                    </div>

                </section>

            </form>
        </div>

        <footer>
            <p>
                <?php echo $Locale->get( 'quiqqer/websetup', 'footer' ); ?>
            </p>
        </footer>
    </div>

    <!-- javascript -->
    <script src="js/qui/src/lib/requirejs.js"></script>
    <script src="js/init.js"></script>
</body>
</html>
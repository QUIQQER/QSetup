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
    ?>

</head>
<body>

    <noscript>
        <div class="noscript-info">
            JavaScript seems to be deactivated on your computer. In order to use QUIQQER, please activate JavaScript.
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

            <section class="welcome grid-100 mobile-grid-100">
                <h1>
                    Welcome to the QUIQQER Setup
                </h1>

                <ul>
                    <li>Please follow the instructions to install quiqqer correctly.</li>
                    <li>For questions or help, please visit <a href="http://www.quiqqer.com" target="_blank">www.quiqqer.com</a></li>
                </ul>
            </section>

            <form action="" method="POST">
                <section class="step database grid-100 mobile-grid-100 ">
                    <h2>
                        1 - Database connection
                    </h2>

                    <p>Please enter your database settings <br /><br /></p>

                    <p>
                        <label for="db_driver" class="grid-50 mobile-grid-100 grid-parent">
                            Driver
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
                            Database host
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
                            Database name
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
                            Database user
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
                            Database password
                        </label>
                        <input type="password"
                            name="db_password"
                            id="db_password"
                            class="grid-50 mobile-grid-100"
                            required="required"
                        />
                    </p>
                    <p class="database-btn">
                        <label class="grid-50 hide-on-mobile">&nbsp;</label>
                    </p>

                </section>

                <section class="step user_groups grid-100 mobile-grid-100">
                    <h2>
                        2 - Set a user for QUIQQER
                    </h2>
                    <p>Please enter an username and a password for your first user<br /><br /></p>

                    <p>
                        <label for="user_username" class="grid-50 mobile-grid-100 grid-parent">
                            Username
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
                            Password
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
                        3 - Set the installation paths and the host of QUIQQER
                    </h2>

                    <p>If you don't know what you do, please use the default settings. <br /><br /></p>

                    <p>
                        <label for="host" class="grid-50 mobile-grid-100 grid-parent">
                            Host
                        </label>
                        <input type="text"
                            name="host"
                            id="host"
                            class="grid-50 mobile-grid-100"
                            value=""
                        />
                    </p>

                    <p>
                        <label for="cms-dir" class="grid-50 mobile-grid-100 grid-parent">
                            cms-dir
                        </label>
                        <input type="text"
                            name="cms-dir"
                            id="cms-dir"
                            class="grid-50 mobile-grid-100"
                            value="<?php echo $dir .'/'; ?>"
                        />
                    </p>

                    <p>
                        <label for="bin-dir" class="grid-50 mobile-grid-100 grid-parent">
                            bin-dir
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
                            lib-dir
                        </label>
                        <input type="text"
                            name="lib-dir"
                            id="lib-dir"
                            class="grid-50 mobile-grid-100"
                            value="<?php echo $dir .'/lib/'; ?>"
                        />
                    </p>
                    <p>
                        <label for="package-dir" class="grid-50 mobile-grid-100 grid-parent">
                            package-dir
                        </label>
                        <input type="text"
                            name="opt-dir"
                            id="opt-dir"
                            class="grid-50 mobile-grid-100"
                            value="<?php echo $dir .'/opt/'; ?>"
                        />
                    </p>
                    <p>
                        <label for="usr-dir" class="grid-50 mobile-grid-100 grid-parent">
                            usr-dir
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
                            var-dir
                        </label>
                        <input type="text"
                            name="var-dir"
                            id="var-dir"
                            class="grid-50 mobile-grid-100"
                            value="<?php echo $dir .'/var/'; ?>"
                        />
                    </p>
                </section>

            </form>
        </div>

        <footer>
            <p> visit us at <a href="http://www.quiqqer.com" target="_blank">www.quiqqer.com</a> </p>
        </footer>
    </div>

    <!-- javascript -->
    <script src="js/qui/src/lib/requirejs.js"></script>
    <script src="js/init.js"></script>
</body>
</html>
<!doctype html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="de"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="de"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="de"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="de"> <!--<![endif]-->
<head>
    <!-- HTML5
          ================================================== -->
    <!--[if lt IE 9]>
        <script src="//raw.github.com/aFarkas/html5shiv/master/dist/html5shiv.js"></script>
    <![endif]-->

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1,maximum-scale=1" />

    <link rel="dns-prefetch" href="//fonts.googleapis.com" />
    <link href='//fonts.googleapis.com/css?family=Open+Sans:400,700,400italic' rel='stylesheet' type='text/css' />

    <title>QUIQQER Composer Repository</title>

    <link rel="stylesheet" href="css/grid.css" />
    <link rel="stylesheet" href="css/typeplate.css" />

    <link rel="stylesheet" href="css/buttons.css" />
    <link rel="stylesheet" href="css/animate.css" />

    <link rel="stylesheet" href="css/style.css" />

    <!--[if (lt IE 9) & (!IEMobile)]>
        <link rel="stylesheet" href="css/grid-ie.css" />
    <![endif]-->

    <script src="js/libs/mootools/mootools-core.js"></script>
    <script src="js/libs/mootools/mootools-more.js"></script>

</head>
<body>

    <div id="wrapper">
        <div class="grid-container container" role="main">

            <section class="step list grid-100 mobile-grid-100">
                <img src="bin/quiqqer.png"
                    alt="QUIQQER"
                    title="QUIQQER"
                    class="logo"
                />
            </section>

            <section class="step welcome grid-100 mobile-grid-100">
                <h1 class="delta">
                    Welcome to the QUIQQER Installation
                </h1>

                <ul>
                    <li>Please follow the instructions to install quiqqer correctly.</li>
                    <li>For questions or help, please visit <a href="http://www.quiqqer.com" target="_blank">www.quiqqer.com</a></li>
                </ul>
            </section>

            <section class="step database grid-100 mobile-grid-100">
                <h2 class="delta">
                    Step 1 - Database connection
                </h2>

                <p>Please enter your Database settings <br /><br /></p>

                <p>
                    <label for="db_driver" class="grid-25 mobile-grid-100">
                        Driver
                    </label>
                    <select name="db_driver" id="db_driver"
                        class="grid-50 mobile-grid-100"
                    >
                        <option value="mysql">MySQL</option>
                        <option value="pgsql">PostgreSQL</option>
                    </select>
                </p>

                <p>
                    <label for="db_host" class="grid-25 mobile-grid-100">
                        Database host
                    </label>
                    <input type="text" name="db_host" id="db_host"
                        class="grid-50 mobile-grid-100"
                    />
                </p>

                <p>
                    <label for="db_database" class="grid-25 mobile-grid-100">
                        Database name
                    </label>
                    <input type="text" name="db_database" id="db_database"
                        class="grid-50 mobile-grid-100"
                    />
                </p>

                <p>
                    <label for="db_user" class="grid-25 mobile-grid-100">
                        Database user
                    </label>
                    <input type="text" name="db_user" id="db_user"
                        class="grid-50 mobile-grid-100"
                    />
                </p>

                <p>
                    <label for="db_password" class="grid-25 mobile-grid-100">
                        Database password
                    </label>
                    <input type="password" name="db_password" id="db_password"
                        class="grid-50 mobile-grid-100"
                    />
                </p>
                <p class="database-btn">
                    <label class="grid-25 hide-on-mobile">&nbsp;</label>
                </p>

            </section>

            <section class="step user_groups grid-100 mobile-grid-100">
                <h2 class="delta">
                    Step 2 - Set a user for QUIQQER
                </h2>
                <p>Please enter Username and a Password for your first User <br /><br /></p>

                <p>
                    <label for="user_username" class="grid-25 mobile-grid-100">
                        Username
                    </label>
                    <input type="text" name="db_password" id="db_password"
                        class="grid-50 mobile-grid-100"
                    />
                </p>
                <p>
                    <label for="user_password" class="grid-25 mobile-grid-100">
                        Password
                    </label>
                    <input type="password" name="db_password" id="db_password"
                        class="grid-50 mobile-grid-100"
                    />
                </p>

            </section>

            <section class="step paths grid-100 mobile-grid-100">
                <h2 class="delta">
                    Step 3 - Set the installation paths and the host of QUIQQER
                </h2>

                <p>If you don't know what you do, please use the default settings. <br /><br /></p>

                <p>
                    <label for="cms-dir" class="grid-25 mobile-grid-100">
                        cms-dir
                    </label>
                    <input type="text" name="cms-dir" id="cms-dir"
                        class="grid-50 mobile-grid-100"
                    />
                </p>

                <p>
                    <label for="bin-dir" class="grid-25 mobile-grid-100">
                        bin-dir
                    </label>
                    <input type="text" name="bin-dir" id="bin-dir"
                        class="grid-50 mobile-grid-100"
                    />
                </p>
                <p>
                    <label for="lib-dir" class="grid-25 mobile-grid-100">
                        lib-dir
                    </label>
                    <input type="text" name="lib-dir" id="lib-dir"
                        class="grid-50 mobile-grid-100"
                    />
                </p>
                <p>
                    <label for="package-dir" class="grid-25 mobile-grid-100">
                        package-dir
                    </label>
                    <input type="text" name="package-dir" id="package-dir"
                        class="grid-50 mobile-grid-100"
                    />
                </p>
                <p>
                    <label for="usr-dir" class="grid-25 mobile-grid-100">
                        usr-dir
                    </label>
                    <input type="text" name="usr-dir" id="usr-dir"
                        class="grid-50 mobile-grid-100"
                    />
                </p>
                <p>
                    <label for="var-dir" class="grid-25 mobile-grid-100">
                        var-dir
                    </label>
                    <input type="text" name="var-dir" id="var-dir"
                        class="grid-50 mobile-grid-100"
                    />
                </p>

            </section>

        </div>

        <footer>
            <p> visit us at <a href="http://www.quiqqer.com" target="_blank">www.quiqqer.com</a> </p>
        </footer>
    </div>

    <!-- javascript -->
    <script data-main="js/init.js" src="js/libs/require.js"></script>
</body>
</html>
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

	<title>QUIQQER Composer Repository</title>

	<link rel="stylesheet" href="css/typeplate.css" />
	<link rel="stylesheet" href="css/normalize.css" />
	<link rel="stylesheet" href="css/style.css" />

</head>
<body>

    <div class="container">
        <section class="step list">

        	<img src="bin/quiqqer.png"
        		alt="QUIQQER"
        		title="QUIQQER"
        		class="logo"
    		/>
		</section>

		<section class="step welcome">
    		<h1 class="ceta">
    			Welcome to the QUIQQER Installation
			</h1>

			<ul>
				<li>Please follow the instructions to install quiqqer correctly.</li>
				<li>For questions or help, please visit <a href="http://www.quiqqer.com" target="_blank">www.quiqqer.com</a></li>
			</ul>
		</section>

		<section class="step database">
    		<h1 class="ceta">
    			Step 1 - Database connection
			</h1>

			<p>
    			<label for="db_driver">Driver</label>
    			<select name="db_driver" id="db_driver">
    				<option value="mysql">MySQL</option>
    				<option value="pgsql">PostgreSQL</option>
    			</select>
			</p>

			<p>
    			<label for="db_host">Database Host</label>
    			<input type="text" name="db_host" id="db_host" />
			</p>

			<p>
    			<label for="db_database">Database name</label>
    			<input type="text" name="db_database" id="db_database" />
			</p>

			<p>
    			<label for="db_user">Database user</label>
    			<input type="text" name="db_user" id="db_user" />
			</p>

			<p>
    			<label for="db_password">Database password</label>
    			<input type="password" name="db_password" id="db_password" />
    		</p>

		</section>

		<section class="step user_groups">
    		<h1 class="ceta">
    			Step 2 - Set a user for QUIQQER
			</h1>


		</section>

		<section class="step paths">
    		<h1 class="ceta">
    			Step 3 - Set the installation paths and the host of QUIQQER
			</h1>

			<p>If you not know what you do, please use the default settings.</p>


		</section>

    </div>

    <footer>
    	<p> visit us at <a href="http://www.quiqqer.com" target="_blank">www.quiqqer.com</a> </p>
    </footer>

</body>
</html>
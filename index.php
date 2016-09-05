<html>
<head>
    <script src="/vendor/quiqqer/qui/qui/lib/mootools-core.js"></script>
    <script src="/vendor/quiqqer/qui/qui/lib/mootools-more.js"></script>
</head>
<body>

<script>
    new Request({
        url      : '/ajax/getDatabaseDrivers.php',
        onSuccess: function () {
            console.log(arguments);
        }
    }).send();
</script>

</body>
</html>
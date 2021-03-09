<!DOCTYPE html>
<!--[if lte IE 6]>
<html class="preIE7 preIE8 preIE9"><![endif]-->
<!--[if IE 7]>
<html class="preIE8 preIE9"><![endif]-->
<!--[if IE 8]>
<html class="preIE9"><![endif]-->
<!--[if gte IE 9]><!-->
<html lang="en"><!--<![endif]-->
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>directory</title>
    <link href="https://fonts.googleapis.com/css?family=Slabo+27px" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css"/>
    <style>
        html {
            font-size: 16px;
        }

        body {
            font-family: 'Slabo 27px', serif;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        ul {
            list-style-type: none;
            display: flex;
            flex-wrap: wrap;
            flex-direction: row;
        }

        li {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            transition: 0.3s;
            padding: 1em;
            background: white;
            margin: 0.5em 1em;
        }

        li:hover {
            box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
        }

        li a {
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
<ul>
    <?php
    $sites = shell_exec("apachectl -S | grep \"port 80\" | awk '{print $4}'");
    $sites = explode(PHP_EOL, $sites);

    foreach ($sites as $site) {
        if (!empty(trim($site))) {
            echo "<li data-site='$site'><a href='http://$site' rel='preload'>$site</a></li>";
        }
    } ?>
</ul>
<script src="//cdnjs.cloudflare.com/ajax/libs/modernizr/2.5.3/modernizr.min.js" type="text/javascript"></script>
<script type="text/javascript">
    function autorun() {

    }

    if (document.addEventListener) document.addEventListener("DOMContentLoaded", autorun, false);
    else if (document.attachEvent) document.attachEvent("onreadystatechange", autorun);
    else window.onload = autorun;
</script>
</body>
</html>

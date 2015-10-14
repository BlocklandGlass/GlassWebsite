<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Page Not Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>

        * {
            line-height: 1.2;
            margin: 0;
        }

        html {
            color: #888;
            display: table;
            font-family: sans-serif;
            height: 100%;
            text-align: center;
            width: 100%;
        }

        body {
            display: table-cell;
            vertical-align: middle;
            margin: 2em auto;
        }

        h1 {
            color: #555;
            font-size: 2em;
            font-weight: 400;
        }

        p {
            margin: 0 auto;
            width: 280px;
        }

        @media only screen and (max-width: 280px) {

            body, p {
                width: 95%;
            }

            h1 {
                font-size: 1.5em;
                margin: 0 0 0.3em;
            }

        }

    </style>
</head>
<body>

<?php
//based on: http://stackoverflow.com/a/16553247

// If it's a 403, just bump it up to a 404
$status = $_SERVER['REDIRECT_STATUS'];
if($status == 403)
{
	$status = 404;
}

switch($status)
{
	case 400:
		header("HTTP/1.0 400 Bad Request", true, 400);
		echo "<h1>An Error Occurred</h1>\n<p>Code: $status Bad Request</p>";
		break;
	case 500:
		header("HTTP/1.0 500 Server Error", true, 500);
		echo "<h1>An Error Occurred</h1>\n<p>Code: $status Server Error</p>";
		break;
	default:
		header("HTTP/1.0 404 Not Found", true, 404);
		echo "<h1>Page Not Found</h1>\n<p>Sorry, but the page you were trying to view does not exist.</p>";
}
?>

</body>
</html>
<!-- IE needs 512+ bytes: http://blogs.msdn.com/b/ieinternals/archive/2010/08/19/http-error-pages-in-internet-explorer.aspx -->

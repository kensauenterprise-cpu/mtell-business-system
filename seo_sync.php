<?php

require_once
__DIR__.'/includes/oauth.php';

if(!accessToken()){

echo '<html>

<head>

<title>Mtell SEO Sync</title>

<style>

body{

font-family:Arial;

background:#f4f6f8;

text-align:center;

padding-top:100px;

}

a{

background:#4285F4;

padding:15px 35px;

color:#fff;

text-decoration:none;

border-radius:6px;

font-size:18px;

}

</style>

</head>

<body>

<h2>

Mtell SEO Synchronization

</h2>

<br>

<a href="'.googleLoginUrl().'">

Connect Google Search Console

</a>

</body>

</html>';

exit;

}

$accessToken=accessToken();

echo "<h2>Google Connected Successfully</h2>";
require_once __DIR__.'/includes/search_console.php';

$data = downloadSearchConsole();

echo "<hr>";

echo "<h2>Search Console Data</h2>";

echo "<pre>";

print_r($data);

echo "</pre>";
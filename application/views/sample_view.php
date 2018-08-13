<?php defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= $title_variable?></title>
        <link href="<?= base_url('/static/css/style.css') ?>" rel="stylesheet">
    </head>
    <body>
        <div>
            <h1>Sample page! Parameter is: <?= $param ?></h1>
            <a href="<?= base_url('/sample/load_model') ?>"> Link path</a>
            <link href="<?= base_url('/static/js/bootstrap.min.js') ?>" rel="stylesheet">
        </div>
    </body>
</html>
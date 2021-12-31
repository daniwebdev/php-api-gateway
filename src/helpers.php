<?php

function dd($array) {
    echo '<pre>';
    var_dump($array);
    echo '</pre>';
    die;
}

function config($file) {

    if(!defined('BASE')) {
        define('BASE', dirname(__DIR__));
    }

    $path = BASE . '/config/' . $file . '.php';

    if (file_exists($path)) {
        return require $path;
    }

    return null;
}
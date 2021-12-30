<?php

function dd($array) {
    echo '<pre>';
    var_dump($array);
    echo '</pre>';
    die;
}

function config($file) {
    $path = BASE . '/config/' . $file . '.php';

    if (file_exists($path)) {
        return require $path;
    }
    return null;
}
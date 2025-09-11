<?php
    $src = $_POST["src"];
    $filename = basename($src, suffix);
    $pathdelete = '/uploads/';
    $fakePath = $pathdelete.$filename;
    if (file_exists(getcwd() . $fakePath)) {
        unlink(getcwd() . $fakePath);
    }
?>
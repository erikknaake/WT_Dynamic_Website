<?php
/**
 * Created by PhpStorm.
 * User: Erik Knaake
 * Date: 22-12-2017
 * Time: 23:57
 */

if ($_SERVER["HTTPS"] != "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

?>
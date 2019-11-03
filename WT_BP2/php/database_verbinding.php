<?php
/**
 * Created by PhpStorm.
 * User: Erik Knaake
 * Date: 11-1-2018
 * Time: 11:24
 */

try {
    if(isset($_SERVER['USERNAME'])) { //Daan heeft deze SERVER var niet
        $database = new PDO("sqlsrv:server=127.0.0.1;database=IMDB");
    }
    else {$database = new PDO("sqlsrv:server=localhost;database=IMDB",'sa','Bl0emp!t');}
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $pdoError)
{
    echo 'Connectie error: ' . $pdoError->getMessage();
    exit();
}
?>
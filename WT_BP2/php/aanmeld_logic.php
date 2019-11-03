<?php
/**
 * Created by PhpStorm.
 * User: Erik Knaake
 * Date: 21-12-2017
 * Time: 11:51
 */
require_once ('session.php');
require_once ('database_verbinding.php');

function checkPassword($pwd, &$errors) {
    $oudeStatus = $errors;

    if (strlen($pwd) < 8) {
        $errors[] = "Password must contain at least 8 characters";
    }

    if (!preg_match("#[0-9]+#", $pwd)) {
        $errors[] = "Password must contain at least one numeric character";
    }

    if (!preg_match("#[a-zA-Z]+#", $pwd)) {
        $errors[] = "Password must contain at least one alphabetic character!";
    }

    return ($errors == $oudeStatus);
}


//require_once ('requireHTTPS.php');
//echo '<h1>' . $_SERVER['REQUEST_METHOD'] . '</h1>';
if($_SERVER['REQUEST_METHOD'] == 'POST')
 {
    if (isset($_POST['wachtwoord']) && isset($_POST['wachtwoord2']) && $_POST['wachtwoord'] == $_POST['wachtwoord2']) {
        $_POST['wachtwoord2'] = 'ThisPasswordMemoryLocationIsOverridden'; //Zorg ervoor dat de memory location wordt overschreven, zodat het niet meer kan worden gelezen
        unset($_POST['wachtwoord2']); //unset the raw password

        $contractType = isset($_POST['subscriptiontype']) ? $_POST['subscriptiontype'] : null;
        unset($_POST['subscriptiontype']);
        if (isset($contractType)) {
            try {
                $userExists = $database->prepare("SELECT username FROM Users where Username=:gebruikersnaam");

                $gebruikersnaam = $_POST['gebruikersnaam'];
                unset($_POST['gebruikersnaam']);
                $userExists->bindParam(':gebruikersnaam', $gebruikersnaam);
                $userExists->execute();

                if (!$userExists->rowCount()) { //gebruikersnaam is niet in gebruik
                    $insert = $database->prepare("INSERT INTO Users(username, salt, password, contract_type) VALUES (:gebruikersnaam, :salt, :wachtwoord, :contract)");

                    $salt = bin2hex(openssl_random_pseudo_bytes(64));
                    //gebruikersnaam is al geset bij het eerste statement
                    $wachtwoord = $_POST['wachtwoord'];
                    $_POST['wachtwoord'] = 'ThisPasswordMemoryLocationIsOverridden'; //Zorg ervoor dat de memory location wordt overschreven, zodat het niet meer kan worden gelezen
                    unset($_POST['wachtwoord']); //unset the raw password
                    $errors = [];
                    if (checkPassword($wachtwoord, $errors)) {
                        $wachtwoord = hash('sha512', $salt . $wachtwoord);
                        $insert->bindParam(':gebruikersnaam', $gebruikersnaam);
                        $insert->bindParam(':salt', $salt);
                        $insert->bindParam(':wachtwoord', $wachtwoord);
                        $insert->bindParam(':contract', $contractType);
                        $insert->execute();

                        if ($insert->rowCount()) {
                            unset($_SESSION['aanmeldfout']); // aanmelden is goed gegaan
                            $_SESSION['contractType'] = $contractType;
                            $_SESSION['gebruikersnaam'] = $gebruikersnaam; //Sla de gebruikersnaam op in de huidige session
                            $_SESSION['ingelogd'] = true;
                        } else
                            $_SESSION['aanmeldfout'] = 'Er is iets misgegaan bij het versturen van uw gegevens naar onze database';
                    } else
                        $_SESSION['aanmeldfout'] = $errors[0]; //Geen sterk wachtwoord
                } else
                    $_SESSION['aanmeldfout'] = 'gebruikersnaam is al in gebruik';
            } catch (PDOException $pdoError) //Database connectie error
            {
                echo '<br>Error in de executie van de query (verbindings error geeft andere foutmelding): ' . $pdoError->getMessage();
            }
        } else
            $_SESSION['aanmeldfout'] = 'Selecteer een abbonnementstype';
    }
}
?>


    <form action="#" method="POST">
        <?php
            /******************************************************************************************
             * Erik Knaake
             * 21 december 2017
             *******************************************************************************************/
            require_once 'aanmeld_form.php';
        ?>
    </form>

<?php
/**
 * Created by PhpStorm.
 * User: Erik Knaake
 * Date: 20-12-2017
 * Time: 11:17
 */

//Exit() en require_once nietIngelogd.php in alle gevallen dat er niet is ingelogd, stuurt ook link terug mee
function requireLogin()
{
    if(!isset($_SESSION['ingelogd']))
        $_SESSION['ingelogd'] = false;
    else
        if(!$_SESSION['ingelogd'])
        {
            if(isset($_SERVER['HTTP_REFERER']) && !isset($_SESSION['backLink']))
                $_SESSION['backLink'] = $_SERVER['HTTP_REFERER'];
            else
                $_SESSION['backLink'] = 'index.php';
            require_once ('nietIngelogd.php');
            exit();
        }
}

//require_once ('requireHTTPS.php');
//requireSSL();

require_once ('database_verbinding.php');

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_SESSION['ingelogd']))
        $_SESSION['ingelogd'] = false;
}
else
{
    if(isset($_POST['wachtwoord']) && isset($_SESSION['ingelogd']) && !$_SESSION['ingelogd'])
    {
        try
        {
            //Haal het gehashde wachtwoord op van de database
            $statement = $database->prepare("SELECT * FROM Users WHERE username = :gebruikersnaam");
            $gebruikersnaam = isset($_POST['gebruikersnaam']) ? $_POST['gebruikersnaam'] : $_SESSION['gebruikersnaam'];
            $statement->bindParam(':gebruikersnaam', $gebruikersnaam);
            $statement->execute();

            //query succesvol
            if($statement->rowCount())
            {
                //Hash met sha512 en salt uit de database
                $resultRow = $statement->fetch();
                $salt = $resultRow['salt'];

                $enteredPassword = hash('sha512', $salt . $_POST['wachtwoord']); //salt is stored as hex, so we dont need to reconvert to hex
                $_POST['wachtwoord'] = 'ThisPasswordMemoryLocationIsOverridden'; //Make sure the password memory location is overridden so the raw password can't be retrieved anymore
                unset($_POST['wachtwoord']);
                if(hash_equals($enteredPassword, $resultRow['password']))
                {
                    unset($_POST['gebruikersnaam']);
                    unset($_SESSION['inlogfout']);
                    $_SESSION['ingelogd'] = true;
                    $_SESSION['gebruikersnaam'] = $gebruikersnaam;
                    date_default_timezone_set ("CET");
                    $_SESSION['inlogtijd'] = date("Y/m/d H:i:s");
                }
                else
                    $_SESSION['inlogfout'] = true;
            }

        }
        catch (PDOException $pdoError)
        {
            echo '<br>Connenction error: ' . $pdoError->getMessage();
        }
    }
    else
        $_SESSION['ingelogd'] = false;
}
?>
<div>
    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
        <?php
            /******************************************************************************************
             * Erik Knaake
             * 20 december 2017
             *******************************************************************************************/
            if($_SESSION['ingelogd'] == true)
                require_once('loguit_form.php');
            else
                require_once('login_form.php');
        ?>
    </form>
</div>
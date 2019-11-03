<?php
/**
 * Created by PhpStorm.
 * User: Erik Knaake
 * Date: 10-1-2018
 * Time: 13:39
 */

require_once ('database_verbinding.php');
require_once('header.php');
require_once('film.php');

//Verplicht om te zijn ingelogd op deze pagina
requireLogin();

if(isset($_GET['rating']) && is_numeric($_GET['rating']))
{
    if(gettype($_GET['rating']) == 'string')
        $_GET['rating'] = intval($_GET['rating']);
    if($_GET['rating'] >= 1 && $_GET['rating'] <= 10)
    {
        $film = haalFilmInfoOp($database);
        if (isset($film) && gettype($film) != 'string' && isset($_SESSION['gebruikersnaam']) && isset($_GET['id']))
        {
            //print_r($_GET['id']);
            try
            {
                //Check of de gebruiker niet al eens heeft gestemd op deze film
                $statement = $database->prepare('INSERT INTO CustomerRating VALUES(:username, :movieId)');
                $statement->bindParam(':username', $_SESSION['gebruikersnaam']);
                $statement->bindParam(':movieId', $_GET['id']);
                $statement->execute();
                //Primary key voorkomt dubbele stem voor een gebruiker op een film
                if ($statement->rowCount()) {
                    //Zorg ervoor dat als er nog geen stemmen zijn, dat er een stem wordt geregistreerd
                    $statement = $database->prepare('SELECT * FROM Ratings WHERE id = :id');
                    $statement->bindParam(':id', $_GET['id']);
                    $statement->execute();
                    //Maak nieuwe ratings rij aan, want ratings bestaat nog niet voor deze film
                    if($statement->rowCount() == 0)
                    {
                        $statement = $database->prepare("INSERT INTO Ratings VALUES(:id, 0, 0)");
                        $statement->bindParam(':id', $_GET['id']);
                        $statement->execute();
                    }
                    //Verwerk stem
                    $nieuweRating = 0;
                    if (isset($film['averageRating']) && isset($film['numberOfVotes']))
                        $nieuweRating = $film['averageRating'] * $film['numberOfVotes'];
                    $nieuweRating += $_GET['rating'];
                    $film['numberOfVotes']++;
                    $nieuweRating = round($nieuweRating / $film['numberOfVotes'], 8);
                    $statement = $database->prepare("UPDATE Ratings
                                                SET numberOfVotes = :aantalStemmen,
                                                averageRating = :rating
                                                where id = :id
                                            ");
                    $statement->bindParam('aantalStemmen', $film['numberOfVotes']);
                    $statement->bindParam(':rating', $nieuweRating);
                    $statement->bindParam(':id', $_GET['id']);
                    $statement->execute();
                    //Terugdraaien mislukte stem
                    if (!$statement->rowCount())
                    {
                        $statement = $database->prepare('DELETE CustomerRating WHERE username = :username AND movieId = :id');
                        $statement->bindParam(':username', $_SESSION['username']);
                        $statement->bindParam(':id', $_GET['id']);
                        $statement->execute();
                    }
                }
            }
            catch(PDOException $pdoError)
            {
                //echo '<br>Connectie error: ' . $pdoError->getMessage();
            }
        }
    }
}

?>
<main>
    <?php
        titel($database);
    ?>
    <div>
        <article>
            <?php
                filmAside($database);
            ?>
        </article>
        <section>
            <?php
                printPersons($database, 'MovieCast');
                printPersons($database, 'MovieCrew');
                genreEnBeschrijving($database);
            ?>
        </section>
    </div>
</main>
<?php
require_once('footer.php');
?>

<?php
require_once("header.php");
require_once("database_verbinding.php");
require_once ("film.php");
requireLogin();
function getVideoURL($database)
{
    $URL = '';
    try {
        $statement = $database->prepare('SELECT trailerURL
    FROM Movie as M
    WHERE M.id=:id');
        $statement->bindParam(':id', $_GET['id']);
        $statement->execute();
        if($statement->rowCount()){
            $URL = $statement->fetch()['trailerURL'];
        }

    } catch (PDOException $pdoError) {
        echo '<br>Connenction error: ' . $pdoError->getMessage();
    }
    $URL = explode('/', $URL , 4);
    return'https://www.youtube.com/embed/' . $URL[3]; //In de database de niet embed variant, dat is wel nodig
}
?>

<main>
    <?php titel($database); ?>
    <iframe width="1000" height="563" src="<?php echo (getVideoURL($database)) ?>" allowfullscreen></iframe>
</main>

<?php
require_once("footer.php");
?>
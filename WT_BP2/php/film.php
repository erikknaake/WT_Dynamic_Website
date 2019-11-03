<?php

function film($img, $titel, $link)
{
    $afbeelding = '../images/image_not_found.jpg';
    if(file_exists($img))
        $afbeelding = $img;

    return '<a href="' . $link . '" class="film"><img src="' . $afbeelding . '" alt="Cover image of ' . $titel . '"><h4>' . $titel . '</h4></a>';
}

function titel($database)
{
    $film = haalFilmInfoOp($database);
    echo'<h1>' . htmlspecialchars($film['primaryTitle']) . '</h1>';
}

//Functie om variabelen lokaal te houden
function filmAside($database)
{
    $film = haalFilmInfoOp($database);
    $link = 'niet_gevonden.php';
    if(is_array($film) && isset($film['trailerURL']))
        $link = $film['trailerURL'];

    echo '<a href="play.php?id=' . $film['id'] .'" id="description">';
    $huidigeFilm = film('../images/' . $film['coverURL'], $film['primaryTitle'], $link);

    $identifier = 'class="film"';
    echo(substr($huidigeFilm, strpos($huidigeFilm, $identifier) + strlen($identifier) + 1, strpos($huidigeFilm, '</a>') - strlen($huidigeFilm))); //Film zonder eigen anchor

    if (isset($film['isAdult']) && $film['isAdult']) //Maakt gebruik van lazzy evaluation als $film['isAdult'] nog niet geset is
        echo '<p>18+</p>';

    echo '<p>Jaar van publicatie: ' . $film['startYear'] . '</p>';

    if(isset($film['runtimeMinutes']))
        echo '<p>Speelduur: ' . $film['runtimeMinutes'] . ' minuten</p>';
    else
        echo '<p>Nog geen speelduur beschikbaar</p>';

    if(isset($film['averageRating']) && isset($film['numberOfVotes']))
        echo '<p>Gemiddeld: ' . round($film['averageRating'], 2) . ' / 10 door ' . $film['numberOfVotes'] . ' stem(men)</p>';
    else
        echo '<p>Nog geen stemmen geregristreerd</p>';
    echo '</a>';
    $options = '';
    for($i = 1; $i <= 10; $i++)
        $options .= ('<option value="' . $i .'">' . $i . '</option>');
    echo '<div>
            <form method="GET" action="#">
                <label for="rating">Stem op deze film: </label>
                <select id="rating" name="rating">
                    ' . $options. '
                </select>
                <input type="hidden" value="' . $_GET['id'] . '" name="id"/>
                <input type="submit" id="send" value="Stem"/>
            </form>
          </div>';
}

//Haal databse info op
function haalFilmInfoOp($database)
{
    try {
        $statement = $database->prepare('SELECT primaryTitle, coverURL, trailerURL, description, startYear, averageRating, numberOfVotes, M.id, genres, M.runtimeMinutes FROM Movie as M LEFT JOIN Ratings as R ON R.id = M.id where M.id = :id');
        if (!isset($_GET['id'])) {
            echo 'Error 404 ID not found';
            exit();
        }
        $statement->bindParam(':id', $_GET['id']);
        $statement->execute();
        if ($statement->rowCount())
            return $statement->fetch();
    } catch (PDOException $pdoError) {
        echo '<br>Connenction error: ' . $pdoError->getMessage();
    }
    return '';
}

function haalPersonOp($database, $CastOfCrew)
{
    try
    {
        $statement = $database->prepare('SELECT DISTINCT firstName, lastName 
                                            FROM Person as P LEFT JOIN ' . $CastOfCrew . ' as MC ON P.person_id = MC.Pid
                                            WHERE MC.Mid = :id');
        if(isset($_GET['id']))
            $statement->bindParam(':id', $_GET['id']);
        $statement->execute();
        if($statement->rowCount())
            return $statement->fetchAll();
    } catch (PDOException $pdoError)
    {
        echo '<br>Connection error ' . $pdoError->getMessage();
    }
    return '';
}

function printPersons($database, $castOfCrew)
{
    $Persons = haalPersonOp($database, $castOfCrew);
    if(gettype($Persons) == 'string')
        return;
    echo '<div><h4>' . substr($castOfCrew, 5, strlen($castOfCrew) - 5) . ': </h4><ul>';
    for($i = 0; $i < count($Persons); $i++)
        echo '<li>' . $Persons[$i]['firstName'] . ' ' . $Persons[$i]['lastName'] . '</li>';
    echo '</ul></div>';
}

function genreEnBeschrijving($database)
{
    $film = haalFilmInfoOp($database);
    echo '<div><h4>Genres: </h4><p>' . str_replace(',', ', ', $film['genres']) . '</p></div>';
    echo '<div><h4>Beschrijving: </h4><p>' . $film['description'] . '</p></div>';
}
 ?>
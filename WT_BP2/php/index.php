<?php
    require_once("header.php");
    require_once ('database_verbinding.php');
    require_once("film.php");
    $films = [];
    try
    {
        $statement = $database->prepare('SELECT TOP 3 * FROM Movie
                                    inner join Ratings
                                    on Movie.id = Ratings.id
                                    ORDER BY averageRating DESC, numberOfVotes ASC');
        $statement->execute();
        if($statement->rowCount())
        {
            $films = $statement->fetchAll();
        }
    }
    catch (PDOException $pdoError)
    {
        echo '<br>Connenction error: ' . $pdoError->getMessage();
    }
?>

    <main>
        <aside>
                <h2>Wat is FletNix</h2>
                <p>
                    FletNix is een video on demand service gemaakt door en voor jongeren. Wij houden ons al jaren bezig om de beste films binnen de genres: Sci-Fi, Actie, Komedie te vinden, zodat jij altijd en over toegang hebt tot de beste films!
                </p>
        </aside>
        <article>
            <h2>Top 3 best gewaarde films</h2>
            <div class="filmContainer">
                <?php
                    for($i = 0; $i < count($films); $i++)
                        echo (film('../images/' . $films[$i]['coverURL'], $films[$i]['primaryTitle'], 'details.php?id=' . $films[$i]['id']));
                ?>
            </div>
        </article>
    </main>

<?php require_once("footer.php") ?>
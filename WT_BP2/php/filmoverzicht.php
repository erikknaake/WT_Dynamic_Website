<?php
/**
 * Created by PhpStorm.
 * User: Erik Knaake
 * Date: 3-1-2018
 * Time: 11:48
 */


require_once ('database_verbinding.php');
require_once ('film.php');
require_once ("header.php");
requireLogin();


$toegestaneGenres = array('Sci-Fi', 'Action', 'Comedy');

function getNaarHiddenInput($welkeGets)
{
    for($i = 0; $i < count($welkeGets); $i++)
        if(isset($_GET[$welkeGets[$i]]))
            if(is_array($_GET[$welkeGets[$i]]))
                for ($j = 0; $j < count($_GET[$welkeGets[$i]]); $j++)
                    echo '<input type=hidden name="' . $welkeGets[$i] . '[]" value="' . $_GET[$welkeGets[$i]][$j] . '">';
            else
                echo '<input type=hidden name="' . $welkeGets[$i] . '" value="' . $_GET[$welkeGets[$i]] . '"/>';
}
function option($get, $waarde, $naam)
{
    $geselecteerd = '';
    if(isset($_GET[$get]) && $_GET[$get] == $waarde)
        $geselecteerd = 'selected';
    echo '<option value="' . $waarde . '" ' . $geselecteerd . '>' . $naam . '</option>';
}
//In welke get (name) komt het, welke opties moeten er worden ge echot
function echoOptions($get, $opties)
{
    for($i = 0; $i < count($opties); $i++)
    {
        option($get, $opties[$i][0], $opties[$i][1]);
    }
}

function genreOpties($toegestaneGenres)
{
    for($i = 0; $i < count($toegestaneGenres); $i++) {
        $checked = " ";
        if(isset($_GET['genre']))
            for($j = 0; $j < count($_GET['genre']); $j++)
                if($toegestaneGenres[$i] == $_GET['genre'][$j])
                    $checked = "checked";
        echo '<tr>
                                <td></td>
                                <td class="checkbox">
                                    <label for="' . $toegestaneGenres[$i] . '">' . $toegestaneGenres[$i] . '</label>
                                        <input type="checkbox" name="genre[]" value="' . $toegestaneGenres[$i] . '" id="' . $toegestaneGenres[$i] . '" ' . $checked . '>
                                </td>';
    }
}

function jaarList()
{
    for($i = $_SESSION['minJaar']; $i < $_SESSION['maxJaar']; $i++) {
        $label = '';
        if($i == $_SESSION['minJaar'] || $i == $_SESSION['maxJaar'] || $i == $_SESSION['minJaar'] + ($_SESSION['maxJaar'] - $_SESSION['minJaar']) / 2)
            $label = 'label="' . $i . '"';
        echo '<option value="' . $i . '" ' . $label . '>' . $i . '</option>';
    }
}
function queryBasis()
{
    //Set de operator die voor het jaar wordt gebruikt
    $jaarOperator = '>='; //Als we dit in combinatie met het minimum jaar doen hebben we standaard alle jaren
    if(isset($_GET['jaar-operator']))
    {
        switch ($_GET['jaar-operator'])
        {
            case 'gt':
                $jaarOperator = '>';
                break;
            case 'gtoe':
                $jaarOperator = '>=';
                break;
            case 'equals':
                $jaarOperator = '=';
                break;
            case 'lt':
                $jaarOperator = '<';
                break;
            case 'ltoe':
                $jaarOperator = '<=';
                break;
            case 'ne':
                $jaarOperator = '<>';
                break;
        }
    }
    /* jaarOperator kan nooit iets anders dan >, >=, ==, < of <= zijn*/
    $genreOperator = ['AND', 'AND', 'AND'];
    if(isset($_GET['genre-operator']))
    {
        if(isset($_GET['genre']))
            for($i = 0; $i < count($_GET['genre']) - 1; $i++) //Voor elk genre dat we hebben moeten we de operator aanpassen naar de opgegeven operator
               if($_GET['genre'][$i + 1] != '%')
                   $genreOperator[$i] = 'OR';
    }
    //genreOperator kan aleen OR of AND bevatten en zorgt ervoor dat de operator voor een onbekende keuze altijd AND is

    //Title is standaard %
    //Regiseur is standaard %
    //Genre is standaard %
    //Alleen jaarOperator en genreOperator worden handmatig gecheckt, de rest kan door het PDO worden gedaan
    return "SELECT startYear, primaryTitle, coverURL, id FROM
	          (SELECT M.startYear, M.primaryTitle, M.coverURL, M.id, ROW_NUMBER() OVER (ORDER BY M.startYear, M.primaryTitle) as RowIndex
                FROM Movie AS M INNER JOIN MovieCast AS MC ON M.id = MC.Mid INNER JOIN Person AS P ON MC.Pid = P.person_id
                WHERE M.startYear " . $jaarOperator . " :jaar AND
                    M.primaryTitle LIKE :titel AND
                    (CONCAT(P.firstName,P.lastName) LIKE :regisseur OR P.firstname IS NULL) AND
                    (M.genres LIKE :genre1 " . $genreOperator[0] . "
                    M.genres LIKE :genre2 " . $genreOperator[1] . "
                    M.genres LIKE :genre3) AND trailerURL IS NOT NULL
                GROUP BY M.id, M.CoverURL, M.primaryTitle, M.startYear
	          ) AS SUB
	        WHERE SUB.RowIndex >= :start AND SUB.RowIndex < :end";
}

$_SESSION['films'] = [];
try
{
    //Haal het eerste jaartal op uit de database als het nog niet ingesteld is
    if(!isset($_SESSION['minJaar']))
    {
        $statement = $database->prepare('SELECT MIN(startYear) as minJaar FROM Movie');
        $statement->execute();
        if ($statement->rowCount())
            $_SESSION['minJaar'] = $statement->fetch()['minJaar'];
    }
    //Haal het laatste jaartal op uit de database als het nog niet ingesteld is
    if(!isset($_SESSION['maxJaar']))
    {
        $statement = $database->prepare('SELECT MAX(startYear) as maxJaar FROM Movie');
        $statement->execute();
        if ($statement->rowCount())
            $_SESSION['maxJaar'] = $statement->fetch()['maxJaar'];
    }

    //Haal de films op uit de database
    $statement = $database->prepare(queryBasis());

    //Er worden per keer maar 20 films geladen om de laad tijd te verbeteren, met het submit form onder aan de pagina worden de volgende films geladen
    $aantalFilmsPerPagina = 21; //Er worden er in werkelijkheid 20 weergegeven
    $loadStart = isset($_GET['min']) ? $_GET['min'] : 0; //Opgeslagen minimum waarde of 0
    $loadEnd = isset($_GET['max']) ? $_GET['max'] : $aantalFilmsPerPagina; //Opgeslagen maximun waarde of aantalFilmsPerPagina
    $statement->bindParam(':start', $loadStart);
    $statement->bindParam(':end', $loadEnd);

    //Jaartal filter
    $jaar = $_SESSION['minJaar'];
    if(isset($_GET['jaar']))
        $jaar = $_GET['jaar'];
    $statement->bindParam(':jaar', $jaar);


    //Titel filter
    $titel = '%';
    if(isset($_GET['titel']))
        $titel = '%' . $_GET['titel'] . '%';
    $statement->bindParam(':titel', $titel);

    //Regisseur filter
    $regisseur = '%';
    if(isset($_GET['regisseur']))
        $regisseur = '%' . $_GET['regisseur'] . '%';
    $statement->bindParam(':regisseur', $regisseur);

    //Genre filter
    //Check of $_GET['genre'] wel een genre bevat, en niet een XSS is
    $genres = ['%', '%', '%'];
    if(isset($_GET['genre']))
    {
        for($i = 0; $i < count($_GET['genre']); $i++)
        {
            global $toegestaneGenres;
            foreach ($toegestaneGenres as &$genre)
                if ($_GET['genre'][$i] != $genre)
                    $value = '%'; //Naar defualt value, iemand heeft in de URL geprobeerd de waarde te wijzigen naar een niet toegestaand genre
                else
                    $genres[$i] = '%' . $_GET['genre'][$i] . '%';
        }
    }
    for($i = 0; $i < count($genres); $i++)
        $statement->bindParam(':genre' . ($i + 1), $genres[$i]);

    $statement->execute();
    if($statement->rowCount())
    {
        $_SESSION['films'] = $statement->fetchAll();
    }
}
catch (PDOException $pdoError)
{
    //Geef error maar wel een mooie pagina...
    echo '<main><br>Connenction error: ' . $pdoError->getMessage() . '</main>';
    require_once ('footer.php');
    //... en voorkom dat de rest van de pagina wordt geladen
    exit();
}


?>
<main>
    <aside>

        <form method="GET" action="#">
            <datalist id="jaarList"> <!--Voor jaartal range, mag niet in table staan voor validatie-->
                <?php
                jaarList();
                ?>
            </datalist>
            <table>
                <!--Titel-->
                <tr><td><label for="titel">Filteren op titel: </label></td><td><input type="text" id="titel" name="titel" <?php if(isset($_GET['titel'])) { echo('value="' . $_GET['titel'] . '"'); } ?>/></td></tr>
                <!--Regisseur-->
                <tr><td><label for="regisseur">Filter op regisseur</label></td><td><input type="text" id="regisseur" name="regisseur" <?php if(isset($_GET['regisseur'])) { echo('value="' . $_GET['regisseur'] . '"'); } ?> /></td></tr>
                <!--jaartal-->
                    <!--Soort operator-->
                <tr><td colspan="2"><label for="jaar">Filteren op jaartal: </label></td></tr>
                <tr><td><label for="jaar-operator">Geselecteerd jaartal is </label></td>
                    <td>
                        <select name="jaar-operator" id="jaar-operator">
                            <?php
                                $opties = array(array('gteo', 'groter dan of gelijk aan'), array('gt', 'groter dan'), array('equals', 'gelijk aan'), array('lt', 'kleiner dan'), array('ltoe', 'kleiner dan of gelijk aan'), array('ne', 'niet gelijk aan'));
                                echoOptions('jaar-operator', $opties);
                                unset($opties);
                            ?>

                        </select>publicatiejaar</td>
                </tr>
                <tr>
                    <td class="left"> <?php echo '<p>' . $_SESSION['minJaar'] . '</p>'; ?> </td>
                        <td class="right"> <?php echo '<p>' . $_SESSION['maxJaar'] . '</p>'; ?> </td>
                    <!--Slider-->
                <tr>
                    <td colspan="2">
                    <input type="range" name="jaar" id="jaar" list="jaarList" min="<?php echo ($_SESSION['minJaar']); ?>" max="<?php echo ($_SESSION['maxJaar']); ?>" value="<?php if(isset($_GET['jaar'])) { echo($_GET['jaar']); } else { echo($_SESSION['minJaar']); } ?>"/></td></tr>
                <!--Datalist in form, want een datalist mag niet in table-->
                <!--Einde jaartal-->
                <!--Genre-->
                <tr>
                    <td>Filteren op genre: </td>
                    <td class="checkbox">
                        <label class="switch" for="genre-operator"><input class="switch" type="checkbox" name="genre-operator" id="genre-operator" value="true" <?php if(isset($_GET['genre-operator'])) echo "checked" ?>/>
                            <span class="slider_left">
								<span>of</span>
								<span class="slider_right">en</span>
							</span>
                        </label>
                    </td>
                </tr>
                <?php
                    //Genre opties
                    genreOpties($toegestaneGenres);
                ?>

                <!--Verzend-->
                <tr><td><label for="send">Zoek</label></td><td><input type="submit" id="send" value="zoek"></td></tr>
            </table>
        </form>

    </aside>
    <article>
        <div>
            <h2>Onze films: </h2>
            <?php
                if(isset($_GET['titel']) && $_GET['titel'] != '%' && $_GET['titel'] != '')
                    echo '<h4>U heeft gezocht op de titel: ' . $_GET['titel'] . '</h4>';
                if(isset($_GET['regisseur']) && $_GET['regisseur'] != '%' && $_GET['regisseur'] != '')
                    echo '<h4>U heeft gezocht op de regisseur: ' . $_GET['regisseur'] . '</h4>';
            ?>
        </div>
        <div class="filmContainer">
        <?php
            //Teken alle geladen films op het scherm
            for($i = 0; $i < count($_SESSION['films']); $i++)
            {
                echo(film('../images/' . $_SESSION['films'][$i]['coverURL'], $_SESSION['films'][$i]['primaryTitle'] . ' (' .$_SESSION['films'][$i]['startYear'] . ')', 'details.php?id=' . $_SESSION['films'][$i]['id']));
            }
        ?>
        </div>
    </article>
    <div class="section">
        <form method="GET" action="#">
            <input type="hidden" name="min" value="<?php echo $loadEnd + 1 ?>"/>
            <input type="hidden" name="max" value="<?php echo $loadEnd + $aantalFilmsPerPagina ?>"/>
            <!--Bewaar oude filters als er meer films worden geladen-->
            <?php getNaarHiddenInput(array('titel', 'regisseur', 'jaar-operator', 'jaar', 'genre-operator', 'genre'));?>
            <input type="submit" value="Laad meer films"/>
        </form>
    </div>
</main>
<?php
    require_once("footer.php");
?>

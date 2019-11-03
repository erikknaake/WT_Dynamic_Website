<?php
/**
 * Created by PhpStorm.
 * User: Erik Knaake
 * Date: 10-1-2018
 * Time: 20:04
 */

require_once ('header.php');
?>

<main class="centered column">
    <h2>U bent niet ingelogd</h2>
    <p>Om deze pagina te bekijken moet u zijn ingelogd</p>
    <?php
        if(isset($_SESSION['backLink']))
            echo '<a href="' . $_SESSION['backLink'] . '">Ga terug</a>'
    ?>
</main>

<?php
require_once ('footer.php')
?>

<?php
/**
 * Created by PhpStorm.
 * User: Erik Knaake
 * Date: 7-1-2018
 * Time: 13:45
 */
//echo '<style>'; require_once('../css/footer.css'); echo '</style>';
function linkItem($tekst, $link)
{
    echo '<p><a href="' . $link . '">' . $tekst . '</a></p>';
}
?>

<footer>
    <p>&copy; 2018 FletNix</p>
    <?php
        linkItem('Cookiegebruik', 'werk_in_uitvoering.php');
        linkItem('Disclaimer', 'werk_in_uitvoering.php');
        linkItem('Contact', 'werk_in_uitvoering.php');
    ?>
</footer>
<!--Geopent in header-->
</body>
</html>

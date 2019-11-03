<?php
require_once ('session.php');

    function getBestandsNaam() {
        return basename(explode('?', $_SERVER['REQUEST_URI'], 2)[0], '.php'); //DOCUMENT URI zonder GET gedeelte wordt in explode geregeld, basename om bestandsnaam zonder extensie te krijgen
    }
    function includeStyle($cssBestand)
    {
        echo '<style>'; require_once ($cssBestand); echo '</style>';
    }

    function styles()
    {
        //TODO hier uitzoeken welke styles er gerequired moeten worden
        $css = array('header', 'footer'); //Standaard CSS
        $bestandsNaam = getBestandsNaam(); //DOCUMENT URI zonder GET gedeelte wordt in explode geregeld, basename om bestandsnaam zonder extensie te krijgen

        //Array[] = '' zorgt ervoor dat her eerst volgende vrije element de waarde na de assign heeft

        if($bestandsNaam == 'filmoverzicht' || $bestandsNaam == 'details' || $bestandsNaam == 'index')
        {
            $css[] = 'films';
            if($bestandsNaam == 'filmoverzicht')
               $css[] = 'filmoverzicht';
            else if ($bestandsNaam == 'details')
               $css[] = 'details';
            else if ($bestandsNaam == 'index')
                $css[] = 'index';
        }
        else if ($bestandsNaam == 'werk_in_uitvoering' || $bestandsNaam == 'nietIngelogd' || $bestandsNaam == 'niet_gevonden')
            $css[] = 'error';
        else if($bestandsNaam == 'abbonnementen')
            $css[] = 'abbonnementen';
        else if ($bestandsNaam == 'about')
            $css[] = 'about_style';
        else if ($bestandsNaam == 'play')
            $css[] = 'play';
        for($i = 0; $i < count($css); $i++)
            includeStyle('../css/' . $css[$i] . '.css');
    }

    function navItem($tekst, $link)
    {
        echo('<a href="' . $link . '"');
        if(getBestandsNaam() == basename($link, '.php')) //bepaal selected item
            echo(' class="selected"');
        echo('>' . $tekst . '</a>');
    }

    function dropdownItem($text, $link, $hoofdOnderdeel, $background)
    {
        if($background != ' selected')
            $background = ' red';
        $hoverKleur = ' onHoverBlue';
        if(getBestandsNaam() == basename($hoofdOnderdeel, '.php'))
            $hoverKleur = ' onHoverRed';
        echo '<a class="dropdownContent ' . $hoverKleur . $background . '" href="' . $link . '">' . $text . '</a>';
    }
    function navItemDropdown($dropdownNaam, $teksten, $links)
    {
        if(count($teksten) != count($links) || count($links) == 0) {
            echo 'inconstistente hoeveelheid teksten en links of te weinig items';
            return;
        }
        $selected = ' ';
        if(getBestandsNaam() == basename($links[0], '.php'))
            $selected = ' selected';
        echo '<div class="dropdownBox' . $selected . ' onHoverBlue"><span class="dropdownTitle">' . $dropdownNaam . '</span>';
        for($i = 0; $i < count($teksten); $i++)
        {
            dropdownItem($teksten[$i], $links[$i], $links[0], $selected);
        }
        echo '</div>';
    }
?>
<!DOCTYPE html>
<html> <!-- Wordt gesloten in footer-->
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo (getBestandsNaam()); ?></title>
    <?php
        styles();
    ?>
</head>
<body><!-- Wordt gesloten in footer-->
<header>
    <a href="index.php">
        <img src="../images/logo.png" alt="logo">
    </a>
    <nav>
        <?php
            navItem("Home", "index.php");
            navItemDropdown("Filmoverzichten", array("Alle genres", "Sci-Fi", "Actie", "Komedie"),
                array("filmoverzicht.php", "filmoverzicht.php?titel=&regisseur=&jaar-operator=gteo&jaar=1975&genre%5B%5D=Sci-Fi#", "filmoverzicht.php?titel=&regisseur=&jaar-operator=gteo&jaar=1975&genre%5B%5D=Action#", "filmoverzicht.php?titel=&regisseur=&jaar-operator=gteo&jaar=1975&genre%5B%5D=Comedy#"));
            navItem("Abbonnementen", "abbonnementen.php");
            navItem("Over ons", "about.php");
        ?>
    </nav>
    <?php
        require_once("inlog_logic.php");
    ?>
</header>


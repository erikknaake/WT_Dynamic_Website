<table class="fullheight">
    <?php
        if(isset($_SESSION['inlogfout']))
            echo '<tr><td colspan="2" class="error">De opgegeven gebruikersnaam en wachtwoord komen niet overeen</td></tr>';
    ?>
    <tr><td colspan="2">U nog niet ingelogd</td></tr>
    <tr><td><label for="gebruikersnaam">Username</label></td><td><input type="text" name="gebruikersnaam" id="gebruikersnaam"/></td></tr>
    <tr><td><label for="wachtwoord">Password</label></td><td><input type="password" name="wachtwoord" id="wachtwoord"/></td></tr>
    <tr><td><label for="login">Log in</label></td><td><input type="submit" id="login" value="Log in"/></td></tr>
    <tr><td colspan="2"><a href="abbonnementen.php">Nog&nbsp;geen account? Meld&nbsp;je&nbsp;aan!</a></td></tr>
</table>

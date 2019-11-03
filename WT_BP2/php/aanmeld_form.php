<table>
    <?php
        if(isset($_SESSION['aanmeldfout']) && $_SERVER['REQUEST_METHOD'] == 'POST')
            echo '<tr><td colspan="2" class="error">' . $_SESSION['aanmeldfout'] . '</td></tr>';
    ?>
    <tr><td colspan="2">Regristreer een nieuwe gebruiker</td></tr>
    <tr><td><label for="gebruikersnaam">Username: </label></td><td><input type="text" name="gebruikersnaam" id="gebruikersnaam"/></td></tr>
    <tr><td><label for="wachtwoord">Password: </label></td><td><input type="password" name="wachtwoord" id="wachtwoord"/></td></tr>
    <tr><td><label for="wachtwoord2">Herhaal password: </label></td><td><input type="password" name="wachtwoord2" id="wachtwoord2"/></td></tr>
    <tr><td><label for="subscriptiontype">Abbonnementstype: </label></td>
        <td>
            <select id="subscriptiontype" name="subscriptiontype">
                <option value="Standard">Standard</option>
                <option value="Extra">Extra</option>
                <option value="Premium">Premium</option>
            </select>

        </td>
    </tr>
    <tr><td><label for="meldaan">Regristreer: </label></td><td><input type="submit" id="meldaan" value="Aanmelden"></td></tr>
</table>

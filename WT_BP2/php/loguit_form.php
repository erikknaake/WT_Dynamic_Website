<table class="fullheight">
    <tr><th colspan="2">U bent ingelogd</th></tr>
    <tr><td>Username</td><td><?php echo $_SESSION['gebruikersnaam']; ?></td></tr>
    <tr><td>Ingelogd op</td><td> <?php echo $_SESSION['inlogtijd']; ?></td></tr>
    <tr><td><label for="loguit">Log uit</label></td><td><input type="submit" id="loguit" value="Log uit"/></td></tr>
</table>
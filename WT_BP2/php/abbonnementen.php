
<?php
    require_once ("header.php");
    require_once ('database_verbinding.php');
    $abbonnementen = [];
    $statement = $database->prepare('SELECT abbonnement, prijs, discount, kwaliteit, aantal_schermen FROM abbonnementen');
    $statement->execute();
    if($statement->rowcount())
        $abbonnementen = $statement->fetchAll();
?>
<main>
    <article>
        <h2>Onze abbonementen:</h2>
        <table>
            <tr><td class="hidden"></td><th>Prijs in &euro;</th><th>Kortingspercentage</th><th>Video kwaliteit</th><th>Aantal schermen</th></tr>
            <?php
                foreach($abbonnementen as &$abbonnement)
                {
                    echo '<tr>';
                    for($i = 0; $i < count($abbonnement) / 2; $i++) //Er is steeds een numerieke key en een string key, we hebben er daar maar 1 van nodig, dus / 2
                    {
                        echo (($i == 0 ? '<th>' : '<td>') . $abbonnement[$i] . ($i == 0 ? '</th>' : '</td>'));
                    }
                    echo '</tr>';
                }
               /* foreach ($abbonnementen as $abbonnement => $details)
                {
                    $hidden = '';
                    if($abbonnement == 'beschrijving')
                        $hidden = 'class="hidden"';
                    echo '<tr><th ' . $hidden . '>' . $abbonnement . '</th>';
                    for ($i = 0; $i < count($abbonnementen) ; $i++)
                        echo "<td>" . $details[$i] . "</td>";
                    echo "</tr>";
                }*/
            ?>
        </table>
        <h2>Registreer u nu!</h2>
        <?php require_once ('aanmeld_logic.php'); ?>
    </article>
</main>
<?php require_once("footer.php"); ?>

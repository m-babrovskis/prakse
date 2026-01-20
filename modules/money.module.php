<?php
/*Funkcija getMoney() nolasa no tabulas players spēlētāja bilanci un atgriež to JS pusē JSON formātā.
Ja tabula vai ieraksts vēl nav, atgriež 0.
JS pēc tam var atjaunot spēlētāja naudas paneli spēles logā. */
require_once "db.php";
//atgriež lietotāja naudas stāvokli.
function getMoney() 
{
    $db = new Database();
    //Pārbauda, vai DB tabula players eksistē
    if (!$db->tableExists('players')) return ["success" => true, "money" => 0];

    $row = $db->query("SELECT money FROM players WHERE id = 1"); // piemēram, testam playerId=1
    $money = $row[0]['money'] ?? 0;//novērš kļūdas, ja spēlētājs vēl nav DB
    return ["success" => true, "money" => $money];
}
?>

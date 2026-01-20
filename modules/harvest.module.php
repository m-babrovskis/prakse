<?php
/*Kad lietotājs novāc ražu, JS sūta šūnas ID uz harvestField().
Funkcija pārbauda, vai tabula eksistē, pēc tam maina šūnas stāvokli uz Empty DB.
Atgrieztais JSON ļauj JS atjaunot laukus spēles logā. */
require_once "db.php";
//atjauno šūnas stāvokli DB uz “Empty”
function harvestField($cellId) //sanem šūnas ID, kuru novākt
{
   $db = new Database();
    // pārbauda, vai šūna eksistē DB
    // Parametrizēts UPDATE
    $updated = $db->execute(
        "UPDATE fields SET type = ? WHERE cell_id = ?",
        ['Empty', $cellId]
    );

    if ($updated > 0) {
        return [
            "success" => true,
            "cellId" => $cellId
        ];
    } else {
        return [
            "success" => false,
            "error" => "Failed to update cell"
        ];
    }
}
?>

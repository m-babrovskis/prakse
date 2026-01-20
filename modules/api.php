<?php
/*api.php ir centrālais kontrolieris,
kas saņem JSON pieprasījumus no JavaScript,
nolasa action,
izsauc atbilstošu loģikas moduli,
un atgriež JSON atbildi atpakaļ pārlūkam.*/

//parada sintakses kludas
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");//json atbilde

require_once "db.php";//datubāzes savienojums
require_once "field.module.php";//funkcijas darbam ar laukiem
require_once "harvest.module.php";
require_once "money.module.php";

//pārvērš JSON par PHP masīvu
$input = json_decode(file_get_contents("php://input"), true);
$action = $input["action"] ?? "";//Ja action eksistē → paņem to

$response = ["success" => false];//noklusejuma atbilde
//Izvēlas, kuru servera funkciju izsaukt, balstoties uz action.
switch ($action)
{
    case "saveField"://funkcija dzīvo field.module.php
        $response = saveField($input["fields"]);
        break;

    case "loadField"://ielādē laukus no DB
        $response = loadField();
        break;

    case "harvest"://Novāc ražu konkrētai šūnai
        $response = harvestField($input["cellId"]);
        break;

    case "getMoney"://Atgriež lietotāja naudu
        $response = getMoney();
        break;

    default:
        $response["error"] = "Unknown action";
}

echo json_encode($response);//Pārvērš PHP masīvu → JSON
?>

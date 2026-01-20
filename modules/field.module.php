<?php
/*field.module.php ir servera loģika laukiem.
JS sūta visus laukus uz saveField(), lai saglabātu, vai prasa loadField(), lai atjaunotu spēles stāvokli.
Funkcijas izmanto Database klasi no db.php, lai droši rakstītu un lasītu datus no MariaDB.
Atgrieztie masīvi tiek pārveidoti formātā, ko JS pusē var tieši ielādēt App.fields*/ 

require_once "db.php";// vajag Database klasi, lai strādātu ar MariaDB
//sanem masivu no JS
function saveField($fields) 
{
    $db = new Database();//Izveido jaunu Database objektu, lai varētu rakstīt DB
    
    foreach ($fields as $cell_id => $cellData) //cikls kas iet pa šūnām
    {
        $type = $cellData['type'];
        // Saglabā šūnu, ja ir, tad update
        $db->execute("INSERT INTO fields (cell_id, type) VALUES ($cell_id, '$type') 
            ON DUPLICATE KEY UPDATE type='$type'");
    }
    return ["success" => true];//atgriež JSON atbildi JS pusē
}

function loadField() 
{
     $db = new Database();
    $fields = $db->query("SELECT * FROM fields ORDER BY cell_id ASC");

    $result = [];
    foreach ($fields as $field) {
        $result[$field['cell_id']] = [
            'cell_id' => $field['cell_id'],
            'type' => $field['type']
        ];
    }

    return [
        'success' => true,
        'fields' => $result
    ];
}
?>

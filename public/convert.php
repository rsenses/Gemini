<?php
////////////////////////////////////////////////////////////////////////
/*
ANTES DE CONVERTIR LEER
*/
////////////////////////////////////////////////////////////////////////

// La primera pasada hacer clientes, la segunda proyectos
// Añadir título a dos tasks completed 1
// Arreglar detail de más de 255 caracteres

$sqlite = new PDO('sqlite:'.__DIR__.'/todo.sqlite');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = $sqlite->prepare('SELECT * FROM clients');
$query->execute();
$clients = $query->fetchAll();

$query = $sqlite->prepare('SELECT * FROM tasks');
$query->execute();
$projects = $query->fetchAll();

$mysql = new PDO('mysql:host=localhost;dbname=gemini;charset=utf8mb4', 'root', '*Med1aS*');
$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$saveProject = $mysql->prepare("INSERT INTO project (project_id, name, short_description, description, contact, budget, bill, color, user_id, client_id, is_printed, created_at, updated_at, due_at, done_at) VALUES (:project_id, :name, :short_description, :description, :contact, :budget, :bill, :color, :user_id, :client_id, :is_printed, :created_at, :updated_at, :due_at, :done_at)");
$saveProject->bindParam(':project_id', $project_id);
$saveProject->bindParam(':name', $name);
$saveProject->bindParam(':short_description', $short_description);
$saveProject->bindParam(':description', $description);
$saveProject->bindParam(':contact', $contact);
$saveProject->bindParam(':budget', $budget);
$saveProject->bindParam(':bill', $bill);
$saveProject->bindParam(':color', $color);
$saveProject->bindParam(':user_id', $user_id);
$saveProject->bindParam(':client_id', $client_id);
$saveProject->bindParam(':is_printed', $is_printed);
$saveProject->bindParam(':created_at', $created_at);
$saveProject->bindParam(':updated_at', $updated_at);
$saveProject->bindParam(':due_at', $due_at);
$saveProject->bindParam(':done_at', $done_at);

$saveClients = $mysql->prepare("INSERT INTO client (client_id, name) VALUES (:client_id, :name)");
$saveClients->bindParam(':client_id', $client_id);
$saveClients->bindParam(':name', $name);

echo '--- Clients ---';
echo '<br>';
foreach ($clients as $key => $value) {
    $client_id = $value['id'];
    $name = $value['name'];

    // $saveClients->execute();
}
echo '--- Projects ---';
echo '<br>';
foreach ($projects as $key => $value) {
    $name = $value['title'];

    if ($name) {
        $project_id = $value['id'];
        $short_description = $value['detail'] ? $value['detail'] : $value['title'];
        $description = $value['description'];
        $contact = $value['contact'];
        $budget = $value['budget'];
        $bill = $value['bill'] ? $value['bill'] : null;
        $client_id = $value['clients'];
        $is_printed = $value['print'];
        $date = strtotime(str_replace('/', '-', $value['date']));
        $date = date('Y-m-d H:i:s', $date);
        $updated_at = $date;
        $created_at = $date;
        $endDate = strtotime(str_replace('/', '-', $value['enddate']));
        $endDate = date('Y-m-d H:i:s', $endDate);
        $due_at = $value['enddate'] ? $endDate : $date;
        $done_at = $value['completed'] ? $endDate : null;

        switch ($value['users']) {
            case 11:
                $user_id = 1;
                break;
            case 12:
                $user_id = 2;
                break;
            case 13:
                $user_id = 3;
                break;
            case 16:
                $user_id = 4;
                break;
            case 17:
                $user_id = 5;
                break;
            case 19:
                $user_id = 6;
                break;
            case 20:
                $user_id = 7;
                break;
            default:
                $user_id = 8;
                break;
        }

        $colors = [
            '#ff0000',
            '#ff4000',
            '#ff8000',
            '#ffbf00',
            '#ffff00',
            '#bfff00',
            '#80ff00',
            '#40ff00',
            '#00ff00',
            '#00ff40',
            '#00ff80',
            '#00ffbf',
            '#00ffff',
            '#00bfff',
            '#0080ff',
            '#0040ff',
            '#0000ff',
            '#4000ff',
            '#8000ff',
            '#bf00ff',
            '#ff00ff',
            '#ff00bf',
            '#ff0080',
            '#ff0040',
            '#ff0000',
        ];

        $color_id = mt_rand(1, 24);
        $color = $colors[$color_id];

        if (strlen($short_description) > 255) {
            echo $project_id;
            echo '<br>';
            echo $short_description;
            die;
        } else {
            $saveProject->execute();
        }
    }
}
echo '-----FIN -----';

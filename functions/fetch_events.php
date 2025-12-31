<?php
require_once './functions/event_crud.php';

$eventCRUD = new EventCRUD();
$events = $eventCRUD->getAllEvents();

$data = [];

foreach ($events as $event) {
    $data[] = [
        'id'    => $event['id'],
        'title' => $event['event_name'],
        'start' => $event['event_date'], // IMPORTANT
        'allDay'=> true
    ];
}

header('Content-Type: application/json');
echo json_encode($data);

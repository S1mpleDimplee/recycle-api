<?php

function getMatenanceTasks($connection) {
    $query = "SELECT mt.id, mt.description, mt.status, mt.created_at, u.name AS customer_name
              FROM maintenance_tasks mt
              JOIN users u ON mt.customer_id = u.id
              WHERE mt.assigned_mechanic_id IS NULL OR mt.assigned_mechanic_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    echo json_encode([
        "success" => true,
        "tasks" => $tasks
    ]);
}

function updateTaskStatus($data, $connection) {
    $taskId = $data['taskId'] ?? null;
    $status = $data['status'] ?? null;

    if (!$taskId || !$status) {
        die(json_encode([
            "success" => false,
            "message" => "Ongeldige taakgegevens"
        ]));
    }

    $query = "UPDATE maintenance_tasks SET status = ? WHERE id = ? AND (assigned_mechanic_id IS NULL OR assigned_mechanic_id = ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("sii", $status, $taskId, $_SESSION['user_id']);
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Taakstatus bijgewerkt"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het bijwerken van de taakstatus"
        ]);
    }
}

?>
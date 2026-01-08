<?php

function updateAppointment($data, $conn)
{
    $aid = $data['aid'] ?? null;
    $userid = $data['userid'] ?? null;
    $repid = $data['repid'] ?? null;
    $moid = $data['moid'] ?? null;
    $apk = $data['apk'] ?? null;
    $note = $data['note'] ?? null;
    $time = $data['time'] ?? null;
    $date = $data['date'] ?? null;
    $duration = $data['duration'] ?? null;

    $sql = "UPDATE appointments SET 
            userid='$userid', 
            repid='$repid', 
            moid='$moid', 
            apk='$apk', 
            note='$note', 
            time='$time', 
            date='$date', 
            duration='$duration' 
            WHERE aid='$aid'";

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            "success" => true,
            "message" => "Afspraak succesvol bijgewerkt"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Afspraak kon niet worden bijgewerkt: " . mysqli_error($conn)
        ]);
    }
}
?>
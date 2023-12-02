<?php
// Stelle eine Verbindung zur Datenbank her (ersetze die Platzhalter durch deine eigenen Daten)
$conn = mysqli_connect("localhost", "root", "", "diary");

// Überprüfe die Verbindung zur Datenbank
if (!$conn) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . mysqli_connect_error());
}

// SQL-Abfrage, um die Anzahl der registrierten Benutzer abzurufen
$sql = "SELECT COUNT(*) AS userCount FROM users"; // Annahme: Deine Benutzer sind in einer Tabelle mit dem Namen "users"
$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $userCount = $row['userCount'];

    // Schließe die Datenbankverbindung
    mysqli_close($conn);

    // Gib die Benutzeranzahl als JSON zurück
    $response = array('userCount' => $userCount);
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    echo "Fehler beim Abrufen der Benutzeranzahl: " . mysqli_error($conn);
}
?>

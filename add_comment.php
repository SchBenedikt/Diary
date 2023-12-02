<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Überprüfen, ob der Benutzer angemeldet ist
    if (!isset($_SESSION["username"])) {
        // Benutzer ist nicht angemeldet, daher keine Berechtigung zum Kommentieren
        echo "Sie müssen angemeldet sein, um Kommentare zu posten.";
        exit();
    }

    // Stellen Sie eine Verbindung zur Datenbank her (ersetzen Sie die Platzhalter durch Ihre eigenen Daten)
    $conn = mysqli_connect("localhost", "root", "", "diary");

    // Überprüfen Sie die Verbindung zur Datenbank
    if (!$conn) {
        die("Verbindung zur Datenbank fehlgeschlagen: " . mysqli_connect_error());
    }

    $entryId = $_POST["entry_id"];
    $username = $_SESSION["username"]; // Benutzername aus der Sitzung holen
    $commentText = $_POST["comment_text"];

    // SQL-Abfrage, um den Kommentar in die Datenbank einzufügen
    $sql = "INSERT INTO comments (entry_id, username, comment_text) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iss", $entryId, $username, $commentText);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo "Fehler beim Hinzufügen des Kommentars: " . mysqli_error($conn);
    }

    // Schließen Sie die Datenbankverbindung
    mysqli_close($conn);

    // Nach dem Hinzufügen des Kommentars können Sie die Seite aktualisieren oder eine Weiterleitung implementieren
    header("Location: index.html");
    exit();
}
?>

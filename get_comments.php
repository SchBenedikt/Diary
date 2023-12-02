<?php
if (isset($_GET["entry_id"])) {
    $entryId = $_GET["entry_id"];

    // Stellen Sie eine Verbindung zur Datenbank her (ersetzen Sie die Platzhalter durch Ihre eigenen Daten)
    $conn = mysqli_connect("localhost", "root", "", "diary");

    // Überprüfen Sie die Verbindung zur Datenbank
    if (!$conn) {
        die("Verbindung zur Datenbank fehlgeschlagen: " . mysqli_connect_error());
    }

    // SQL-Abfrage, um die Kommentare für einen Eintrag abzurufen und nach Erstellungsdatum absteigend zu sortieren
    $sql = "SELECT * FROM comments WHERE entry_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $entryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $comments = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Fügen Sie das Datums- und Uhrzeitformat für jedes Kommentar hinzu
        foreach ($comments as &$comment) {
            $comment['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($comment['created_at']));
        }

        echo json_encode($comments);
        mysqli_stmt_close($stmt);
    } else {
        echo "Fehler beim Abrufen der Kommentare: " . mysqli_error($conn);
    }

    // Schließen Sie die Datenbankverbindung
    mysqli_close($conn);
}
?>
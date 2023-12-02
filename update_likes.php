<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["entry_id"]) && isset($_SESSION["username"])) {
    // Verbindung zur Datenbank herstellen (ersetze die Platzhalter durch deine tatsächlichen Daten)
    $hostname = "localhost"; // Hostname
    $username = "root"; // MySQL-Benutzername
    $password = ""; // MySQL-Passwort
    $database = "diary"; // Name der Datenbank

    // Verbindung zur Datenbank herstellen
    $mysqli = new mysqli($hostname, $username, $password, $database);

    // Überprüfen, ob die Verbindung fehlgeschlagen ist
    if ($mysqli->connect_error) {
        die("Verbindung zur MySQL-Datenbank fehlgeschlagen: " . $mysqli->connect_error);
    }

    $entryId = $_GET["entry_id"];
    $username = $_SESSION["username"];

    // Überprüfe, ob der Benutzer diese Nachricht bereits geliked hat
    $checkLikeQuery = "SELECT id FROM entry_likes WHERE entry_id = ? AND username = ?";
    $stmt = $mysqli->prepare($checkLikeQuery);
    $stmt->bind_param("is", $entryId, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Der Benutzer hat diese Nachricht noch nicht geliked, füge den Like hinzu
        $insertLikeQuery = "INSERT INTO entry_likes (entry_id, username, like_time) VALUES (?, ?, NOW())";
        $stmt = $mysqli->prepare($insertLikeQuery);
        $stmt->bind_param("is", $entryId, $username);
        $stmt->execute();

        // Aktualisiere die Anzahl der Likes in der ursprünglichen Tabelle
        $getLikesQuery = "SELECT likes FROM entries WHERE id = ?";
        $stmt = $mysqli->prepare($getLikesQuery);
        $stmt->bind_param("i", $entryId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $currentLikes = $row["likes"];
            $newLikes = $currentLikes + 1;

            // SQL-Abfrage, um die Likes zu aktualisieren
            $updateLikesQuery = "UPDATE entries SET likes = ? WHERE id = ?";
            $stmt = $mysqli->prepare($updateLikesQuery);
            $stmt->bind_param("ii", $newLikes, $entryId);

            if ($stmt->execute()) {
                echo $newLikes; // Gebe die aktualisierte Anzahl der Likes zurück
            } else {
                echo "Fehler beim Aktualisieren der Likes.";
            }
        } else {
            echo "Eintrag nicht gefunden.";
        }
    } else {
        // Der Benutzer hat diese Nachricht bereits geliked, entferne den Like
        $deleteLikeQuery = "DELETE FROM entry_likes WHERE entry_id = ? AND username = ?";
        $stmt = $mysqli->prepare($deleteLikeQuery);
        $stmt->bind_param("is", $entryId, $username);
        $stmt->execute();

        // Aktualisiere die Anzahl der Likes in der ursprünglichen Tabelle
        $getLikesQuery = "SELECT likes FROM entries WHERE id = ?";
        $stmt = $mysqli->prepare($getLikesQuery);
        $stmt->bind_param("i", $entryId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $currentLikes = $row["likes"];
            $newLikes = max(0, $currentLikes - 1); // Mindestens 0 Likes

            // SQL-Abfrage, um die Likes zu aktualisieren
            $updateLikesQuery = "UPDATE entries SET likes = ? WHERE id = ?";
            $stmt = $mysqli->prepare($updateLikesQuery);
            $stmt->bind_param("ii", $newLikes, $entryId);

            if ($stmt->execute()) {
                echo $newLikes; // Gebe die aktualisierte Anzahl der Likes zurück
            } else {
                echo "Fehler beim Aktualisieren der Likes.";
            }
        } else {
            echo "Eintrag nicht gefunden.";
        }
    }

    // Datenbankverbindung schließen
    $mysqli->close();
} else {
    echo "Ungültige Anfrage.";
}
?>

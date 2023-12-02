<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["comment_id"]) && isset($_SESSION["username"])) {
    // Verbindung zur Datenbank herstellen (ersetzen Sie die Platzhalter durch Ihre tatsächlichen Daten)
    $hostname = "localhost"; // Hostname
    $username = "root";      // MySQL-Benutzername
    $password = "";          // MySQL-Passwort
    $database = "diary";     // Name der Datenbank

    // Verbindung zur Datenbank herstellen
    $mysqli = new mysqli($hostname, $username, $password, $database);

    // Überprüfen, ob die Verbindung fehlgeschlagen ist
    if ($mysqli->connect_error) {
        die("Verbindung zur MySQL-Datenbank fehlgeschlagen: " . $mysqli->connect_error);
    }

    $commentId = $_GET["comment_id"];
    $username = $_SESSION["username"];

    // Überprüfen, ob der Benutzer diesen Kommentar bereits geliked hat
    $checkLikeQuery = "SELECT id FROM comment_likes WHERE comment_id = ? AND username = ?";
    $stmt = $mysqli->prepare($checkLikeQuery);
    $stmt->bind_param("is", $commentId, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Der Benutzer hat diesen Kommentar noch nicht geliked, füge den Like hinzu
        $insertLikeQuery = "INSERT INTO comment_likes (comment_id, username, like_time) VALUES (?, ?, NOW())";
        $stmt = $mysqli->prepare($insertLikeQuery);
        $stmt->bind_param("is", $commentId, $username);
        $stmt->execute();

        // Aktualisiere die Anzahl der Likes in der ursprünglichen Tabelle
        $getLikesQuery = "SELECT likes FROM comments WHERE id = ?";
        $stmt = $mysqli->prepare($getLikesQuery);
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $currentLikes = $row["likes"];
            $newLikes = $currentLikes + 1;

            // SQL-Abfrage, um die Likes zu aktualisieren
            $updateLikesQuery = "UPDATE comments SET likes = ? WHERE id = ?";
            $stmt = $mysqli->prepare($updateLikesQuery);
            $stmt->bind_param("ii", $newLikes, $commentId);

            if ($stmt->execute()) {
                echo $newLikes; // Gebe die aktualisierte Anzahl der Likes zurück
            } else {
                echo "Fehler beim Aktualisieren der Likes.";
            }
        } else {
            echo "Kommentar nicht gefunden.";
        }
    } else {
        // Der Benutzer hat diesen Kommentar bereits geliked, entferne den Like
        $deleteLikeQuery = "DELETE FROM comment_likes WHERE comment_id = ? AND username = ?";
        $stmt = $mysqli->prepare($deleteLikeQuery);
        $stmt->bind_param("is", $commentId, $username);
        $stmt->execute();

        // Aktualisiere die Anzahl der Likes in der ursprünglichen Tabelle
        $getLikesQuery = "SELECT likes FROM comments WHERE id = ?";
        $stmt = $mysqli->prepare($getLikesQuery);
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $currentLikes = $row["likes"];
            $newLikes = max(0, $currentLikes - 1); // Mindestens 0 Likes

            // SQL-Abfrage, um die Likes zu aktualisieren
            $updateLikesQuery = "UPDATE comments SET likes = ? WHERE id = ?";
            $stmt = $mysqli->prepare($updateLikesQuery);
            $stmt->bind_param("ii", $newLikes, $commentId);

            if ($stmt->execute()) {
                echo $newLikes; // Gebe die aktualisierte Anzahl der Likes zurück
            } else {
                echo "Fehler beim Aktualisieren der Likes.";
            }
        } else {
            echo "Kommentar nicht gefunden.";
        }
    }

    // Datenbankverbindung schließen
    $mysqli->close();
} else {
    echo "Ungültige Anfrage.";
}
?>

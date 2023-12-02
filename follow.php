<?php
session_start();

// Datenbankverbindungsinformationen
$hostname = "localhost";  // Der Hostname Ihrer Datenbank (normalerweise "localhost")
$username = "root";  // Ihr Datenbank-Benutzername
$password = "";  // Ihr Datenbank-Passwort
$database = "diary";  // Der Name Ihrer Datenbank

// Datenbankverbindung herstellen
$conn = mysqli_connect($hostname, $username, $password, $database);

// Überprüfen Sie die Verbindung
if (!$conn) {
    die("Datenbankverbindung fehlgeschlagen: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $follower = $_SESSION["username"];
    $following = $_POST["following_username"];
    
    // Überprüfen, ob der Benutzer bereits folgt
    $sql = "SELECT COUNT(*) FROM follows WHERE follower_username = ? AND following_username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $follower, $following);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            // Der Benutzer folgt bereits; entfernen Sie die Zeile
            $sql = "DELETE FROM follows WHERE follower_username = ? AND following_username = ?";
        } else {
            // Der Benutzer folgt noch nicht; fügen Sie eine Zeile hinzu
            $sql = "INSERT INTO follows (follower_username, following_username) VALUES (?, ?)";
        }

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $follower, $following);
            if (mysqli_stmt_execute($stmt)) {
                echo "Erfolgreich aktualisiert.";
            } else {
                echo "Fehler beim Aktualisieren.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

// Schließen Sie die Datenbankverbindung am Ende Ihres Skripts
mysqli_close($conn);
?>

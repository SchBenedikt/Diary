<?php
session_start();

// Überprüfen, ob der Benutzer bereits angemeldet ist
if (isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredUsername = $_POST["username"];
    $enteredPassword = $_POST["password"];

    // Prüfen, ob der Benutzername bereits vorhanden ist
    $checkUsernameQuery = "SELECT id FROM users WHERE username = ?";
    $checkStmt = $mysqli->prepare($checkUsernameQuery);
    $checkStmt->bind_param("s", $enteredUsername);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Benutzername ist bereits vergeben
        $error = "Benutzername bereits vergeben. Bitte wählen Sie einen anderen Benutzernamen.";
    } else {
        // Benutzername ist verfügbar, fügen Sie den neuen Benutzer in die Datenbank ein
        $hashedPassword = password_hash($enteredPassword, PASSWORD_DEFAULT); // Passwort hashen

        $insertQuery = "INSERT INTO users (username, password) VALUES (?, ?)";
        $insertStmt = $mysqli->prepare($insertQuery);
        $insertStmt->bind_param("ss", $enteredUsername, $hashedPassword);

        if ($insertStmt->execute()) {
            // Registrierung erfolgreich
            $_SESSION["username"] = $enteredUsername;
            header("Location: index.html"); // Weiterleitung zur Dashboard-Seite nach erfolgreicher Registrierung
            exit();
        } else {
            // Fehler beim Hinzufügen des Benutzers
            $error = "Fehler beim Registrieren des Benutzers: " . $mysqli->error;
        }
        
        // Das vorbereitete Statement schließen
        $insertStmt->close();
    }

    // Das vorbereitete Statement schließen
    $checkStmt->close();
}

// Datenbankverbindung schließen
$mysqli->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registrieren</title>
</head>
<link rel="stylesheet" href="style.css">
<body>
    <?php if(isset($error)) { echo "<p style='color: red;'>$error</p>"; } ?>
    <form action="register.php" method="post">
        <h2>Registrieren</h2>
        <label for="username">Benutzername:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Passwort:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <input type="submit" value="Registrieren">
        <p>Bereits registriert? <a href="login.php">Login</a></p>
    </form>
</body>
</html>

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

    // SQL-Abfrage, um den Benutzer mit dem eingegebenen Benutzernamen zu suchen
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $enteredUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Benutzername existiert, prüfe das Passwort
        $row = $result->fetch_assoc();
        $hashedPassword = $row["password"];
        
        if (password_verify($enteredPassword, $hashedPassword)) {
            // Authentifizierung erfolgreich
            $_SESSION["username"] = $row["username"];
            header("Location: index.html"); // Weiterleitung zur Dashboard-Seite nach erfolgreichem Login
            exit();
        } else {
            // Authentifizierung fehlgeschlagen
            $error = "Ungültiges Passwort.";
        }
    } else {
        // Benutzername existiert nicht, füge den Benutzer hinzu
        $hashedPassword = password_hash($enteredPassword, PASSWORD_DEFAULT); // Passwort hashen

        $insertQuery = "INSERT INTO users (username, password) VALUES (?, ?)";
        $insertStmt = $mysqli->prepare($insertQuery);
        $insertStmt->bind_param("ss", $enteredUsername, $hashedPassword);

        if ($insertStmt->execute()) {
            // Registrierung erfolgreich
            $_SESSION["username"] = $enteredUsername;
            header("Location: index.html"); // Weiterleitung zur Dashboard-Seite nach erfolgreicher Registrierung oder Login
            exit();
        } else {
            // Fehler beim Hinzufügen des Benutzers
            $error = "Fehler beim Registrieren des Benutzers: " . $mysqli->error;
        }
        
        // Das vorbereitete Statement schließen
        $insertStmt->close();
    }

    // Das vorbereitete Statement schließen
    $stmt->close();
}

// Datenbankverbindung schließen
$mysqli->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<link rel="stylesheet" href="style.css">
<body>

    <?php if(isset($error)) { echo "<p style='color: red;'>$error</p>"; } ?>
    <form action="login.php" method="post">
    <h2>Login</h2>
        <label for="username">Benutzername:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Passwort:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <input type="submit" value="Einloggen">
        <p>Noch keinen Account? <a href="register.php">Jetzt registrieren</a></p>
    </form>
</body>
</html>

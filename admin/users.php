<?php
session_start();

// Überprüfen, ob der Benutzer angemeldet ist und der Benutzername "admin" ist
if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
    // Benutzer ist authentifiziert, erlaube den Zugriff auf die Seite

    // Hier sollte eine Verbindung zur Datenbank hergestellt werden (Ersetze die Platzhalter mit den tatsächlichen Daten)
    $conn = mysqli_connect("localhost", "root", "", "diary");

    // Überprüfen Sie die Verbindung
    if (!$conn) {
        die("Verbindung zur Datenbank fehlgeschlagen: " . mysqli_connect_error());
    }

    // Überprüfen, ob ein Benutzer zum Löschen markiert wurde
    if (isset($_POST['delete_user'])) {
        $userIdToDelete = $_POST['delete_user'];

        // Vor dem Löschen des Benutzers, alle Likes des Benutzers löschen
        $deleteLikesSql = "DELETE FROM entry_likes WHERE entry_id IN (SELECT id FROM entries WHERE username = (SELECT username FROM users WHERE id = $userIdToDelete))";
        mysqli_query($conn, $deleteLikesSql);

        // Dann alle Beiträge des Benutzers löschen
        $deleteEntriesSql = "DELETE FROM entries WHERE username = (SELECT username FROM users WHERE id = $userIdToDelete)";
        mysqli_query($conn, $deleteEntriesSql);

        // Schließlich den Benutzer löschen
        $deleteUserSql = "DELETE FROM users WHERE id = $userIdToDelete";
        mysqli_query($conn, $deleteUserSql);
    }

    // Überprüfen, ob ein Benutzer zum Bearbeiten markiert wurde
    if (isset($_POST['edit_user'])) {
        $userIdToEdit = $_POST['edit_user'];
        // Hier könntest du zur Bearbeitungsseite weiterleiten oder einen Bearbeitungsdialog öffnen
        // Zum Beispiel: header("Location: edit_user.php?id=$userIdToEdit");
    }

    // SQL-Abfrage, um alle Benutzer abzurufen
    $sql = "SELECT * FROM users";

    $result = mysqli_query($conn, $sql);

    // Datenbankverbindung schließen
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Admin Bereich - Alle Benutzer</title>
    <!-- Weitere Headerinformationen können hinzugefügt werden -->
</head>

<body>
    <h1>Willkommen im Admin-Bereich</h1>
    
    <!-- Hier werden alle Benutzer angezeigt -->
    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div>";
            echo "<p><strong>Benutzername:</strong> " . $row['username'] . "</p>";

            // Löschen-Button hinzufügen
            echo "<form method='post' action=''>";
            echo "<input type='hidden' name='delete_user' value='" . $row['id'] . "'>";
            echo "<input type='submit' value='Benutzer löschen'>";
            echo "</form>";


            echo "</div><hr>";
        }
    } else {
        echo "Keine Benutzer gefunden.";
    }
    ?>
    
    <!-- Der Rest des Inhalts der Admin-Seite -->
</body>

</html>

<?php
} else {
    // Benutzer ist nicht authentifiziert, zeige eine Fehlermeldung und leite ihn nach 3 Sekunden um
    echo "Zugriff verweigert. Nur der Benutzer 'admin' hat Zugriff auf diese Seite.";
    header("refresh:3;url=../login.php"); // Umleitung nach 3 Sekunden
    exit; // Beende das Skript nach der Umleitung
}
?>

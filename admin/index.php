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

    // Überprüfen, ob ein Beitrag zum Löschen markiert wurde
    if (isset($_POST['delete_entry'])) {
        $entryIdToDelete = $_POST['delete_entry'];
        // SQL-Abfrage, um den markierten Beitrag zu löschen
        $deleteSql = "DELETE FROM entries WHERE id = $entryIdToDelete";
        mysqli_query($conn, $deleteSql);
    }

    // Überprüfen, ob ein Beitrag zum Bearbeiten markiert wurde
    if (isset($_POST['edit_entry'])) {
        $entryIdToEdit = $_POST['edit_entry'];
        $editedEntryText = $_POST['edited_entry'];
        
        // SQL-Abfrage, um den Beitrag zu aktualisieren
        $updateSql = "UPDATE entries SET entry_text = '$editedEntryText' WHERE id = $entryIdToEdit";
        mysqli_query($conn, $updateSql);
    }

    // SQL-Abfrage, um alle Beiträge abzurufen
    $sql = "SELECT * FROM entries ORDER BY created_at DESC";

    $result = mysqli_query($conn, $sql);

    // Datenbankverbindung schließen
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Admin Bereich - Alle Beiträge</title>
    <!-- Weitere Headerinformationen können hinzugefügt werden -->
    <style>
        .edit-form {
            display: none;
        }
    </style>
</head>

<body>
    <h1>Willkommen im Admin-Bereich</h1>
    
    <!-- Hier werden alle Beiträge angezeigt -->
    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div>";
            echo "<p><strong>Benutzername:</strong> " . $row['username'] . "</p>";
            echo "<p><strong>Erstellt am:</strong> " . $row['created_at'] . "</p>";
            echo "<p><strong>Beitrag:</strong> " . $row['entry_text'] . "</p>";

            // Lösch-Button hinzufügen
            echo "<form method='post' action=''>";
            echo "<input type='hidden' name='delete_entry' value='" . $row['id'] . "'>";
            echo "<input type='submit' value='Beitrag löschen'>";
            echo "</form>";
            echo "<br>";
            // Bearbeiten-Button hinzufügen
            echo "<button class='edit-button' data-entry-id='" . $row['id'] . "'>Beitrag bearbeiten</button>";

            // Bearbeitungsformular
            echo "<form class='edit-form' method='post' action=''>";
            echo "<input type='hidden' name='edit_entry' value='" . $row['id'] . "'>";
            echo "<textarea name='edited_entry'>" . $row['entry_text'] . "</textarea>";
            echo "<input type='submit' value='Speichern'>";
            echo "</form>";

            echo "</div><hr>";
        }
    } else {
        echo "Keine Beiträge gefunden.";
    }
    ?>

    <script>
        // JavaScript-Code zum Anzeigen/Bearbeiten der Formulare
        const editButtons = document.querySelectorAll('.edit-button');
        const editForms = document.querySelectorAll('.edit-form');

        editButtons.forEach((button, index) => {
            button.addEventListener('click', () => {
                // Verstecke alle Formulare
                editForms.forEach((form) => {
                    form.style.display = 'none';
                });

                // Zeige das ausgewählte Formular an
                editForms[index].style.display = 'block';
            });
        });
    </script>

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

<?php
session_start();

// Hier sollte eine Verbindung zur Datenbank hergestellt werden
$conn = mysqli_connect("localhost", "root", "", "diary");

// Prüfen, ob das Formular abgeschickt wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entry = trim($_POST["entry"]); // Leere Zeichen am Anfang und Ende entfernen

    // Überprüfen, ob der Benutzer angemeldet ist
    if (!isset($_SESSION["username"])) {
        // Benutzer ist nicht angemeldet, leite ihn zu register.php weiter
        header("Location: login.php");
        exit; // Beenden Sie das Skript nach der Weiterleitung
    }

    // Überprüfen, ob die Nachricht leer ist
    if (empty($entry)) {
        echo "Die Nachricht darf nicht leer sein.";
        exit;
    }

    // Überprüfen, ob die Nachricht bereits vorhanden ist
    $sqlCheckDuplicate = "SELECT COUNT(*) FROM entries WHERE entry_text = ? AND username = ?";
    $stmtCheckDuplicate = mysqli_prepare($conn, $sqlCheckDuplicate);
    
    if ($stmtCheckDuplicate) {
        mysqli_stmt_bind_param($stmtCheckDuplicate, "ss", $entry, $_SESSION["username"]);
        mysqli_stmt_execute($stmtCheckDuplicate);
        mysqli_stmt_bind_result($stmtCheckDuplicate, $count);
        mysqli_stmt_fetch($stmtCheckDuplicate);

        if ($count > 0) {
            echo "Diese Nachricht existiert bereits.";
            exit;
        }

        mysqli_stmt_close($stmtCheckDuplicate);
    } else {
        echo "Fehler beim Erstellen des vorbereiteten Statements: " . mysqli_error($conn);
        exit;
    }

    // Verwende ein vorbereitetes Statement, um SQL-Injektionen zu verhindern
    $sql = "INSERT INTO entries (entry_text, username) VALUES (?, ?)";
    
    // Vorbereiten der SQL-Abfrage
    $stmt = mysqli_prepare($conn, $sql);
    
    // Überprüfen, ob das vorbereitete Statement erfolgreich erstellt wurde
    if ($stmt) {
        // Eintragstext und Benutzername binden
        mysqli_stmt_bind_param($stmt, "ss", $entry, $_SESSION["username"]);

        // Suchen nach Schlagworten und sie in die Datenbank einfügen
        preg_match_all('/#(\w+)/', $entry, $matches);
        $keywords = !empty($matches[1]) ? array_unique($matches[1]) : [];

        // Ausführen der vorbereiteten Abfrage
        if (mysqli_stmt_execute($stmt)) {
            $entry_id = mysqli_insert_id($conn);

            // Füge Schlagworte hinzu
            foreach ($keywords as $keyword) {
                $sqlKeyword = "INSERT INTO keywords (entry_id, keyword) VALUES (?, ?)";
                $stmtKeyword = mysqli_prepare($conn, $sqlKeyword);
                
                if ($stmtKeyword) {
                    mysqli_stmt_bind_param($stmtKeyword, "ss", $entry_id, $keyword);
                    mysqli_stmt_execute($stmtKeyword);
                    mysqli_stmt_close($stmtKeyword);
                } else {
                    echo "Fehler beim Hinzufügen des Schlagworts: " . mysqli_error($conn);
                }
            }

            // Hochladen von Bildern
            if (!empty($_FILES['image']['name'])) {
                $imageFileName = basename($_FILES['image']['name']);
                $targetPath = "uploads/" . $imageFileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    // Bild erfolgreich hochgeladen, speichere den Dateinamen in der Datenbank
                    $sqlUpdateImage = "UPDATE entries SET image_filename = ? WHERE id = ?";
                    $stmtUpdateImage = mysqli_prepare($conn, $sqlUpdateImage);

                    if ($stmtUpdateImage) {
                        mysqli_stmt_bind_param($stmtUpdateImage, "ss", $imageFileName, $entry_id);
                        mysqli_stmt_execute($stmtUpdateImage);
                        mysqli_stmt_close($stmtUpdateImage);

                        echo "Bild erfolgreich hochgeladen: " . $imageFileName;
                    } else {
                        echo "Fehler beim Aktualisieren des Bildnamens in der Datenbank: " . mysqli_error($conn);
                    }
                } else {
                    echo "Fehler beim Hochladen des Bildes.";
                }
            }

            // CSS-Stiles for the success message
            echo '<style>
                .keyword-container {
                    display: inline-block;
                    margin: 5px;
                    padding: 5px;
                    background-color: #f2f2f2;
                    border-radius: 5px;
                }
            </style>';

            // Ausgabe der verlinkten Keywords
            echo '<div>';
            foreach ($keywords as $keyword) {
                echo '<span class="keyword-container"><a href="#" onmouseover="showKeywordEntries(\''.$keyword.'\')" onmouseout="hideKeywordEntries()">'.$keyword.'</a></span>';
            }
            echo '</div>';

            // Erfolgsmeldung
            echo '<div style="color: green;"><b>Nachricht erfolgreich gesendet!</b><br></br></div>';

            
            // (Der restliche Code bleibt unverändert)
        } else {
            echo "Fehler beim Hinzufügen des Eintrags: " . mysqli_error($conn);
        }

        // Das vorbereitete Statement schließen
        mysqli_stmt_close($stmt);
    } else {
        echo "Fehler beim Erstellen des vorbereiteten Statements: " . mysqli_error($conn);
    }

    // Schließen Sie die Datenbankverbindung
    mysqli_close($conn);
}
?>

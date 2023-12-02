<?php
// Hier sollte eine Verbindung zur Datenbank hergestellt werden
$conn = mysqli_connect("localhost", "root", "", "diary");

// Überprüfen Sie die Verbindung
if (!$conn) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . mysqli_connect_error());
}

// Benutzername aus der URL holen
$requestedUsername = isset($_GET['username']) ? $_GET['username'] : '';

// SQL-Abfrage, um die neuesten 100 Einträge des angegebenen Benutzers abzurufen
$sql = "SELECT * FROM entries WHERE username = '$requestedUsername' ORDER BY created_at DESC LIMIT 100";

$result = mysqli_query($conn, $sql);

$diaryEntries = array();

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Überprüfen, ob der Eintrag Keywords mit # enthält
        if (strpos($row['entry_text'], '#') !== false) {
            // Extrahiere die Keywords mit # und erstelle Links
            $entryKeywords = [];
            preg_match_all('/#(\w+)/', $row['entry_text'], $matches);
            if (!empty($matches[1])) {
                $entryKeywords = $matches[1];
            }

            // Ersetze die Keywords durch Links zu schaechner.de
            foreach ($entryKeywords as $keyword) {
                $link = '<a href="/diary/keyword.html?keyword=' . $keyword . '" style="text-decoration: none; color: black; font-weight: bold;">#' . $keyword . '</a>';
                $row['entry_text'] = str_replace('#' . $keyword, $link, $row['entry_text']);
            }
        }

        // Verlinke Benutzernamen, wenn ein @ gefolgt von einem Benutzernamen steht
        $row['entry_text'] = preg_replace('/@(\w+)/', '<a href="profile.html?username=$1" style="text-decoration: none; color: black; font-weight: bold;">@$1</a>', $row['entry_text']);

        // Überprüfen, ob ein Bild vorhanden ist
        if (!empty($row['image_filename'])) {
            $imagePath = 'uploads/' . $row['image_filename'];
            // Füge eine Zeile vor dem Bild hinzu
            $row['entry_text'] .= "<br><br><a href='$imagePath' target='_blank'><img src='$imagePath' alt='Bild' width='200'></a>"; // Hier wird die Breite auf 200 Pixel festgelegt
        }

        $diaryEntries[] = $row;
    }
}

// Daten in JSON umwandeln und ausgeben
header('Content-Type: application/json');
echo json_encode($diaryEntries);

// Datenbankverbindung schließen
mysqli_close($conn);
?>

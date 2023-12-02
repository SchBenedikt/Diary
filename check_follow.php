<?php
session_start();

// Hier sollte eine Verbindung zur Datenbank hergestellt werden
$conn = mysqli_connect("localhost", "root", "", "diary");

if (!$conn) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $usernameToCheck = mysqli_real_escape_string($conn, $_GET['username']);
    $currentUsername = $_SESSION['username'];

    // Überprüfen, ob der Benutzer dem anderen Benutzer bereits folgt
    $query = "SELECT * FROM followers WHERE follower_username = '$currentUsername' AND following_username = '$usernameToCheck'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['isFollowing' => true]);
    } else {
        echo json_encode(['isFollowing' => false]);
    }
}

mysqli_close($conn);
?>

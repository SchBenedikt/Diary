<?php
session_start();

// Session löschen und zurück zur Login-Seite weiterleiten
session_destroy();
header("Location: login.php");
exit();
?>

<?php

function DB_Connect($databasenavn)
{
    $servername = "db";
    $username = "root";
    $password = "strong(!)Pass";
    $dbname = $databasenavn;

    global $forbindelse;
    $forbindelse = mysqli_connect($servername, $username, $password, $dbname);

    if (!$forbindelse) {
        die("Ingen forbindelse: " . mysqli_connect_error());
    }

    // Change character set to UTF-8
    mysqli_set_charset($forbindelse, "utf8");
}
?>

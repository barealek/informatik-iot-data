<?php
include_once "db.php";
DB_Connect("iot_opsamling");

$result = mysqli_query($forbindelse, "SELECT * FROM devices");
while ($row = mysqli_fetch_assoc($result)) {
    $state =
        $row["state"] == 2
            ? "Tæt på"
            : ($row["state"] == 1
                ? "I nærheden"
                : "Væk");
    echo $row["name"] . ": " . $row["uuid"] . " - " . $state . "<br>";
}

<script>
setTimeout(() => {
window.reload();
}, 3000)
</script>

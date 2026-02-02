<?php
include_once "db.php";
DB_Connect("iot_opsamling");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? "";
    $uuid = $_POST["uuid"] ?? "";

    if ($name && $uuid) {
        $ch = curl_init("http://server:8080/devices");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["name" => $name, "uuid" => $uuid]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_exec($ch);
        curl_close($ch);
        header("Location: index.php");
        exit;
    }
}

?>

<form method="POST">
    <input type="text" name="name" placeholder="Navn" required>
    <input type="text" name="uuid" placeholder="UUID" required>
    <button type="submit">Tilføj enhed</button>
</form>


<div id="devices">
<?php
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
?>
</div>

<button onclick="location.reload()">Opdater</button>

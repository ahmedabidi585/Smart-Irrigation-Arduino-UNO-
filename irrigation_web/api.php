<?php
// ============================================================
//  Smart Irrigation — API PHP
//  Fichier : htdocs/irrigation/api.php
//  URL     : http://localhost/irrigation/api.php
// ============================================================

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

// ── Connexion MySQL ────────────────────────────────────────
$host = "localhost";
$user = "root";
$pass = "";
$db   = "smart_irrigation";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "DB connexion échouée: " . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8mb4");

$action = $_GET["action"] ?? "latest";

// ── GET : dernière mesure ──────────────────────────────────
if ($action === "latest") {
    $result = $conn->query("
        SELECT * FROM sensor_data
        ORDER BY id DESC LIMIT 1
    ");
    $row = $result->fetch_assoc();
    if ($row) {
        // Ajouter statuts automatiques
        $row["status_sol"]  = getStatus($row["soil"],        30, 70);
        $row["status_eau"]  = getStatus($row["water"],       20, 85);
        $row["status_temp"] = getStatus($row["temperature"], 15, 35);
        $row["status_hum"]  = getStatus($row["humidity"],    30, 80);
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Pas de données"]);
    }
}

// ── GET : historique (30 dernières mesures) ────────────────
elseif ($action === "history") {
    $result = $conn->query("
        SELECT temperature, humidity, soil, water, created_at
        FROM sensor_data
        ORDER BY id DESC LIMIT 30
    ");
    $rows = [];
    while ($r = $result->fetch_assoc()) $rows[] = $r;
    echo json_encode(array_reverse($rows));
}

// ── GET : paramètres ──────────────────────────────────────
elseif ($action === "settings") {
    $result = $conn->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
    echo json_encode($result->fetch_assoc());
}

// ── POST : envoyer commande à ISIS ────────────────────────
elseif ($action === "command" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    $cmd   = $conn->real_escape_string($input["command"] ?? "");
    $val   = $conn->real_escape_string($input["value"]   ?? "");

    if ($cmd && $val !== "") {
        $conn->query("INSERT INTO commands (command, value) VALUES ('$cmd', '$val')");
        echo json_encode(["ok" => true, "command" => "$cmd:$val"]);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Paramètres manquants"]);
    }
}

// ── POST : modifier seuils ────────────────────────────────
elseif ($action === "seuils" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    $sol   = intval($input["seuil_sol"] ?? 40);
    $eau   = intval($input["seuil_eau"] ?? 30);

    $conn->query("UPDATE settings SET seuil_sol=$sol, seuil_eau=$eau");

    // Envoyer aussi à ISIS via la table commands
    $conn->query("INSERT INTO commands (command, value) VALUES ('SEUIL_S', '$sol')");
    $conn->query("INSERT INTO commands (command, value) VALUES ('SEUIL_W', '$eau')");

    echo json_encode(["ok" => true]);
}

else {
    http_response_code(404);
    echo json_encode(["error" => "Action inconnue"]);
}

$conn->close();

// ── Fonction statut ────────────────────────────────────────
function getStatus($val, $min, $max) {
    if ($val < $min) return "BAS";
    if ($val > $max) return "ÉLEVÉ";
    return "OK";
}
?>

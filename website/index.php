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
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Enheder - Oversigt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
        }

        h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
            margin-bottom: 10px;
        }

        .subtitle {
            color: rgba(255,255,255,0.8);
            font-size: 1.1rem;
        }

        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px 30px;
            border-bottom: 1px solid #e9ecef;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"] {
            padding: 14px 18px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        input[type="text"]::placeholder {
            color: #adb5bd;
        }

        button {
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #495057;
            border: 2px solid #e9ecef;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }

        .device-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .device-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .device-item:hover {
            background: white;
            border-color: #e9ecef;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transform: translateX(5px);
        }

        .device-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 18px;
            flex-shrink: 0;
        }

        .device-icon.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .device-icon.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .device-icon.orange {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .device-info {
            flex: 1;
            min-width: 0;
        }

        .device-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: #212529;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .device-uuid {
            font-size: 0.875rem;
            color: #6c757d;
            font-family: 'SF Mono', Monaco, monospace;
        }

        .device-status {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .status-away {
            background: #fff3cd;
            color: #856404;
        }

        .status-nearby {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-close {
            background: #d4edda;
            color: #155724;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .status-away .status-dot {
            background: #856404;
        }

        .status-nearby .status-dot {
            background: #0c5460;
        }

        .status-close .status-dot {
            background: #155724;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .actions {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        footer {
            text-align: center;
            color: rgba(255,255,255,0.6);
            font-size: 0.875rem;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📡 IoT Enheder</h1>
            <p class="subtitle">Administrer dine IoT-enheder og deres status</p>
        </header>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    Tilføj ny enhed
                </h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Navn</label>
                            <input type="text" id="name" name="name" placeholder="F.eks. Stue-sensor" required>
                        </div>
                        <div class="form-group">
                            <label for="uuid">UUID</label>
                            <input type="text" id="uuid" name="uuid" placeholder="F.eks. a1b2c3d4..." required>
                        </div>
                        <button type="submit" class="btn-primary">
                            <span>➕</span>
                            Tilføj
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <span>📋</span>
                    Enhedsoversigt
                </h2>
            </div>
            <div class="card-body">
                <div id="devices" class="device-list">
                <?php
                $result = mysqli_query($forbindelse, "SELECT * FROM devices");
                $hasDevices = false;
                while ($row = mysqli_fetch_assoc($result)) {
                    $hasDevices = true;
                    
                    // Determine status class and text
                    if ($row["state"] == 2) {
                        $statusClass = "status-close";
                        $statusText = "Tæt på";
                        $iconClass = "green";
                        $icon = "📍";
                    } elseif ($row["state"] == 1) {
                        $statusClass = "status-nearby";
                        $statusText = "I nærheden";
                        $iconClass = "orange";
                        $icon = "📡";
                    } else {
                        $statusClass = "status-away";
                        $statusText = "Væk";
                        $iconClass = "blue";
                        $icon = "📡";
                    }
                    ?>
                    <div class="device-item">
                        <div class="device-icon <?php echo $iconClass; ?>">
                            <?php echo $icon; ?>
                        </div>
                        <div class="device-info">
                            <div class="device-name"><?php echo htmlspecialchars($row["name"]); ?></div>
                            <div class="device-uuid"><?php echo htmlspecialchars($row["uuid"]); ?></div>
                        </div>
                        <div class="device-status <?php echo $statusClass; ?>">
                            <span class="status-dot"></span>
                            <?php echo $statusText; ?>
                        </div>
                    </div>
                    <?php
                }
                
                if (!$hasDevices) {
                    ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <p>Ingen enheder fundet</p>
                        <p style="font-size: 0.875rem; margin-top: 5px;">Tilføj din første enhed ovenfor</p>
                    </div>
                    <?php
                }
                ?>
                </div>
            </div>
        </div>

        <div class="actions">
            <button onclick="location.reload()" class="btn-secondary">
                <span>🔄</span>
                Opdater
            </button>
        </div>

        <footer>
            <p>IoT Opsamling System &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>
</body>
</html>

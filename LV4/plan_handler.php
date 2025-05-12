<?php
session_start();
header('Content-Type: application/json'); 

if (!isset($_SESSION['ID'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
    exit;
}

require_once 'includes/db.php'; 

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$user_id = $_SESSION['ID'];
$response = ['success' => false];

if ($action === 'add') {
    $weather_data_id = isset($_POST['weather_data_id']) ? (int)$_POST['weather_data_id'] : null;

    if (!$weather_data_id) {
        $response['message'] = 'Weather data ID not provided.';
        echo json_encode($response);
        exit;
    }

    $stmt_check_weather = $conn->prepare("SELECT temperature, weather_type FROM weather_data WHERE id = ?");
    $extreme_weather_warning = null;
    $user_email = null; 

    if ($stmt_check_weather) {
        $stmt_check_weather->bind_param("i", $weather_data_id);
        $stmt_check_weather->execute();
        $result_weather_check = $stmt_check_weather->get_result();
        $weather_details = $result_weather_check->fetch_assoc();
        $stmt_check_weather->close();

        if ($weather_details && $weather_details['temperature'] > 35) { 
            $extreme_weather_warning = "Upozorenje: Temperatura za odabrani dan (" . htmlspecialchars($weather_details['temperature']) . "°C) je ekstremno visoka!";
            
           /*  
            $stmt_user_email = $conn->prepare("SELECT email FROM users WHERE ID = ?"); 
                $stmt_user_email->bind_param("i", $user_id);
                $stmt_user_email->execute();
                $result_user_email = $stmt_user_email->get_result();
                if($user_row = $result_user_email->fetch_assoc()){
                    $user_email = $user_row['email'];
                }
                $stmt_user_email->close();
            }

            if ($user_email) {
                $subject = "Upozorenje o ekstremnim vremenskim uvjetima za planirani izlet";
                $message_body = "Poštovani,\n\nPokušali ste planirati izlet za dan s ekstremnim vremenskim uvjetima:\nTemperatura: " . $weather_details['temperature'] . "°C.\nMolimo Vas da razmotrite promjenu plana.\n\nLijep pozdrav,\nVaša Aplikacija";
                $headers = "From: no-reply@vasaaplikacija.com" . "\r\n" . 
                           "Reply-To: no-reply@vasaaplikacija.com" . "\r\n" .
                           "X-Mailer: PHP/" . phpversion();
                
                // mail($user_email, $subject, $message_body, $headers); 
                // mail() function often requires server configuration. For local dev, it might not work out of the box.
                // Consider logging or just relying on the UI warning for now if mail setup is an issue.
                error_log("Mail attempt to $user_email: $subject"); // Log attempt
            } */
        }
    }


    $stmt_check = $conn->prepare("SELECT id FROM planned_trip WHERE user_id = ? AND weather_data_id = ?");
    $stmt_check->bind_param("ii", $user_id, $weather_data_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        $response['message'] = 'Ovaj dan je već u vašem planu.';
        $response['already_exists'] = true;
        if ($extreme_weather_warning) $response['warning'] = $extreme_weather_warning;
    } else {
        $stmt_check->close(); 
        $stmt_insert = $conn->prepare("INSERT INTO planned_trip (user_id, weather_data_id) VALUES (?, ?)");
        if ($stmt_insert) {
            $stmt_insert->bind_param("ii", $user_id, $weather_data_id);
            if ($stmt_insert->execute()) {
                $response['success'] = true;
                $response['plan_id'] = $stmt_insert->insert_id; 
                $response['message'] = 'Dan uspješno dodan u plan.';
                if ($extreme_weather_warning) $response['warning'] = $extreme_weather_warning;
            } else {
                $response['message'] = 'Greška pri spremanju u plan: ' . $stmt_insert->error;
            }
            $stmt_insert->close();
        } else {
            $response['message'] = 'Greška pri pripremi upita za spremanje: ' . $conn->error;
        }
    }


} elseif ($action === 'remove') {
    $plan_id = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : null;

    if (!$plan_id) {
        $response['message'] = 'Plan ID nije pružen.';
        echo json_encode($response);
        exit;
    }

    $stmt_delete = $conn->prepare("DELETE FROM planned_trip WHERE id = ? AND user_id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("ii", $plan_id, $user_id);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Dan uklonjen iz plana.';
            } else {
                $response['message'] = 'Zapis nije pronađen ili nemate ovlasti za brisanje.';
            }
        } else {
            $response['message'] = 'Greška pri brisanju iz plana: ' . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $response['message'] = 'Greška pri pripremi upita za brisanje: ' . $conn->error;
    }
} else {
    $response['message'] = 'Nepoznata akcija.';
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
echo json_encode($response);
exit;
?>
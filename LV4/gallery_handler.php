<?php
session_start();
require_once 'includes/db.php'; 

header('Content-Type: application/json'); 

if (!isset($_SESSION['ID'])) {
    echo json_encode(['success' => false, 'message' => 'Korisnik nije prijavljen.']);
    exit;
}
$user_id = $_SESSION['ID'];

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {
    case 'get_images_with_ratings':
        $sql = "SELECT s.id, s.naziv_datoteke, s.putanja, s.opis, s.izvor, s.api_image_id,
                       COALESCE(AVG(o.ocjena), 0) as average_rating, 
                       COUNT(o.id) as total_ratings,
                       (SELECT ocjena FROM ocjene WHERE id_slika = s.id AND id_korisnik = ?) as user_rating
                FROM slike s
                LEFT JOIN ocjene o ON s.id = o.id_slika
                WHERE s.izvor = 'lokalno' OR s.izvor IS NULL
                GROUP BY s.id
                ORDER BY s.uploaded_at DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $images = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $images[] = $row;
            }
            mysqli_stmt_close($stmt);
            echo json_encode($images);
        } else {
            error_log("SQL Error in get_images_with_ratings: " . mysqli_error($conn));
            echo json_encode(['success' => false, 'message' => 'Greška pri dohvaćanju slika.']);
        }
        break;

    case 'fetch_api_images':
        $unsplash_access_key = 'xl8vyQP0HQ-yKznA8msi-6r0yh6V7EFCYaaN2oQnqoI';
        $api_url = "https://api.unsplash.com/photos/random?count=6&client_id=" . $unsplash_access_key;
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ["Accept-Version: v1"]
        ]);
        $api_response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code != 200 || $api_response === false) {
            error_log("Unsplash API error. HTTP Code: " . $http_code . ". Response: " . $api_response);
            echo json_encode(['success' => false, 'message' => 'Greška pri dohvaćanju slika s Unsplash API-ja. HTTP status: ' . $http_code]);
            exit;
        }

        $api_images_data = json_decode($api_response);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($api_images_data)) {
            error_log("Unsplash API JSON decode error: " . json_last_error_msg() . ". Response: " . $api_response);
            echo json_encode(['success' => false, 'message' => 'Greška pri parsiranju odgovora s Unsplash API-ja.']);
            exit;
        }
        
        $processed_api_images = [];
        foreach ($api_images_data as $api_image) {
            $api_img_id_val = $api_image->id;
            $api_img_url = $api_image->urls->regular ?? $api_image->urls->small;
            $api_img_desc = $api_image->alt_description ?? $api_image->description ?? 'Slika s Unsplash API-ja';

            $local_image_id = null;

            $stmt_find = mysqli_prepare($conn, "SELECT id FROM slike WHERE api_image_id = ?");
            if(!$stmt_find) { error_log("Prepare failed (find): " . mysqli_error($conn)); continue; }
            mysqli_stmt_bind_param($stmt_find, "s", $api_img_id_val);
            mysqli_stmt_execute($stmt_find);
            $result_find = mysqli_stmt_get_result($stmt_find);
            if ($found_row = mysqli_fetch_assoc($result_find)) {
                $local_image_id = $found_row['id'];
            }
            mysqli_stmt_close($stmt_find);

            if (!$local_image_id) {
                $stmt_insert = mysqli_prepare($conn, "INSERT INTO slike (naziv_datoteke, putanja, opis, izvor, api_image_id, uploaded_at) VALUES (?, ?, ?, 'api', ?, NOW())");
                if(!$stmt_insert) { error_log("Prepare failed (insert): " . mysqli_error($conn)); continue; }
                $naziv_datoteke_api = "api_" . $api_img_id_val . ".jpg";
                mysqli_stmt_bind_param($stmt_insert, "ssss", $naziv_datoteke_api, $api_img_url, $api_img_desc, $api_img_id_val);
                if (mysqli_stmt_execute($stmt_insert)) {
                    $local_image_id = mysqli_insert_id($conn);
                } else {
                    error_log("Insert API image failed: " . mysqli_stmt_error($stmt_insert));
                }
                mysqli_stmt_close($stmt_insert);
            }

            if ($local_image_id) {
                $rating_data = get_average_rating_for_image($conn, $local_image_id, $user_id);
                $processed_api_images[] = [
                    'id' => $local_image_id,
                    'naziv_datoteke' => "api_" . $api_img_id_val . ".jpg",
                    'putanja' => $api_img_url,
                    'opis' => $api_img_desc,
                    'izvor' => 'api',
                    'api_image_id' => $api_img_id_val,
                    'average_rating' => $rating_data['average_rating'],
                    'total_ratings' => $rating_data['total_ratings'],
                    'user_rating' => $rating_data['user_rating']
                ];
            }
        }
        echo json_encode($processed_api_images);
        break;

    case 'rate_image':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Nedozvoljena metoda.']);
            exit;
        }
        $id_slika = isset($_POST['id_slika']) ? (int)$_POST['id_slika'] : 0;
        $ocjena = isset($_POST['ocjena']) ? (int)$_POST['ocjena'] : 0;

        if ($id_slika <= 0 || $ocjena < 1 || $ocjena > 5) {
            echo json_encode(['success' => false, 'message' => 'Neispravni podaci za ocjenu.']);
            exit;
        }

        $sql_check = "SELECT id FROM ocjene WHERE id_korisnik = ? AND id_slika = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $id_slika);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        $message = '';
        $success = false;

        if (mysqli_stmt_num_rows($stmt_check) > 0) { 
            mysqli_stmt_close($stmt_check); 
            $sql_update = "UPDATE ocjene SET ocjena = ?, vrijeme_ocjene = CURRENT_TIMESTAMP WHERE id_korisnik = ? AND id_slika = ?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "iii", $ocjena, $user_id, $id_slika);
            if (mysqli_stmt_execute($stmt_update)) {
                $success = true;
                $message = 'Ocjena ažurirana.';
            } else {
                $message = 'Greška pri ažuriranju ocjene: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_update);
        } else { 
            mysqli_stmt_close($stmt_check); 
            $sql_insert = "INSERT INTO ocjene (id_korisnik, id_slika, ocjena) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "iii", $user_id, $id_slika, $ocjena);
            if (mysqli_stmt_execute($stmt_insert)) {
                $success = true;
                $message = 'Ocjena spremljena.';
            } else {
                $message = 'Greška pri spremanju ocjene: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_insert);
        }
        
        if ($success) {
            $new_avg_data = get_average_rating_for_image($conn, $id_slika, $user_id);
            echo json_encode(['success' => true, 'message' => $message, 'average_rating' => $new_avg_data['average_rating'], 'total_ratings' => $new_avg_data['total_ratings']]);
        } else {
            echo json_encode(['success' => false, 'message' => $message]);
        }
        break;
    
    case 'upload_image':
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nemate ovlasti za ovu akciju.']);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Nedozvoljena metoda.']);
            exit;
        }

        $opis = trim($_POST['opis'] ?? '');
        $target_dir = "slike/"; 
        if (!file_exists($target_dir) && !is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); 
        }

        if (isset($_FILES['slika_datoteka']) && $_FILES['slika_datoteka']['error'] == 0) {
            $file = $_FILES['slika_datoteka'];
            $fileName = basename($file["name"]);
            $target_file = $target_dir . $fileName;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png'];
            $max_size = 5 * 1024 * 1024; 

            if (!in_array($imageFileType, $allowed_types)) {
                echo json_encode(['success' => false, 'message' => 'Dozvoljeni formati su JPG, JPEG, PNG.']);
                exit;
            }
            if ($file["size"] > $max_size) {
                echo json_encode(['success' => false, 'message' => 'Slika je prevelika. Maksimalna veličina je 5MB.']);
                exit;
            }

            $new_filename = $fileName;
            $counter = 1;
            while (file_exists($target_dir . $new_filename)) {
                $name_without_ext = pathinfo($fileName, PATHINFO_FILENAME);
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $new_filename = $name_without_ext . "_" . $counter . "." . $extension;
                $counter++;
            }
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $sql_img_insert = "INSERT INTO slike (naziv_datoteke, putanja, opis, izvor, uploaded_at) VALUES (?, ?, ?, 'lokalno', NOW())";
                $stmt_img_insert = mysqli_prepare($conn, $sql_img_insert);
                mysqli_stmt_bind_param($stmt_img_insert, "sss", $new_filename, $target_file, $opis);
                if (mysqli_stmt_execute($stmt_img_insert)) {
                    echo json_encode(['success' => true, 'message' => 'Slika uspješno učitana i spremljena.']);
                } else {
                    unlink($target_file);
                    echo json_encode(['success' => false, 'message' => 'Greška pri spremanju podataka o slici u bazu: ' . mysqli_error($conn)]);
                }
                mysqli_stmt_close($stmt_img_insert);
            } else {
                echo json_encode(['success' => false, 'message' => 'Greška pri premještanju učitane datoteke.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Nije odabrana datoteka ili je došlo do greške pri učitavanju. Error code: ' . ($_FILES['slika_datoteka']['error'] ?? 'N/A')]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Nepoznata akcija.']);
        break;
}

function get_average_rating_for_image($db_conn, $image_id, $current_user_id) {
    $sql_avg = "SELECT COALESCE(AVG(o.ocjena), 0) as average_rating, 
                       COUNT(o.id) as total_ratings,
                       (SELECT ocjena FROM ocjene WHERE id_slika = ? AND id_korisnik = ?) as user_rating
                FROM ocjene o 
                WHERE o.id_slika = ?";
    $stmt_avg = mysqli_prepare($db_conn, $sql_avg);
    if (!$stmt_avg) {
        error_log("Prepare failed (get_average_rating_for_image): " . mysqli_error($db_conn));
        return ['average_rating' => 0, 'total_ratings' => 0, 'user_rating' => null];
    }
    mysqli_stmt_bind_param($stmt_avg, "iii", $image_id, $current_user_id, $image_id);
    mysqli_stmt_execute($stmt_avg);
    $result_avg = mysqli_stmt_get_result($stmt_avg);
    $avg_data = mysqli_fetch_assoc($result_avg);
    mysqli_stmt_close($stmt_avg);

    if (!$avg_data) {
        return ['average_rating' => 0, 'total_ratings' => 0, 'user_rating' => null];
    }
    return $avg_data;
}

if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
?>
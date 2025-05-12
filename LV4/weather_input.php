<?php
session_start();
if (!isset($_SESSION['ID']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require_once 'includes/db.php'; 

$greska = '';
$uspjeh = '';
$action = $_GET['action'] ?? 'list'; 
$record_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

$form_mode = 'add'; 
$current_record = [
    'id' => null,
    'date' => '',
    'location' => '',
    'temperature' => '',
    'precipitation' => '',
    'weather_type' => '',
    'season' => ''
];

if ($action === 'delete' && $record_id) {
    $stmt_delete = mysqli_prepare($conn, "DELETE FROM weather_data WHERE id = ?");
    mysqli_stmt_bind_param($stmt_delete, "i", $record_id);
    if (mysqli_stmt_execute($stmt_delete)) {
        mysqli_stmt_close($stmt_delete);
        header("Location: weather_input.php?status=deleted");
        exit;
    } else {
        $greska = "Greška pri brisanju zapisa: " . mysqli_error($conn);
    }
    if(isset($stmt_delete)) mysqli_stmt_close($stmt_delete);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
    $datum = $_POST['date'];
    $lokacija = trim($_POST['location']);
    $temperatura = $_POST['temperature'];
    $oborine = $_POST['precipitation'];
    $vrijeme_tip = $_POST['weather_type'];
    $sezona = $_POST['season'];

    $current_record = $_POST;
    $current_record['id'] = $posted_id; 

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum)) {
        $greska = "Neispravan format datuma.";
    } elseif (!in_array($vrijeme_tip, ['Sunny', 'Rainy', 'Cloudy', 'Snowy'])) {
        $greska = "Neispravan tip vremena.";
    } elseif (!in_array($sezona, ['Summer', 'Autumn', 'Winter', 'Spring'])) {
        $greska = "Neispravna sezona.";
    } elseif (empty($lokacija) || !preg_match('/^[a-zA-Z0-9\sčćđšžČĆĐŠŽ]+$/u', $lokacija)) {
        $greska = "Lokacija može sadržavati samo slova, brojeve i hrvatske znakove.";
    } elseif (!is_numeric($temperatura) || $temperatura < -50 || $temperatura > 60) {
        $greska = "Temperatura mora biti između -50 i 60 °C.";
    } elseif (!is_numeric($oborine) || $oborine < 0 || $oborine > 500) {
        $greska = "Oborine moraju biti u rasponu 0 - 500 mm.";
    }

    if (empty($greska)) {
        if ($posted_id) { 
            $stmt = mysqli_prepare($conn, "UPDATE weather_data SET date = ?, location = ?, temperature = ?, precipitation = ?, weather_type = ?, season = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssddssi", $datum, $lokacija, $temperatura, $oborine, $vrijeme_tip, $sezona, $posted_id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: weather_input.php?status=updated");
                exit;
            } else {
                $greska = "Greška pri ažuriranju zapisa: " . mysqli_error($conn);
            }
        } else { 
            $stmt = mysqli_prepare($conn, "INSERT INTO weather_data (date, location, temperature, precipitation, weather_type, season) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssddss", $datum, $lokacija, $temperatura, $oborine, $vrijeme_tip, $sezona);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: weather_input.php?status=added");
                exit;
            } else {
                $greska = "Greška pri unosu u bazu: " . mysqli_error($conn);
            }
        }
        if(isset($stmt)) mysqli_stmt_close($stmt);
    }
    $form_mode = $posted_id ? 'edit' : 'add';
    $record_id = $posted_id; 
}


if ($action === 'edit' && $record_id && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt_fetch = mysqli_prepare($conn, "SELECT id, date, location, temperature, precipitation, weather_type, season FROM weather_data WHERE id = ?");
    mysqli_stmt_bind_param($stmt_fetch, "i", $record_id);
    mysqli_stmt_execute($stmt_fetch);
    $result_fetch = mysqli_stmt_get_result($stmt_fetch);
    $fetched_data = mysqli_fetch_assoc($result_fetch);
    mysqli_stmt_close($stmt_fetch);

    if ($fetched_data) {
        $current_record = $fetched_data;
        $form_mode = 'edit';
    } else {
        $greska = "Zapis s ID " . htmlspecialchars($record_id) . " nije pronađen.";
        $action = 'list'; 
        $form_mode = 'add'; 
    }
} elseif ($action === 'add_form') { 
    $form_mode = 'add';
}


$records = [];
$sql_list = "SELECT id, date, location, temperature, precipitation, weather_type, season FROM weather_data ORDER BY date DESC, id DESC";
$result_list = mysqli_query($conn, $sql_list);
if ($result_list) {
    while ($row = mysqli_fetch_assoc($result_list)) {
        $records[] = $row;
    }
    mysqli_free_result($result_list);
} else {
    $greska .= (empty($greska) ? '' : ' ') . "Greška pri dohvaćanju zapisa za listu: " . mysqli_error($conn);
}

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'deleted') $uspjeh = "Zapis uspješno obrisan.";
    if ($_GET['status'] == 'updated') $uspjeh = "Zapis uspješno ažuriran.";
    if ($_GET['status'] == 'added') $uspjeh = "Zapis uspješno dodan.";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Upravljanje vremenskim zapisima</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .action-links a { margin-right: 10px; }
        .form-section { margin-bottom: 30px; padding: 20px; border: 1px solid #ccc; background-color:#f9f9f9; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

<h1>Upravljanje vremenskim zapisima</h1>

<?php if ($greska): ?><div class="message error"><?php echo htmlspecialchars($greska); ?></div><?php endif; ?>
<?php if ($uspjeh): ?><div class="message success"><?php echo htmlspecialchars($uspjeh); ?></div><?php endif; ?>

<?php if ($form_mode === 'add' || $form_mode === 'edit'): ?>
<div class="form-section">
    <h2><?php echo ($form_mode === 'edit') ? 'Uredi zapis (ID: ' . htmlspecialchars($current_record['id']) . ')' : 'Novi vremenski zapis'; ?></h2>
    <form method="POST" action="weather_input.php<?php echo ($form_mode === 'edit' && $current_record['id']) ? '?action=edit&id=' . htmlspecialchars($current_record['id']) : ''; ?>">
        <?php if ($form_mode === 'edit' && $current_record['id']): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($current_record['id']); ?>">
        <?php endif; ?>

        <label>Datum:</label><br>
        <input type="date" name="date" value="<?php echo htmlspecialchars($current_record['date']); ?>" required><br><br>

        <label>Lokacija:</label><br>
        <input type="text" name="location" value="<?php echo htmlspecialchars($current_record['location']); ?>" required><br><br>

        <label>Temperatura (°C):</label><br>
        <input type="number" step="0.1" name="temperature" value="<?php echo htmlspecialchars($current_record['temperature']); ?>" required><br><br>

        <label>Oborine (mm):</label><br>
        <input type="number" step="0.1" name="precipitation" value="<?php echo htmlspecialchars($current_record['precipitation']); ?>" required><br><br>

        <label>Tip vremena:</label><br>
        <select name="weather_type" required>
            <option value="">-- Odaberi --</option>
            <option value="Sunny" <?php echo ($current_record['weather_type'] == 'Sunny') ? 'selected' : ''; ?>>Sunčano</option>
            <option value="Rainy" <?php echo ($current_record['weather_type'] == 'Rainy') ? 'selected' : ''; ?>>Kišno</option>
            <option value="Cloudy" <?php echo ($current_record['weather_type'] == 'Cloudy') ? 'selected' : ''; ?>>Oblačno</option>
            <option value="Snowy" <?php echo ($current_record['weather_type'] == 'Snowy') ? 'selected' : ''; ?>>Snježno</option>
        </select><br><br>

        <label>Sezona:</label><br>
        <select name="season" required>
            <option value="">-- Odaberi --</option>
            <option value="Spring" <?php echo ($current_record['season'] == 'Spring') ? 'selected' : ''; ?>>Proljeće</option>
            <option value="Summer" <?php echo ($current_record['season'] == 'Summer') ? 'selected' : ''; ?>>Ljeto</option>
            <option value="Autumn" <?php echo ($current_record['season'] == 'Autumn') ? 'selected' : ''; ?>>Jesen</option>
            <option value="Winter" <?php echo ($current_record['season'] == 'Winter') ? 'selected' : ''; ?>>Zima</option>
        </select><br><br>

        <button type="submit"><?php echo ($form_mode === 'edit') ? 'Ažuriraj zapis' : 'Spremi zapis'; ?></button>
        <?php if ($form_mode === 'edit'): ?>
            <a href="weather_input.php" style="margin-left: 10px;">Odustani / Dodaj novi</a>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>


<h2>Postojeći vremenski zapisi</h2>
<?php if ($form_mode !== 'add' && $form_mode !== 'edit'): ?>
    <p><a href="weather_input.php?action=add_form">Dodaj novi zapis</a></p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Datum</th>
            <th>Lokacija</th>
            <th>Temp (°C)</th>
            <th>Oborine (mm)</th>
            <th>Tip vremena</th>
            <th>Sezona</th>
            <th>Akcije</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($records)): ?>
            <tr>
                <td colspan="8">Nema unesenih zapisa.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($records as $rec): ?>
                <tr>
                    <td><?php echo htmlspecialchars($rec['id']); ?></td>
                    <td><?php echo htmlspecialchars($rec['date']); ?></td>
                    <td><?php echo htmlspecialchars($rec['location']); ?></td>
                    <td><?php echo htmlspecialchars($rec['temperature']); ?></td>
                    <td><?php echo htmlspecialchars($rec['precipitation']); ?></td>
                    <td><?php echo htmlspecialchars($rec['weather_type']); ?></td>
                    <td><?php echo htmlspecialchars($rec['season']); ?></td>
                    <td class="action-links">
                        <a href="weather_input.php?action=edit&id=<?php echo $rec['id']; ?>">Uredi</a>
                        <a href="weather_input.php?action=delete&id=<?php echo $rec['id']; ?>" onclick="return confirm('Jeste li sigurni da želite obrisati ovaj zapis?');">Obriši</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<p><a href="admin.php">Natrag na admin stranicu</a></p>

<?php
if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
?>
</body>
</html>

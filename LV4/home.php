<?php
session_start();

if (!isset($_SESSION['ID'])) {
    header("Location: index.php");
    exit;
}

require_once 'includes/db.php'; 

$weather_records = [];
$sql = "SELECT id, location, date, season, weather_type, temperature, precipitation FROM weather_data";

$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $weather_records[] = $row;
        }
    }
    $result->free(); 
} else {
    error_log("SQL Error in home.php: " . $conn->error);
}

$user_plan = [];
$user_id = $_SESSION['ID'];
$sql_plan = "SELECT p.id as plan_id, wd.id as weather_data_id, wd.location, wd.date, wd.season, wd.weather_type, wd.temperature, wd.precipitation
             FROM planned_trip p
             JOIN weather_data wd ON p.weather_data_id = wd.id
             WHERE p.user_id = ?";
$stmt_plan = $conn->prepare($sql_plan);
if ($stmt_plan) {
    $stmt_plan->bind_param("i", $user_id);
    $stmt_plan->execute();
    $result_plan = $stmt_plan->get_result();
    while ($row = $result_plan->fetch_assoc()) {
        $user_plan[] = $row;
    }
    $stmt_plan->close();
} else {
    error_log("SQL Error in home.php (planirani_izleti): " . $conn->error);
}

$conn->close(); 
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dobrodošli</title>
    <link rel="stylesheet" href="style.css">
    <script>
        const initialWeatherData = <?php echo json_encode($weather_records); ?>;
        const initialUserPlan = <?php echo json_encode($user_plan); ?>;
    </script>
</head>
<body>
    <header>
    <h2>Dobrodošao, <?php echo htmlspecialchars($_SESSION['Username']); ?>!</h2>
    <p>Ovdje ćeš moći upravljati svojim izletima.</p>
    <nav>
    <a href="gallery.php">Galerija</a>
    <a href="logout.php">Odjavi se</a>
    </nav>
    </header>
    <div id="filteri" style="margin-top: 20px;">
    <select id="filter-season">
    <option value="">-- Odaberi sezonu --</option>
    <option value="Spring">Spring</option>
    <option value="Summer">Summer</option>
    <option value="Autumn">Autumn</option>
    <option value="Winter">Winter</option>
    </select>
    <fieldset>
        <legend>Weather Type:</legend>
        <div>
            <label for="weather-all">All</label>

            <input type="radio" id="weather-all" name="weather-type" value="" checked>
        </div>
        <div>
            <label for="weather-sunny">Sunny</label>

            <input type="radio" id="weather-sunny" name="weather-type" value="Sunny">
        </div>
        <div>
            <label for="weather-snowy">Snowy</label>

            <input type="radio" id="weather-snowy" name="weather-type" value="Snowy">
        </div>
        <div>
            <label for="weather-rainy">Rainy</label>

            <input type="radio" id="weather-rainy" name="weather-type" value="Rainy">
        </div>
        <div>
            <label for="weather-cloudy">Cloudy</label>

            <input type="radio" id="weather-cloudy" name="weather-type" value="Cloudy">
        </div>
    </fieldset>
    <p>Odaberi raspon temperature:</p>
    <div>
        <label for="filter-temperature-min">Min:</label>
        <input type="range" id="filter-temperature-min" min="-20" max="40" step="1" value="-20">
        <span id="temperature-min-value">-20</span> °C
    </div>
    <div>
        <label for="filter-temperature-max">Max:</label>
        <input type="range" id="filter-temperature-max" min="-20" max="40" step="1" value="40">
        <span id="temperature-max-value">40</span> °C
    </div>
    <button id="primijeni-filtere" style="margin-bottom: 10px;">Filtriraj</button>
    </div>
    

		<table id="weather-data" class="weather-table">
		</table>

        <button id="add-to-plan" style="margin-top: 10px;">Add Selected Days to Plan</button>

    <div id="plan-section" style="margin-top: 30px;">
        <h2>Your Plan</h2>
        <div id="plan-feedback" style="margin-bottom:10px;"></div>
        <table id="plan-table" class="weather-table">
        </table>
        <p id="plan-message" style="margin-top: 10px;"></p>
        <button id="preview-plan" style="margin-top: 10px; margin-bottom: 20px;">Preview Plan</button>
    </div>
    <script src="script.js"></script> 
</body>
</html>

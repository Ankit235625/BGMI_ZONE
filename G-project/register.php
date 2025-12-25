<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login to register for tournaments
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=register.php?id=" . (isset($_GET['id']) ? intval($_GET['id']) : ''));
    exit();
}

$tournament_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = "";

// Fetch Tournament Details
$t_query = "SELECT * FROM tournaments WHERE id = $tournament_id";
$t_result = $conn->query($t_query);

if ($t_result->num_rows == 0) {
    die("<div class='container section text-center'><h2>Tournament Not Found</h2></div>");
}

$tournament = $t_result->fetch_assoc();
$type = $tournament['type']; // solo, duo, squad

// Check if registration time is up
$current_time = new DateTime();
$tournament_date = new DateTime($tournament['date']);
$is_expired = $current_time > $tournament_date;

// Check if user is already registered
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$is_already_registered = false;
if($user_id > 0) {
    $check_reg = $conn->query("SELECT id, payment_status FROM registrations WHERE tournament_id = $tournament_id AND user_id = $user_id");
    if($check_reg->num_rows > 0) {
        $reg_data = $check_reg->fetch_assoc();
        if ($reg_data['payment_status'] == 'pending') {
            header("Location: payment.php?id=" . $reg_data['id']);
            exit();
        }
        $is_already_registered = true;
    }
}

// Check if slots are full
$slots_query = $conn->query("SELECT COUNT(*) as registered FROM registrations WHERE tournament_id = $tournament_id AND payment_status = 'completed'");
$slots_data = $slots_query->fetch_assoc();
$registered_count = $slots_data['registered'];
$max_slots = $tournament['max_slots'];
$is_full = ($registered_count >= $max_slots);

if ($is_expired || $tournament['status'] != 'open') {
   $message = "<div class='alert error' style='font-size: 1.2rem; border-color: red;'>⛔ Registration Closed for this Tournament</div>";
   $disable_form = true;
} else if ($is_full) {
    $message = "<div class='alert error' style='font-size: 1.2rem; border-color: red;'>🚫 REGISTRATION FULL - All $max_slots slots are taken!</div>";
    $disable_form = true;
} else if ($is_already_registered) {
    $message = "<div class='alert success' style='font-size: 1.1rem;'>✅ You are already registered for this tournament! <br><a href='profile.php' style='color:inherit; text-decoration:underline;'>View your team here</a></div>";
    $disable_form = true;
} else {
    $disable_form = false;
}

// Determine Number of Players
$player_count = 1;
if ($type == 'duo') $player_count = 2;
if ($type == 'squad') $player_count = 4;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    // Final check for duplicates
    if($user_id > 0) {
        $check_reg = $conn->query("SELECT id FROM registrations WHERE tournament_id = $tournament_id AND user_id = $user_id");
        if($check_reg->num_rows > 0) {
            die("Error: Duplicate registration attempt.");
        }
    }

    $team_name = isset($_POST['team_name']) ? $conn->real_escape_string($_POST['team_name']) : '';
    $captain_discord = $conn->real_escape_string($_POST['contact']);
    
    // Collect all players
    $players = [];
    for ($i = 1; $i <= $player_count; $i++) {
        $p_name = $_POST["player_{$i}_name"];
        $p_id = $_POST["player_{$i}_id"];
        $players[] = ["name" => $p_name, "bgmi_id" => $p_id];
    }

    // Captain is Player 1
    $captain_name = $conn->real_escape_string($players[0]['name']);
    
    // For Solo, auto-team name
    if ($type == 'solo') {
        $team_name = "Solo_" . $captain_name;
    }
    
    // JSON encode all details
    $player_details = $conn->real_escape_string(json_encode($players));

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "NULL";

    // Determine Payment Status
    $payment_status = ($tournament['entry_fee'] == 'Free' || $tournament['entry_fee'] == 'free' || empty($tournament['entry_fee'])) ? 'completed' : 'pending';

    $sql = "INSERT INTO registrations (tournament_id, user_id, team_name, captain_name, captain_discord, player_details, payment_status) 
            VALUES ($tournament_id, $user_id, '$team_name', '$captain_name', '$captain_discord', '$player_details', '$payment_status')";

    if ($conn->query($sql) === TRUE) {
        $reg_id = $conn->insert_id;
        if ($payment_status == 'pending') {
            header("Location: payment.php?id=$reg_id");
            exit();
        } else {
            $message = "<div class='alert success'>Registration successful! GLHF 🎮</div>";
        }
    } else {
        $message = "<div class='alert error'>Error: " . $conn->error . "</div>";
    }
}

include 'includes/header.php';
?>

<div class="container section">
    <div class="form-container">
        <h2 class="section-title text-center" style="margin-bottom: 1rem;">
            Register for <span class="text-primary"><?php echo htmlspecialchars($tournament['title']); ?></span>
        </h2>
        <div class="text-center" style="margin-bottom: 2rem;">
            <span class="badge badge-<?php echo $type; ?>" style="font-size: 1rem;"><?php echo ucfirst($type); ?> Format</span>
        </div>

        <?php echo $message; ?>
        
        <?php if(!$disable_form): ?>
        <form method="POST" action="">
            
            <?php if($type != 'solo'): ?>
            <div class="form-group fade-in">
                <label>Team Name</label>
                <input type="text" name="team_name" required class="form-control" placeholder="Enter Clan/Team Name">
            </div>
            <?php endif; ?>

            <div class="form-group fade-in">
                <label>Contact Number / Discord ID</label>
                <input type="text" name="contact" required class="form-control" value="<?php echo isset($_SESSION['phone']) ? htmlspecialchars($_SESSION['phone']) : ''; ?>" placeholder="For organizing matches">
            </div>

            <h4 class="text-primary" style="margin: 20px 0; border-bottom: 1px solid #333; padding-bottom: 10px;">Player Details</h4>

            <?php for($i = 1; $i <= $player_count; $i++): ?>
                <div class="player-group fade-in" style="animation-delay: <?php echo $i * 0.1; ?>s;">
                    <h5 style="margin-bottom: 10px; color: var(--secondary-color);">Player <?php echo $i; ?> <?php echo ($i==1) ? '(Captain)' : ''; ?></h5>
                    <div class="input-row">
                        <?php 
                        // Auto-fill for Captain (Player 1)
                        $val_name = ($i == 1 && isset($_SESSION['username'])) ? htmlspecialchars($_SESSION['username']) : '';
                        $val_id = ($i == 1 && isset($_SESSION['bgmi_id'])) ? htmlspecialchars($_SESSION['bgmi_id']) : '';
                        $readonly_attr = ($i == 1 && isset($_SESSION['username'])) ? 'readonly style="background:#222; cursor:not-allowed;"' : '';
                        ?>
                        <input type="text" name="player_<?php echo $i; ?>_name" required class="form-control" value="<?php echo $val_name; ?>" <?php echo $readonly_attr; ?> placeholder="In-Game Name (IGN)">
                        <input type="text" name="player_<?php echo $i; ?>_id" required class="form-control" value="<?php echo $val_id; ?>" <?php echo $readonly_attr; ?> placeholder="BGMI ID (e.g. 512345678)">
                    </div>
                </div>
            <?php endfor; ?>
            
            <div style="margin-top: 30px;" class="fade-in">
                <button type="submit" class="btn" style="width: 100%;">Confirm Registration</button>
            </div>
        </form>
        <?php else: ?>
            <div class="text-center">
                <a href="tournaments.php" class="btn" style="margin-top: 20px;">Back to Tournaments</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Page Background */
body {
    background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.9)), url('https://wallpaperaccess.com/full/6202720.jpg');
    background-size: cover;
    background-attachment: fixed;
    background-position: center;
}

.form-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 3rem;
    background: rgba(10, 10, 10, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 180, 0, 0.3); /* Gold border */
    border-radius: 0;
    position: relative;
    box-shadow: 0 0 50px rgba(0,0,0,0.8);
    clip-path: polygon(
        20px 0, 100% 0, 
        100% calc(100% - 20px), calc(100% - 20px) 100%, 
        0 100%, 0 20px
    );
}

/* Corner Accents */
.form-container::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 40px; height: 40px;
    border-top: 3px solid var(--primary-color);
    border-left: 3px solid var(--primary-color);
}
.form-container::after {
    content: '';
    position: absolute;
    bottom: 0; right: 0;
    width: 40px; height: 40px;
    border-bottom: 3px solid var(--primary-color);
    border-right: 3px solid var(--primary-color);
}

.section-title {
    font-size: 2rem;
    color: #fff;
    text-transform: uppercase;
    font-style: italic;
    border-bottom: 2px solid var(--primary-color);
    display: inline-block;
    padding-bottom: 10px;
    margin-bottom: 30px;
}

/* Form Groups */
.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--primary-color);
    font-family: var(--font-headers);
    letter-spacing: 1px;
    font-size: 0.9rem;
}

.form-control {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid #333;
    padding: 15px;
    color: #fff;
    font-family: var(--font-subheaders);
    font-size: 1rem;
    transition: 0.3s;
}

.form-control:focus {
    background: rgba(0,0,0,0.5);
    border-color: var(--primary-color);
    box-shadow: 0 0 15px rgba(255, 180, 0, 0.2);
}

/* Player Cards */
.player-group {
    background: linear-gradient(90deg, rgba(255, 180, 0, 0.05) 0%, transparent 100%);
    border-left: 3px solid var(--primary-color);
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
    transition: 0.3s;
}

.player-group:hover {
    background: linear-gradient(90deg, rgba(255, 180, 0, 0.1) 0%, transparent 100%);
    transform: translateX(5px);
}

.player-group h5 {
    color: #fff;
    font-size: 1.1rem;
    margin-bottom: 15px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 10px;
}
.player-group h5::before {
    content: '';
    display: inline-block;
    width: 8px; height: 8px;
    background: var(--primary-color);
    transform: rotate(45deg);
}

.input-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .input-row { grid-template-columns: 1fr; }
    .form-container { padding: 1.5rem; width: 95%; }
}

/* Animations */
.fade-in { animation: slideInRight 0.5s ease-out forwards; opacity: 0; }
@keyframes slideInRight {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

.alert { margin-bottom: 20px; padding: 15px; border-radius: 4px; text-align: center; font-weight: bold; }
.success { background: rgba(0, 255, 0, 0.1); color: #00ff00; border: 1px solid #00ff00; }
.error { background: rgba(255, 0, 0, 0.1); color: #ff4444; border: 1px solid #ff4444; }
</style>

<?php include 'includes/footer.php'; ?>

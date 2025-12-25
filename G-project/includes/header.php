<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global Security Check: Disable users on the fly
if (isset($_SESSION['user_id'])) {
    include_once __DIR__ . '/db.php';
    $check_uid = $_SESSION['user_id'];
    $check_res = $conn->query("SELECT status FROM users WHERE id = $check_uid");
    if ($check_res && $user_check = $check_res->fetch_assoc()) {
        if ($user_check['status'] === 'disabled') {
            session_unset();
            session_destroy();
            header("Location: login.php?error=disabled");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGMI Tournaments</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/badges.css">
    <!-- Google Fonts: Orbitron & Rajdhani for Headers, Inter for Body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Orbitron:wght@400;600;800&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <div class="container navbar-content">
        <a href="index.php" class="logo">BGMI<span class="text-primary">ZONE</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="tournaments.php">Tournaments</a></li>
            <?php if(isset($_SESSION['user_id'])): 
                // Fetch Unread Notifications Count
                $uid = $_SESSION['user_id'];
                include_once 'db.php'; // Ensure connection
                $notif_count_res = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = $uid AND is_read = 0");
                $notif_count = $notif_count_res->fetch_assoc()['count'];
            ?>
                <li style="position: relative;">
                    <a href="profile.php" title="Notifications">
                        🔔 <?php if($notif_count > 0): ?><span class="notif-badge"><?php echo $notif_count; ?></span><?php endif; ?>
                    </a>
                </li>
                <li class="dropdown">
                    <div class="profile-pill">
                        <div class="user-avatar">👤</div>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <span class="dropdown-arrow">▼</span>
                    </div>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="profile.php">Registrations</a></li>
                        <li class="divider"></li>
                        <li><a href="logout.php" class="logout-link">Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php" class="btn signup-btn">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<style>
.navbar {
    background-color: rgba(15, 15, 15, 0.95);
    border-bottom: 1px solid rgba(0, 255, 136, 0.1);
    padding: 1.5rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    backdrop-filter: blur(10px);
}

.navbar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-family: var(--font-primary);
    font-size: 1.8rem;
    font-weight: 900;
    letter-spacing: 2px;
}

.nav-links {
    list-style: none;
    display: flex;
    gap: 2rem;
}

.nav-links a {
    text-transform: uppercase;
    font-weight: 600;
    font-size: 0.9rem;
    letter-spacing: 1px;
    padding: 0.5rem 0;
    position: relative;
}

.nav-links a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--primary-color);
    transition: var(--transition);
}

.nav-links a:hover::after {
    width: 100%;
}

.nav-links a:hover {
    color: var(--primary-color);
    text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
}

.profile-pill {
    background: rgba(255, 180, 0, 0.1);
    border: 1px solid rgba(255, 180, 0, 0.3);
    padding: 5px 12px 5px 5px !important;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: 0.3s;
    cursor: pointer;
}

.dropdown-arrow {
    font-size: 0.6rem;
    color: var(--primary-color);
    margin-left: 2px;
}

.dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: #1a1a1a;
    border: 1px solid rgba(255, 180, 0, 0.2);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    list-style: none;
    padding: 10px 0;
    min-width: 180px;
    margin-top: 15px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: 0.3s;
    z-index: 1001;
}

.dropdown-menu::before {
    content: '';
    position: absolute;
    top: -6px;
    right: 20px;
    width: 10px;
    height: 10px;
    background: #1a1a1a;
    border-left: 1px solid rgba(255, 180, 0, 0.2);
    border-top: 1px solid rgba(255, 180, 0, 0.2);
    transform: rotate(45deg);
}

.dropdown:hover .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-menu li a {
    padding: 10px 20px !important;
    display: block !important;
    text-transform: none !important;
    font-size: 0.85rem !important;
    letter-spacing: 0.5px !important;
    color: #ccc !important;
    transition: 0.2s;
}

.dropdown-menu li a:hover {
    background: rgba(255, 180, 0, 0.1);
    color: var(--primary-color) !important;
}

.dropdown-menu .divider {
    height: 1px;
    background: rgba(255,255,255,0.05);
    margin: 5px 0;
}

.logout-link {
    color: #ff4444 !important;
}

.profile-pill:hover {
    background: rgba(255, 180, 0, 0.2);
    border-color: var(--primary-color);
    box-shadow: 0 0 15px rgba(255, 180, 0, 0.2);
}

.user-avatar {
    width: 30px;
    height: 30px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000;
    font-size: 1rem;
    font-weight: bold;
}

.user-name {
    color: #fff !important;
    font-family: var(--font-headers);
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.signup-btn {
    padding: 5px 15px !important;
    font-size: 0.8rem !important;
    border: 1px solid var(--primary-color) !important;
}

/* Notification Badge */
.notif-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4444;
    color: #fff;
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 50%;
    font-weight: bold;
    border: 2px solid #0f0f0f;
    line-height: normal;
}

@media (max-width: 768px) {
    .navbar-content {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

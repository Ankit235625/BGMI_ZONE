<?php
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get User Data
$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_sql);
$user_data = $user_result->fetch_assoc();

// Get Registration History
$reg_sql = "SELECT r.*, t.title as tournament_title, t.date as tournament_date, t.prize_pool, t.entry_fee, t.type as tournament_type, t.image_url, t.room_id, t.room_password 
           FROM registrations r 
           JOIN tournaments t ON r.tournament_id = t.id 
           WHERE r.user_id = $user_id AND r.payment_status = 'completed'
           ORDER BY r.created_at DESC";
$reg_result = $conn->query($reg_sql);

// Get Notifications
$notif_sql = "SELECT n.*, t.title as tournament_title 
             FROM notifications n 
             JOIN tournaments t ON n.tournament_id = t.id 
             WHERE n.user_id = $user_id 
             ORDER BY n.created_at DESC LIMIT 5";
$notif_result = $conn->query($notif_sql);

// Mark as read simulation (optional: you could do this here)
// $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
?>

<div class="container section">
    <div class="profile-header fade-in">
        <h2 class="section-title">User <span class="text-primary">Profile</span></h2>
        <div class="user-stats">
            <div class="stat-card">
                <span class="label">In-Game Name</span>
                <span class="value"><?php echo htmlspecialchars($user_data['username']); ?></span>
            </div>
            <div class="stat-card">
                <span class="label">BGMI ID</span>
                <span class="value"><?php echo htmlspecialchars($user_data['bgmi_id']); ?></span>
            </div>
            <div class="stat-card">
                <span class="label">Email</span>
                <span class="value"><?php echo htmlspecialchars($user_data['email']); ?></span>
            </div>
        </div>
    </div>

    <!-- Notifications Section -->
    <div class="notifications-section fade-in" style="margin-top: 30px; animation-delay: 0.1s;">
        <h3 class="section-title" style="font-size: 1.2rem; margin-bottom: 15px;">Inbox <span class="text-primary">& Alerts</span></h3>
        <div class="notif-container">
            <?php if ($notif_result->num_rows > 0): ?>
                <?php while ($n = $notif_result->fetch_assoc()): ?>
                    <div class="notif-item <?php echo $n['is_read'] ? '' : 'unread'; ?>">
                        <div class="notif-icon">🛡️</div>
                        <div class="notif-content">
                            <div class="notif-text"><?php echo $n['message']; ?></div>
                            <div class="notif-time"><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-notif">No new messages from admins.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="tournament-history fade-in" style="margin-top: 50px; animation-delay: 0.2s;">
        <h2 class="section-title">Registered <span class="text-primary">Tournaments</span></h2>
        
        <?php if ($reg_result->num_rows > 0): ?>
            <div class="history-list">
                <?php while ($reg = $reg_result->fetch_assoc()): ?>
                    <div class="history-item">
                        <div class="item-img" style="background-image: url('<?php echo !empty($reg['image_url']) ? $reg['image_url'] : 'assets/css/bg.jpg'; ?>')"></div>
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($reg['tournament_title']); ?></h3>
                            <div class="meta">
                                <span>📅 <?php echo date('d M | H:i', strtotime($reg['tournament_date'])); ?></span>
                                <span class="badge badge-<?php echo $reg['tournament_type']; ?>"><?php echo ucfirst($reg['tournament_type']); ?></span>
                                <span style="color: var(--primary-color);">🏆 <?php echo $reg['prize_pool']; ?> (<?php echo htmlspecialchars($reg['entry_fee']); ?>)</span>
                            </div>
                            <div class="team">Registered as: <strong><?php echo htmlspecialchars($reg['team_name']); ?></strong></div>
                            
                            <?php if(!empty($reg['room_id'])): ?>
                                <div class="room-details fade-in" style="margin-top: 15px; padding: 15px; background: rgba(0, 255, 136, 0.05); border: 1px dashed var(--primary-color);">
                                    <div style="font-size: 0.8rem; color: #888; text-transform: uppercase; margin-bottom: 5px;">Tournament Access</div>
                                    <div style="display: flex; gap: 20px;">
                                        <div>Room ID: <strong style="color: #fff;"><?php echo htmlspecialchars($reg['room_id']); ?></strong></div>
                                        <div>Password: <strong style="color: #fff;"><?php echo htmlspecialchars($reg['room_password']); ?></strong></div>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--primary-color); margin-top: 5px;">⚠️ Do not share these details with anyone!</div>
                                </div>
                            <?php else: ?>
                                <div style="margin-top: 10px; font-size: 0.85rem; color: #666;">⏳ Room ID/Pass will be shared here 30 mins before match.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center" style="padding: 40px; background: rgba(255,255,255,0.05); border-radius: 8px;">
                <p style="color: #888;">You haven't registered for any tournaments yet.</p>
                <a href="tournaments.php" class="btn" style="margin-top: 20px;">Browse Tournaments</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.profile-header {
    background: rgba(10, 10, 10, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 180, 0, 0.3);
    padding: 30px;
    clip-path: polygon(20px 0, 100% 0, 100% calc(100% - 20px), calc(100% - 20px) 100%, 0 100%, 0 20px);
}

.user-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

/* Notifications UI */
.notif-container {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid #222;
    border-radius: 4px;
    overflow: hidden;
}
.notif-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #222;
    transition: 0.3s;
    background: rgba(0,0,0,0.3);
}
.notif-item.unread {
    border-left: 4px solid var(--primary-color);
    background: rgba(255, 180, 0, 0.05);
}
.notif-item:hover { background: rgba(255,255,255,0.05); }
.notif-icon { font-size: 1.2rem; }
.notif-text { color: #ddd; font-size: 0.95rem; line-height: 1.4; }
.notif-text strong { color: var(--primary-color); }
.notif-time { font-size: 0.75rem; color: #666; margin-top: 5px; }
.no-notif { padding: 20px; color: #555; text-align: center; font-style: italic; }

.stat-card {
    background: rgba(255, 180, 0, 0.05);
    border-left: 3px solid var(--primary-color);
    padding: 15px;
}

.stat-card .label {
    display: block;
    color: #888;
    font-size: 0.8rem;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.stat-card .value {
    color: #fff;
    font-family: var(--font-headers);
    font-size: 1.2rem;
}

.history-list {
    display: grid;
    gap: 20px;
}

.history-item {
    background: rgba(10, 10, 10, 0.8);
    border: 1px solid #333;
    display: flex;
    overflow: hidden;
    transition: 0.3s;
}

.history-item:hover {
    border-color: var(--primary-color);
    transform: translateX(10px);
}

.item-img {
    width: 200px;
    background-size: cover;
    background-position: center;
}

.item-details {
    padding: 20px;
    flex-grow: 1;
}

.item-details h3 {
    margin-bottom: 10px;
    font-family: var(--font-headers);
    color: #fff;
}

.item-details .team {
    margin-top: 15px;
    font-size: 0.9rem;
    color: #888;
}

@media (max-width: 600px) {
    .history-item { flex-direction: column; }
    .item-img { width: 100%; height: 150px; }
}
</style>

<?php include 'includes/footer.php'; ?>

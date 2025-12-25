<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<div class="container section">
    <h2 class="section-title">All Tournaments</h2>
    
    <!-- Filters -->
    <div class="filters text-center">
        <?php 
        $type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';
        ?>
        <a href="tournaments.php?type=all" class="btn btn-filter <?php echo $type_filter == 'all' ? 'active' : ''; ?>">All</a>
        <a href="tournaments.php?type=solo" class="btn btn-filter <?php echo $type_filter == 'solo' ? 'active' : ''; ?>">Solo</a>
        <a href="tournaments.php?type=duo" class="btn btn-filter <?php echo $type_filter == 'duo' ? 'active' : ''; ?>">Duo</a>
        <a href="tournaments.php?type=squad" class="btn btn-filter <?php echo $type_filter == 'squad' ? 'active' : ''; ?>">Squad</a>
    </div>

    <div class="grid">
        <?php
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $completed_ids = [];
        $pending_regs = []; // maps tournament_id => registration_id
        
        if($user_id > 0) {
            $reg_check = $conn->query("SELECT id, tournament_id, payment_status FROM registrations WHERE user_id = $user_id");
            while($r = $reg_check->fetch_assoc()) {
                if ($r['payment_status'] == 'completed') {
                    $completed_ids[] = $r['tournament_id'];
                } else {
                    $pending_regs[$r['tournament_id']] = $r['id'];
                }
            }
        }

        $sql = "SELECT * FROM tournaments";
        if($type_filter != 'all') {
            $safe_type = $conn->real_escape_string($type_filter);
            $sql .= " WHERE type='$safe_type'";
        }
        $sql .= " ORDER BY FIELD(type, 'solo', 'duo', 'squad'), created_at DESC";
        
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $is_registered = in_array($row['id'], $completed_ids);
                $pending_id = isset($pending_regs[$row['id']]) ? $pending_regs[$row['id']] : 0;
                
                // Get slot count
                $slot_q = $conn->query("SELECT COUNT(*) as cnt FROM registrations WHERE tournament_id = {$row['id']} AND payment_status = 'completed'");
                $slot_data = $slot_q->fetch_assoc();
                $registered_slots = $slot_data['cnt'];
                $max_slots = $row['max_slots'];
                $is_full = ($registered_slots >= $max_slots);
                
                $statusColor = 'text-primary'; // Default
                if($row['status'] == 'closed') $statusColor = 'text-muted';
                if($row['status'] == 'ongoing') $statusColor = 'text-warning';
                if($row['status'] == 'open') $statusColor = 'text-success'; // Added for open status
                ?>
                <div class="card">
                    <div class="card-image" style="background-image: url('<?php echo !empty($row['image_url']) ? $row['image_url'] : 'https://placehold.co/600x400/1a1a1a/00ff88?text=BGMI+Tournament'; ?>');">
                        <span class="badge badge-<?php echo $row['type']; ?> overlay-badge"><?php echo ucfirst($row['type']); ?></span>
                        <?php if($is_registered): ?>
                            <span class="badge badge-registered overlay-badge" style="left: auto; right: 10px; background: #00ff88; color: #000;">Registered ✅</span>
                        <?php elseif($pending_id > 0): ?>
                            <span class="badge overlay-badge" style="left: auto; right: 10px; background: #ffcc00; color: #000;">Payment Pending ⏳</span>
                        <?php elseif($is_full): ?>
                            <span class="badge overlay-badge" style="left: auto; right: 10px; background: #ff4444; color: #fff;">FULL 🚫</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <div class="meta" style="margin-bottom: 0.5rem;">
                            <span>📅 <?php echo date('M d | H:i', strtotime($row['date'])); ?></span>
                            <span class="<?php echo $statusColor; ?>"><?php echo ucfirst($row['status']); ?></span>
                        </div>
                        <div class="meta">
                            <span style="color: #fff;">Entry: <span class="text-primary"><?php echo htmlspecialchars($row['entry_fee']); ?></span></span>
                            <span style="color: #fff;">Prize: <span class="text-primary"><?php echo htmlspecialchars($row['prize_pool']); ?></span></span>
                        </div>
                        <div class="meta" style="margin-top: 5px;">
                            <span style="<?php echo $is_full ? 'color: #ff4444;' : 'color: #00ff88;'; ?> font-weight: bold;">
                                Slots: <?php echo $registered_slots; ?>/<?php echo $max_slots; ?>
                                <?php echo $is_full ? '(FULL)' : ''; ?>
                            </span>
                        </div>
                        
                        <?php 
                        $is_expired = new DateTime() > new DateTime($row['date']);
                        if($is_registered):
                        ?>
                            <a href="profile.php" class="btn btn-secondary" style="background: rgba(0, 255, 136, 0.1); color: #00ff88; border-color: #00ff88;">View Team</a>
                        <?php elseif($pending_id > 0): ?>
                            <a href="payment.php?id=<?php echo $pending_id; ?>" class="btn" style="background: #ffcc00; color: #000; border: none;">Finish Payment</a>
                        <?php elseif($row['status'] == 'open' && !$is_expired): ?>
                            <a href="register.php?id=<?php echo $row['id']; ?>" class="btn">Register Team</a>
                        <?php else: ?>
                            <button class="btn" disabled style="opacity: 0.5; cursor: not-allowed; border-color: #555;"><?php echo $is_expired ? 'Time Up' : 'Registration Closed'; ?></button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No tournaments found for this category.</p>";
        }
        ?>
    </div>
</div>

<style>
.text-warning { color: #ffcc00; }
.prize {
    font-size: 1.1rem;
    font-weight: bold;
    margin-bottom: 1rem;
    color: #fff;
}
</style>

<?php include 'includes/footer.php'; ?>

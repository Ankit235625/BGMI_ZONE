<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<!-- Hero Section -->
<header class="hero">
    <div class="container hero-content text-center">
        <h1 class="hero-title">Dominate the <span class="text-primary">Battleground</span></h1>
        <p class="hero-subtitle">BATTLEGROUNDS MOBILE INDIA OFFICIAL TOURNAMENTS</p>
        
        <div style="margin-top: 40px;">
            <a href="tournaments.php" class="btn">View Tournaments</a>
        </div>
    </div>
</header>

<!-- Neon News Ticker -->
<div class="ticker-wrap">
    <div class="ticker">
        <div class="ticker-item">📢 Welcome to BGMI ZONE.</div>
        <div class="ticker-item">🏆 Paid Tournaments are now available.</div>
        <div class="ticker-item">⚠️ Registration for tournaments are now open.</div>
        <div class="ticker-item">🔥 Winner gets Big prize.</div>
    </div>
</div>

<style>
.hero {
    height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(rgba(15, 15, 15, 0.8), rgba(15, 15, 15, 0.95)), url('https://w0.peakpx.com/wallpaper/814/99/HD-wallpaper-bgmi-battleground-mobile-india-pubg-pubg-mobile.jpg'); /* Placeholder BGMI Image */
    background-size: cover;
    background-position: center;
    position: relative;
    border-bottom: 2px solid var(--primary-color);
}

.hero-title {
    font-size: 4rem;
    margin-bottom: 1rem;
    text-shadow: 0 0 20px rgba(0, 0, 0, 0.8);
}

.hero-subtitle {
    font-size: 1.2rem;
    color: var(--text-muted);
    margin-bottom: 2rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

@media (max-width: 768px) {
    .hero-title { font-size: 2.5rem; }
    .hero { height: 60vh; }
}
</style>

<!-- Featured Tournaments -->
<section class="section container">
    <h2 class="section-title">Featured Tournaments</h2>
    
    <div class="grid">
        <?php
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $completed_ids = [];
        $pending_regs = []; // maps tournament_id => reg_id
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

        $sql = "SELECT * FROM tournaments WHERE status='open' ORDER BY created_at DESC LIMIT 3";
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
                ?>
                <div class="card">
                    <div class="card-image" style="background-image: url('<?php echo !empty($row['image_url']) ? $row['image_url'] : 'https://placehold.co/600x400/1a1a1a/00ff88?text=BGMI+Tournament'; ?>');">
                        <?php if($is_registered): ?>
                            <span class="badge badge-registered overlay-badge" style="position:absolute; top: 10px; right: 10px; background: #00ff88; color: #000; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 0.75rem;">Registered ✅</span>
                        <?php elseif($pending_id > 0): ?>
                            <span class="badge overlay-badge" style="position:absolute; top: 10px; right: 10px; background: #ffcc00; color: #000; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 0.75rem;">Action Required ⚠️</span>
                        <?php elseif($is_full): ?>
                            <span class="badge overlay-badge" style="position:absolute; top: 10px; right: 10px; background: #ff4444; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 0.75rem;">FULL 🚫</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <div class="meta" style="margin-bottom: 5px;">
                            <span>📅 <?php echo date('M d | H:i', strtotime($row['date'])); ?></span>
                            <span class="badge badge-<?php echo $row['type']; ?>"><?php echo ucfirst($row['type']); ?></span>
                        </div>
                        <div class="meta">
                            <span style="color: var(--primary-color);">Entry: <?php echo htmlspecialchars($row['entry_fee']); ?></span>
                            <span style="color: #fff;">Prize: <?php echo htmlspecialchars($row['prize_pool']); ?></span>
                        </div>
                        <div class="meta" style="margin-top: 5px;">
                            <span style="<?php echo $is_full ? 'color: #ff4444;' : 'color: #00ff88;'; ?> font-weight: bold;">
                                Slots: <?php echo $registered_slots; ?>/<?php echo $max_slots; ?>
                                <?php echo $is_full ? '(FULL)' : ''; ?>
                            </span>
                        </div>
                        <p><?php echo substr(htmlspecialchars($row['description']), 0, 80); ?>...</p>
                        
                        <?php if($is_registered): ?>
                            <a href="profile.php" class="btn btn-sm" style="background: rgba(0, 255, 136, 0.1); color: #00ff88; border-color: #00ff88;">View My Registration</a>
                        <?php elseif($pending_id > 0): ?>
                            <a href="payment.php?id=<?php echo $pending_id; ?>" class="btn btn-sm" style="background: #ffcc00; color: #000; border: none;">Finish Payment 💳</a>
                        <?php else: ?>
                            <a href="register.php?id=<?php echo $row['id']; ?>" class="btn btn-sm">Register Now</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p class='text-muted'>No active tournaments at the moment. Check back soon!</p>";
        }
        ?>
    </div>
</section>

<style>
.section { padding: 4rem 20px; }

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.card {
    background: var(--bg-card);
    border: 1px solid #333;
    border-radius: 8px;
    overflow: hidden;
    transition: var(--transition);
}

.card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-color);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.card-image {
    height: 200px;
    background-color: #222;
    background-size: cover;
    background-position: center;
}

.card-content {
    padding: 1.5rem;
}

.card-content h3 {
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
}

.meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
}

.card-content p {
    color: #999;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.btn-sm {
    padding: 8px 20px;
    font-size: 0.8rem;
    width: 100%;
    text-align: center;
}
</style>

<?php include 'includes/footer.php'; ?>

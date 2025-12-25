<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$reg_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($reg_id == 0) {
    header("Location: index.php");
    exit();
}

// Fetch registration and tournament details
$sql = "SELECT r.*, t.title, t.entry_fee, t.qr_code FROM registrations r 
        JOIN tournaments t ON r.tournament_id = t.id 
        WHERE r.id = $reg_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$reg = $result->fetch_assoc();

// Security: Only the user who registered can pay for it
if ($reg['user_id'] != $_SESSION['user_id']) {
    header("Location: index.php");
    exit();
}

if ($reg['payment_status'] == 'completed') {
    header("Location: profile.php");
    exit();
}

if (isset($_POST['confirm_payment'])) {
    $conn->query("UPDATE registrations SET payment_status = 'completed' WHERE id = $reg_id");
    header("Location: profile.php?success=paid");
    exit();
}

include 'includes/header.php';
?>

<div class="container section">
    <div class="payment-card fade-in">
        <h2 class="section-title text-center">Complete <span class="text-primary">Payment</span></h2>
        
        <div class="tournament-info">
            <p>Tournament: <strong><?php echo htmlspecialchars($reg['title']); ?></strong></p>
            <p>Entry Fee: <strong class="text-primary" style="font-size: 1.5rem;"><?php echo htmlspecialchars($reg['entry_fee']); ?></strong></p>
        </div>

        <div class="qr-section text-center">
            <p style="color: #888; margin-bottom: 20px;">Scan the QR code below using any UPI App (Google Pay, PhonePe, Paytm)</p>
            <div class="qr-placeholder">
                <?php if(!empty($reg['qr_code'])): ?>
                    <img src="<?php echo $reg['qr_code']; ?>" alt="Custom Admin QR Code">
                <?php else: ?>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=upi://pay?pa=esports@upi&pn=BGMIZONE&am=<?php echo preg_replace('/[^0-9]/', '', $reg['entry_fee']); ?>&cu=INR" alt="Dynamic UPI QR Code">
                <?php endif; ?>
                <div class="scan-line"></div>
            </div>
            <p style="margin-top: 15px; font-weight: bold; color: var(--primary-color);">Merchant: BGMI ZONE ESPORTS</p>
        </div>

        <div class="payment-footer">
            <div class="alert note" style="font-size: 0.85rem; background: rgba(255,180,0,0.05); color: #888; border: 1px solid rgba(255,180,0,0.2);">
                ℹ️ After successful payment, click the button below to confirm your spot. Misleading confirmations may lead to account ban.
            </div>
            
            <form method="POST">
                <button type="submit" name="confirm_payment" class="btn active-btn" style="width: 100%; margin-top: 20px;">
                    I have Paid - Confirm Registration
                </button>
            </form>
            
            <a href="index.php" class="text-muted" style="display: block; text-align: center; margin-top: 15px; font-size: 0.9rem;">Cancel and Go Home</a>
        </div>
    </div>
</div>

<style>
.payment-card {
    max-width: 500px;
    margin: 40px auto;
    background: rgba(15, 15, 15, 0.9);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(0, 255, 136, 0.2);
    padding: 40px;
    position: relative;
    box-shadow: 0 0 40px rgba(0,255,136,0.05);
}

.tournament-info {
    background: rgba(255,255,255,0.03);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    text-align: center;
}

.qr-placeholder {
    width: 220px;
    height: 220px;
    background: #fff;
    margin: 0 auto;
    padding: 10px;
    position: relative;
    border: 5px solid var(--primary-color);
}

.qr-placeholder img {
    width: 100%;
}

.scan-line {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 2px;
    background: var(--primary-color);
    box-shadow: 0 0 10px var(--primary-color);
    animation: scan 2s linear infinite;
}

@keyframes scan {
    0% { top: 3%; }
    50% { top: 97%; }
    100% { top: 3%; }
}

.active-btn {
    background: var(--primary-color);
    color: #000;
    font-weight: 900;
    font-size: 1rem;
    padding: 15px;
    text-transform: uppercase;
    box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
}

.active-btn:hover {
    transform: scale(1.02);
    box-shadow: 0 0 30px rgba(0, 255, 136, 0.5);
}
</style>

<?php include 'includes/footer.php'; ?>

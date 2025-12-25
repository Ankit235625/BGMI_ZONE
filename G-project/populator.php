<?php
include 'includes/db.php';

// disable foreign key check to truncate
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE registrations");
$conn->query("TRUNCATE TABLE tournaments");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

$tournaments = [
    [
        "BATTLEGROUNDS MOBILE INDIA SERIES 2025",
        "The biggest official tournament of the year. Verify your squad and compete for national glory and a massive prize pool.",
        date('Y-m-d H:i:s', strtotime('+5 days')),
        "₹2,00,00,000",
        "Free",
        "open",
        "squad",
        "https://wallpaperaccess.com/full/6202720.jpg"
    ],
    [
        "BMPS Season 2: Pro League",
        "Battlegrounds Mobile India Pro Series. Exclusive for Tier 1 teams. Register for qualifiers now.",
        date('Y-m-d H:i:s', strtotime('+12 days')),
        "₹1,00,00,000",
        "Free",
        "open",
        "squad",
        "https://wallpaperaccess.com/full/3522777.jpg"
    ],
    [
        "Red Bull M.E.O. Season 6",
        "Open for all. The ultimate mobile esports open. Rise from the underdog to the champion.",
        date('Y-m-d H:i:s', strtotime('+20 days')),
        "₹50,00,000",
        "Free",
        "open",
        "squad",
        "https://images.hdqwalls.com/wallpapers/pubg-mobile-4k-2020-43.jpg"
    ],
    [
        "Solo Showdown: Erangel",
        "Prove you are the best individual player. King of the hill format. High stakes.",
        date('Y-m-d H:i:s', strtotime('+2 days')),
        "₹50,000",
        "₹100",
        "open",
        "solo",
        "https://wallpaperaccess.com/full/2232599.jpg"
    ],
    [
        "Duo TDM Championship",
        "Fast-paced TDM action. Coordinate with your partner and dominate the warehouse.",
        date('Y-m-d H:i:s', strtotime('+1 day')),
        "₹20,000",
        "₹50",
        "open",
        "duo",
        "https://w0.peakpx.com/wallpaper/814/99/HD-wallpaper-bgmi-battleground-mobile-india-pubg-pubg-mobile.jpg"
    ]
];

$stmt = $conn->prepare("INSERT INTO tournaments (title, description, date, prize_pool, entry_fee, status, type, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($tournaments as $t) {
    // 8 strings
    $stmt->bind_param("ssssssss", $t[0], $t[1], $t[2], $t[3], $t[4], $t[5], $t[6], $t[7]);
    if ($stmt->execute()) {
        echo "Created: " . $t[0] . "<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
}

echo "Database Populated with Real Data!";
?>

<?php
include '../includes/db.php';
include 'auth.php';
check_login();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Registrations - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/badges.css">
    <style>
        body { background: #0f0f0f; color: #fff; }
        .sidebar {
            width: 250px;
            background: #1a1a1a;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            border-right: 1px solid #333;
        }
        .main-content {
            margin-left: 250px;
            padding: 40px;
        }
        .nav-item {
            display: block;
            padding: 15px;
            color: #b3b3b3;
            font-weight: 600;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .nav-item:hover, .nav-item.active {
            background: rgba(0, 255, 136, 0.1);
            color: var(--primary-color);
        }
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1a1a1a;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        th { background: #222; text-transform: uppercase; font-size: 0.9rem; color: #888; letter-spacing: 1px; }
        tr:hover { background: rgba(255, 255, 255, 0.02); }
        
        .btn-view {
            padding: 5px 15px;
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
            background: rgba(0, 184, 255, 0.1);
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-view:hover {
            background: var(--secondary-color);
            color: #fff;
            box-shadow: 0 0 10px rgba(0, 184, 255, 0.5);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background: #1a1a1a;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid var(--primary-color);
            width: 50%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 0 30px rgba(0, 255, 157, 0.2);
            position: relative;
            animation: slideUp 0.3s ease-out;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: #fff; }
        .modal-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .player-row {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #333;
        }
        .player-row:last-child { border-bottom: none; }
        .player-label { color: #888; font-size: 0.9rem; }
        .player-value { color: #fff; font-weight: bold; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 class="text-primary text-center" style="margin-bottom: 40px;">BGMI<span style="color:#fff">ADMIN</span></h2>
    <a href="dashboard.php" class="nav-item">Dashboard</a>
    <a href="tournaments.php" class="nav-item">Manage Tournaments</a>
    <a href="registrations.php" class="nav-item active">View Registrations</a>
    <a href="users.php" class="nav-item">Manage Users</a>
    <a href="../index.php" class="nav-item" target="_blank">View Site</a>
    <a href="logout.php" class="nav-item" style="color: #ff4444;">Logout</a>
</div>

<div class="main-content">
    <h2 class="section-title">Team Registrations</h2>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tournament</th>
                <th>Team Name</th>
                <th>Captain</th>
                <th>Contact</th>
                <th>Registered At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT r.*, t.title as tournament_title 
                    FROM registrations r 
                    JOIN tournaments t ON r.tournament_id = t.id 
                    WHERE r.payment_status = 'completed'
                    ORDER BY r.created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Prepare JSON data for valid HTML attribute
                    $detailsJson = htmlspecialchars($row['player_details'], ENT_QUOTES, 'UTF-8');
                    
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td class='text-primary'>" . htmlspecialchars($row['tournament_title']) . "</td>
                        <td>" . htmlspecialchars($row['team_name']) . "</td>
                        <td>" . htmlspecialchars($row['captain_name']) . "</td>
                        <td>" . htmlspecialchars($row['captain_discord']) . "</td>
                        <td>" . date('M d, H:i', strtotime($row['created_at'])) . "</td>
                        <td>
                            <button class='btn-view' onclick='openModal({$row['id']}, \"{$row['team_name']}\", $detailsJson)'>View Players</button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No registrations yet.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Details Modal -->
<div id="playerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 class="modal-title" id="modalTeamName">Team Details</h3>
        <div id="modalBody">
            <!-- Dynamic Content -->
        </div>
    </div>
</div>

<script>
    function openModal(id, teamName, players) {
        document.getElementById('modalTeamName').innerText = teamName + " - Roster";
        const body = document.getElementById('modalBody');
        body.innerHTML = ''; // Clear previous

        if (Array.isArray(players)) {
            players.forEach((player, index) => {
                const num = index + 1;
                const html = `
                    <div class="player-row">
                        <div>
                            <div class="player-label">Player ${num}</div>
                            <div class="player-value">${player.name}</div>
                        </div>
                        <div style="text-align: right;">
                            <div class="player-label">BGMI ID</div>
                            <div class="player-value text-primary">${player.bgmi_id}</div>
                        </div>
                    </div>
                `;
                body.innerHTML += html;
            });
        } else {
            body.innerHTML = '<p class="text-center">No details found.</p>';
        }

        document.getElementById('playerModal').style.display = "block";
    }

    function closeModal() {
        document.getElementById('playerModal').style.display = "none";
    }

    // Close if clicked outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('playerModal')) {
            closeModal();
        }
    }
</script>

</body>
</html>

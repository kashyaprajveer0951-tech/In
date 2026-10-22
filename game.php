<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$username = $_SESSION['username'];

// Get user balance from database
require 'backend/db.php';
$user_id = $_SESSION['user_id'];
$balance = 0;
$bal_result = $conn->query("SELECT balance FROM users WHERE id = $user_id");
if ($bal_result && $row = $bal_result->fetch_assoc()) {
    $balance = $row['balance'];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, in
    itial-scale=1.0">
    <title>Chicken Road - Game</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Top Bar/Header -->
    <div class="new-topbar new-header header-flex-row">
        <div class="header-left header-row">
            <span class="big-logo">🐔</span>
            <span class="logo-title-main">CHICKEN ROAD</span>
        </div>
        <button class="new-menu-btn" id="menuBtn">☰</button>
        <div class="header-balance">
            <span class="balance-icon">₹</span>
            <span id="balanceHeader"><?php echo number_format($balance, 2); ?></span>
        </div>
        <a href="recharge.php" class="recharge-btn-header">Recharge</a>
    </div>
    <!-- Info Bar -->
    <div class="topbar-center info-bar-new">
        <span class="live-wins">Live wins</span>
        <span class="online-dot">●</span>
        <span class="online-count">Online: <span id="onlineCount">11134</span></span>
        <span class="recent-win"><span class="recent-user">Teal Azure O...</span> <span class="recent-amount win-amount">+₹504.00</span></span>
    </div>

    <!-- Game Area -->
    <div class="game-area">
        <div class="road new-road" id="road">
            <!-- Chicken and doors will be rendered by JS -->
        </div>
    </div>

    <!-- Show balance above controls card -->
    <div class="balance-bar">
        Balance: ₹<span id="balance"><?php echo number_format($balance, 2); ?></span>
    </div>

    <!-- Controls Card -->
    <div class="controls-card">
        <div class="bet-row">
            <button class="bet-btn min-btn" onclick="setWager(1)">MIN</button>
            <span class="bet-amount-label">₹<span id="wager">1</span></span>
            <button class="bet-btn max-btn" onclick="setWager(500)">MAX</button>
        </div>
        <div class="quick-bets-row">
            <button class="quick-bet-btn" onclick="setWager(20)">20</button>
            <button class="quick-bet-btn" onclick="setWager(50)">50</button>
            <button class="quick-bet-btn" onclick="setWager(100)">100</button>
            <button class="quick-bet-btn" onclick="setWager(500)">500</button>
        </div>
        <div class="difficulty-row">
            <select class="difficulty-select" id="difficultySelect" onchange="setDifficulty(this.value)">
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
            </select>
        </div>
        <div id="lowBalanceMsg" style="display:none;color:#ff5252;font-weight:600;text-align:center;margin:10px 0 0 0;">Your balance is too low. Please recharge to play.</div>
        <button class="play-btn new-play-btn" id="playBtn" onclick="startGame()">PLAY</button>
        <div class="go-cashout-row" style="display: flex; gap: 12px; margin-top: 14px;">
            <button class="play-btn new-play-btn" id="goBtn" onclick="goNext()" style="display:none; flex:1;">GO</button>
            <button class="play-btn cashout-btn" id="cashoutBtn" onclick="cashout()" style="display:none; flex:1;">CASHOUT <span id="cashoutAmt">0.00</span></button>
        </div>
        <div id="gameMessage" style="display:none;"></div>
    </div>

    <!-- Popups and scripts remain unchanged -->
    <audio id="bgMusic" src="assets/mp3.mp3" loop preload="auto"></audio>
    <audio id="goSound" src="https://cdn.pixabay.com/audio/2022/03/15/audio_115b9bfae2.mp3" preload="auto"></audio>
    <audio id="winSound" src="assets/win.mp3" preload="auto"></audio>
    <audio id="lossSound" src="assets/loss.mp3" preload="auto"></audio>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="avatar"></div>
            <div class="user-info">
                <span class="username"><?php echo htmlspecialchars($username); ?></span>
                <button class="change-avatar">Change avatar</button>
            </div>
        </div>
        <div class="sidebar-options">
            <!-- Navigation Buttons -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Quick Actions</div>
                <a href="referrals.php" class="sidebar-link sidebar-btn referral-btn">
                    <span>🎯</span> Referrals
                </a>
                <a href="recharge.php" class="sidebar-link sidebar-btn recharge-btn">
                    <span>💳</span> Recharge
                </a>
                <a href="withdrawal.php" class="sidebar-link sidebar-btn withdrawal-btn">
                    <span>💰</span> Withdraw
                </a>
                <a href="logout.php" class="sidebar-link sidebar-btn logout-btn">
                    <span>🚪</span> Logout
                </a>
            </div>
            
            <!-- Settings Section -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Settings</div>
                <div class="sidebar-toggle"><span>🔊</span> Sound <label class="switch"><input type="checkbox" id="soundToggle"><span class="slider"></span></label></div>
                <div class="sidebar-toggle"><span>🎵</span> Music <label class="switch"><input type="checkbox" id="musicToggle" checked><span class="slider"></span></label></div>
                <div class="sidebar-toggle"><span>⌨️</span> <span>«Space» to spin & go</span> <label class="switch"><input type="checkbox" id="spaceToggle" checked><span class="slider"></span></label></div>
            </div>
            
            <!-- Other Links -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Game Info</div>
                <div class="sidebar-link"><span>🛡️</span> Provably fair settings</div>
                <div class="sidebar-link"><span>📜</span> Game rules</div>
                <div class="sidebar-link"><span>📑</span> My bet history</div>
            </div>
        </div>
        <div class="sidebar-footer">
            <span class="powered-by">Powered by <b>iNOUT</b></span>
        </div>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="popup-overlay" id="crashPopup">
        <div class="popup-content crash-popup">
            <div class="popup-header crash-header">
                <div class="crash-icon">💥</div>
                <h2>Chicken Crashed!</h2>
            </div>
            <div class="popup-body">
                <p class="crash-message">The chicken crashed at level <span id="crashLevel">3</span></p>
                <p class="crash-amount">You lost: <span id="lostAmount">₹0.00</span></p>
                <div class="crash-animation">
                    <div class="falling-chicken">🐔</div>
                </div>
            </div>
            <div class="popup-footer">
                <button class="popup-btn" onclick="closeCrashPopup()">Play Again</button>
            </div>
        </div>
    </div>
    <div class="popup-overlay" id="cashoutPopup">
        <div class="popup-content cashout-popup">
            <div class="popup-header cashout-header">
                <div class="cashout-icon">🎉</div>
                <h2>Cashout Successful!</h2>
            </div>
            <div class="popup-body">
                <p class="cashout-message">You cashed out at level <span id="cashoutLevel">3</span></p>
                <p class="cashout-amount">You won: <span id="wonAmount">₹0.00</span></p>
                <div class="cashout-animation">
                    <div class="flying-chicken">🐔</div>
                    <div class="money-rain">💰</div>
                </div>
            </div>
            <div class="popup-footer">
                <button class="popup-btn" onclick="closeCashoutPopup()">Continue</button>
            </div>
        </div>
    </div>
    <script>
        // Sidebar open/close logic
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            document.getElementById('menuBtn').onclick = function() {
                sidebar.classList.add('open');
                overlay.classList.add('show');
            };
            overlay.onclick = function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
            };
        });
    </script>
    <style>
        /* Mobile-First Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0a0f1c 0%, #1a1f2e 25%, #2d3748 50%, #1a1f2e 75%, #0a0f1c 100%);
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(34, 197, 94, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(251, 191, 36, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        /* Topbar Mobile Optimization */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 100;
            box-shadow: 0 4px 25px rgba(0,0,0,0.4);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            flex-wrap: wrap;
            gap: 8px;
        }
        

        
        .logo-title {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        
        .logo {
            font-size: 1.5rem;
        }
        
        .title {
            font-size: 1rem;
            font-weight: 700;
            white-space: nowrap;
        }
        
        .live-info {
            display: none; /* Hide on mobile to save space */
        }
        
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn {
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #444a5a;
            color: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            min-width: 36px;
        }
        
        .btn:hover {
            background: #383f4e;
            transform: translateY(-1px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .recharge-btn {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }
        
        .recharge-btn:hover {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        }
        
        .withdrawal-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .withdrawal-btn:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        }
        

        
        .menu-btn {
            background: #64748b;
            font-size: 1.1rem;
        }
        

        
        .referral-btn {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }
        
        .referral-btn:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .logout-btn:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
        
        .wager-controls, .difficulty-controls {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        /* Controls */
        .controls {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            justify-content: center;
            flex-wrap: wrap;
            background: rgba(30, 41, 59, 0.8);
            padding: 16px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        
        /* Main Container */
        .container {
            max-width: 100%;
            margin: 0;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 0;
            box-shadow: none;
            padding: 16px;
            margin-top: 80px;
            min-height: calc(100vh - 80px);
            position: relative;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 30% 20%, rgba(34, 197, 94, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(251, 191, 36, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        /* Road Layout */
        .road {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            position: relative;
            border-radius: 20px;
            padding: 24px 16px 20px 16px;
            margin-bottom: 24px;
            min-height: 180px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4), 0 0 0 8px #222 inset;
            background: linear-gradient(180deg, #444a5a 0%, #232834 100%);
            overflow: hidden;
            border: 4px solid #ffe066;
        }
        
        .road .lane {
            width: 80px;
            height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            position: relative;
            margin: 0 4px;
            z-index: 1;
            border-right: 2px dashed #ffe066;
        }
        .road .lane:last-child {
            border-right: none;
        }
        
        .road .lane:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 8px;
            right: 0;
            width: 0;
            height: 80px;
            border-right: 5px dashed #ffe066;
            opacity: 0.7;
            z-index: 2;
        }

        .lane .chicken {
            width: 80px !important;
            height: 80px !important;
            font-size: 3.5rem !important;
            margin-bottom: 4px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            background: none !important;
            z-index: 3 !important;
            margin-left: 0 !important;
        }
        
        .lane .item {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 6px;
        }
        
        .lane .chicken {
            width: 50px;
            height: 50px;
            background: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 0;
            z-index: 2;
        }
        
        .item.multiplier, .lane .multiplier {
            width: 90px !important;
            height: 90px !important;
            aspect-ratio: 1/1 !important;
            background: rgba(30,40,60,0.7) !important;
            color: #ffe066 !important;
            border-radius: 50% !important;
            font-size: 2.2rem !important;
            font-weight: 900 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-bottom: 4px !important;
            border: 2px solid #23243a !important;
            box-shadow: 0 2px 8px rgba(255,224,102,0.08) !important;
            text-align: center !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        
        .lane .obstacle {
            font-size: 1.8rem;
            margin-bottom: 6px;
        }
        
        .lane .coin {
            font-size: 1.8rem;
            margin-bottom: 6px;
        }
        

        
        .wager-label, .difficulty-label {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0 6px;
        }
        
        .bet-btn {
            background: #383f4e;
            color: #fff;
            border: none;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .bet-btn:active {
            background: #1e293b;
            transform: scale(0.95);
        }
        
        .wager-box {
            background: #232834;
            border-radius: 8px;
            padding: 0 12px;
            font-size: 1rem;
            font-weight: 600;
            min-width: 60px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
        }
        
        .diff-btn {
            background: #383f4e;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            margin-left: 4px;
            transition: all 0.3s ease;
            min-height: 40px;
        }
        
        .diff-btn.active, .diff-btn:active {
            background: #ffe066;
            color: #232834;
        }
        
        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
            background: rgba(30, 41, 59, 0.8);
            padding: 16px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        
        .play-btn {
            border: none;
            border-radius: 16px;
            padding: 0 24px;
            height: 56px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }
        
        .play-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .play-btn:hover::before {
            left: 100%;
        }
        
        .go-btn {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
            color: #fff !important;
        }
        
        .go-btn:active {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
            color: #fff !important;
            transform: scale(0.98);
        }
        
        .cashout-btn {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #232834;
        }
        
        .cashout-btn:active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            transform: scale(0.98);
        }
        
        .play-btn[disabled] {
            background: #64748b;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Info Bar */
        .info-bar {
            display: flex;
            justify-content: space-around;
            gap: 16px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            flex-wrap: wrap;
            background: rgba(30, 41, 59, 0.8);
            padding: 16px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        
        .highlight {
            color: #ffe066;
            font-weight: 700;
        }
        
        .game-message {
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            margin-top: 12px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            right: -100%;
            width: 85vw;
            max-width: 320px;
            height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            z-index: 200;
            transition: right 0.3s ease;
            overflow-y: auto;
            box-shadow: -5px 0 20px rgba(0,0,0,0.3);
        }
        
        .sidebar.open {
            right: 0;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .avatar {
            width: 60px;
            height: 60px;
            background: #64748b;
            border-radius: 50%;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .user-info {
            text-align: center;
        }
        
        .username {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        .change-avatar {
            background: #64748b;
            border: none;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
        }
        
        .sidebar-options {
            padding: 20px;
        }
        
        .sidebar-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #64748b;
            transition: 0.3s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }
        
        .switch input:checked + .slider {
            background-color: #22c55e;
        }
        
        .switch input:checked + .slider:before {
            transform: translateX(20px);
        }
        
        .sidebar-section {
            margin-bottom: 24px;
        }
        
        .sidebar-section-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 0;
            font-size: 0.9rem;
            cursor: pointer;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-decoration: none;
            color: #f8fafc;
            transition: all 0.3s ease;
        }
        
        .sidebar-link:hover {
            color: #22c55e;
            background: rgba(34, 197, 94, 0.1);
            padding-left: 8px;
            border-radius: 8px;
        }
        
        .sidebar-btn {
            border-radius: 8px;
            margin-bottom: 8px;
            padding: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
        }
        
        .sidebar-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .sidebar-footer {
            padding: 20px;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.8rem;
            color: #94a3b8;
        }
        
        .powered-by b {
            color: #22c55e;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 150;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(2px);
        }
        
        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        /* Popup Styles */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
            padding: 16px;
        }
        
        .popup-content {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 16px;
            padding: 0;
            max-width: 100%;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: popupSlideIn 0.5s ease-out;
            border: 2px solid;
            overflow: hidden;
        }
        
        @keyframes popupSlideIn {
            from {
                transform: translateY(-50px) scale(0.9);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }
        
        .popup-header {
            padding: 20px 20px 12px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .crash-popup {
            border-color: #e11d48;
        }
        
        .cashout-popup {
            border-color: #22c55e;
        }
        
        .crash-header {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        }
        
        .cashout-header {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }
        
        .crash-icon, .cashout-icon {
            font-size: 2.5rem;
            margin-bottom: 8px;
            animation: bounce 1s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-8px);
            }
            60% {
                transform: translateY(-4px);
            }
        }
        
        .popup-header h2 {
            color: #fff;
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0;
        }
        
        .popup-body {
            padding: 20px;
            text-align: center;
        }
        
        .crash-message, .cashout-message {
            color: #f8fafc;
            font-size: 1rem;
            margin-bottom: 12px;
        }
        
        .crash-amount, .cashout-amount {
            color: #f8fafc;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 16px;
        }
        
        .crash-amount span {
            color: #e11d48;
        }
        
        .cashout-amount span {
            color: #22c55e;
        }
        
        .crash-animation, .cashout-animation {
            height: 60px;
            position: relative;
            margin: 16px 0;
        }
        
        .falling-chicken {
            font-size: 1.8rem;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            animation: fallDown 2s ease-in forwards;
        }
        
        @keyframes fallDown {
            0% {
                transform: translateX(-50%) translateY(0) rotate(0deg);
            }
            50% {
                transform: translateX(-50%) translateY(30px) rotate(180deg);
            }
            100% {
                transform: translateX(-50%) translateY(60px) rotate(360deg);
            }
        }
        
        .flying-chicken {
            font-size: 1.8rem;
            position: absolute;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            animation: flyUp 2s ease-out infinite;
        }
        
        @keyframes flyUp {
            0% {
                transform: translateX(-50%) translateY(0);
            }
            50% {
                transform: translateX(-50%) translateY(-15px);
            }
            100% {
                transform: translateX(-50%) translateY(0);
            }
        }
        
        .money-rain {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            font-size: 1.2rem;
            animation: moneyRain 1.5s ease-out infinite;
        }
        
        .money-rain::before {
            content: '💰 💰 💰 💰 💰';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            animation: fallMoney 2s linear infinite;
        }
        
        @keyframes fallMoney {
            0% {
                transform: translateY(-40px);
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translateY(60px);
                opacity: 0;
            }
        }
        
        .popup-footer {
            padding: 16px 20px 20px;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .popup-btn {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-height: 44px;
            width: 100%;
        }
        
        .popup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);
        }
        
        .popup-btn:active {
            transform: translateY(0);
        }
        
        /* Tablet and larger screens */
        @media (min-width: 768px) {
            .topbar {
                padding: 16px 24px;
            }
            
            .title {
                font-size: 1.2rem;
            }
            
            .live-info {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 0.85rem;
            }
            
            .container {
                max-width: 1200px;
                margin: 30px auto;
                border-radius: 16px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.2);
                padding: 24px 32px 32px 32px;
                margin-top: 100px;
            }
            
            .road {
                padding: 32px 16px 24px 16px;
                margin-bottom: 32px;
                min-height: 220px;
                border-radius: 16px;
            }
            
            .road .lane {
                width: 100px;
                height: 180px;
            }
            
            .lane .item, .lane .chicken {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }
            
            .lane .chicken {
                font-size: 3.2rem;
            }
            
            .lane .multiplier {
                width: 60px;
                height: 60px;
                font-size: 1.2rem;
                border: 3px solid #232834;
            }
            
            .controls {
                gap: 10px;
                margin-bottom: 18px;
            }
            
            .wager-label, .difficulty-label {
                font-size: 1.1rem;
                margin: 0 8px;
            }
            
            .bet-btn {
                width: 36px;
                height: 36px;
                font-size: 1.2rem;
            }
            
            .wager-box {
                padding: 0 16px;
                font-size: 1.1rem;
                min-width: 50px;
                height: 36px;
            }
            
            .diff-btn {
                padding: 8px 18px;
                font-size: 1rem;
                height: 36px;
            }
            
            .action-bar {
                gap: 18px;
                margin-bottom: 10px;
            }
            
            .play-btn {
                padding: 0 32px;
                height: 48px;
                font-size: 1.2rem;
                min-width: 140px;
            }
            
            .info-bar {
                font-size: 1rem;
            }
            
            .popup-content {
                max-width: 400px;
                width: 90%;
            }
            
            .popup-header {
                padding: 25px 25px 15px;
            }
            
            .popup-header h2 {
                font-size: 1.5rem;
            }
            
            .popup-body {
                padding: 25px;
            }
            
            .crash-message, .cashout-message {
                font-size: 1.1rem;
                margin-bottom: 15px;
            }
            
            .crash-amount, .cashout-amount {
                font-size: 1.3rem;
                margin-bottom: 20px;
            }
            
            .crash-animation, .cashout-animation {
                height: 80px;
                margin: 20px 0;
            }
            
            .crash-icon, .cashout-icon {
                font-size: 3rem;
                margin-bottom: 10px;
            }
            
            .popup-footer {
                padding: 20px 25px 25px;
            }
            
            .popup-btn {
                width: auto;
                padding: 12px 30px;
                font-size: 1rem;
            }
        }
        
        /* Large screens */
        @media (min-width: 1024px) {
            .sidebar {
                width: 320px;
            }
        }
        
        /* Extra small screens */
        @media (max-width: 360px) {
            .topbar {
                padding: 10px 12px;
            }
            
            .title {
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 6px 8px;
                font-size: 0.8rem;
                min-height: 32px;
                min-width: 32px;
            }
            
            .balance-box {
                padding: 6px 8px;
                font-size: 0.8rem;
                min-width: 70px;
            }
            
            .road {
                padding: 16px 6px 12px 6px;
                min-height: 160px;
            }
            
            .road .lane {
                width: 60px;
                height: 120px;
            }
            
            .lane .item, .lane .chicken {
                width: 50px;
                height: 50px;
                font-size: 2rem;
            }
            
            .lane .chicken {
                font-size: 2.5rem;
            }
            
            .lane .multiplier {
                width: 40px;
                height: 40px;
                font-size: 0.9rem;
            }
            
            .controls {
                gap: 6px;
                padding: 12px;
            }
            
            .wager-label, .difficulty-label {
                font-size: 0.8rem;
                margin: 0 4px;
            }
            
            .bet-btn {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }
            
            .wager-box {
                padding: 0 8px;
                font-size: 0.9rem;
                min-width: 50px;
                height: 36px;
            }
            
            .diff-btn {
                padding: 6px 8px;
                font-size: 0.8rem;
                height: 36px;
            }
            
            .play-btn {
                padding: 0 16px;
                height: 48px;
                font-size: 0.9rem;
                min-width: 100px;
            }
            
            .action-bar {
                padding: 12px;
                gap: 8px;
            }
            
            .info-bar {
                font-size: 0.8rem;
                gap: 12px;
                padding: 12px;
            }
        }
        
        /* Enhanced Mobile Styles */
        @media (max-width: 480px) {
            .container {
                padding: 12px;
                margin-top: 70px;
            }
            
            .road {
                padding: 20px 8px 16px 8px;
                margin-bottom: 16px;
                min-height: 180px;
            }
            
            .road .lane {
                width: 70px;
                height: 140px;
            }
            
            .lane .item, .lane .chicken {
                width: 60px;
                height: 60px;
                font-size: 2.2rem;
            }
            
            .lane .chicken {
                font-size: 2.8rem;
            }
            
            .lane .multiplier {
                width: 50px;
                height: 50px;
                font-size: 1rem;
                border: 2px solid #232834;
            }
            
            .controls, .action-bar, .info-bar {
                margin-bottom: 12px;
                padding: 12px;
            }
            
            .play-btn {
                height: 52px;
                font-size: 1rem;
                min-width: 120px;
            }
            
            .btn {
                min-height: 40px;
                min-width: 40px;
                font-size: 0.9rem;
            }
            
            .balance-box {
                min-height: 40px;
                font-size: 0.9rem;
            }
        }
        
        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            .btn, .play-btn, .bet-btn, .diff-btn {
                min-height: 48px;
                min-width: 48px;
            }
            
            .play-btn {
                min-height: 60px;
                font-size: 1.1rem;
            }
            
            .controls, .action-bar {
                gap: 16px;
                padding: 16px;
            }
            
            .wager-box {
                min-height: 48px;
                font-size: 1.1rem;
            }
            
            .bet-btn {
                font-size: 1.3rem;
            }
            
            .diff-btn {
                font-size: 1rem;
                padding: 12px 16px;
            }
        }
        
        /* Landscape orientation */
        @media (orientation: landscape) and (max-height: 600px) {
            .topbar {
                padding: 8px 16px;
            }
            
            .container {
                margin-top: 70px;
                padding: 12px 16px;
            }
            
            .road {
                min-height: 120px;
                padding: 12px 8px 8px 8px;
                margin-bottom: 16px;
            }
            
            .road .lane {
                height: 80px;
            }
            
            .controls {
                margin-bottom: 12px;
            }
            
            .action-bar {
                margin-bottom: 8px;
            }
        }
        
        /* Mobile animations and effects */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        @keyframes glow {
            0%, 100% {
                box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            }
            50% {
                box-shadow: 0 4px 25px rgba(34, 197, 94, 0.4);
            }
        }
        
        .play-btn:not([disabled]):hover {
            animation: pulse 1s infinite;
        }
        
        .go-btn {
            animation: glow 2s infinite;
        }
        
        /* Mobile scroll improvements */
        .container {
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
        }
        
        /* Prevent zoom on input focus */
        input, select, textarea {
            font-size: 16px;
        }
        
        /* Mobile tap highlights */
        .btn, .play-btn, .bet-btn, .diff-btn {
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            -webkit-user-select: none;
        }
        
        /* Mobile loading states */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .go-cashout-row {
            display: flex;
            gap: 12px;
            margin-top: 14px;
        }
        .go-cashout-row .play-btn {
            flex: 1;
            margin-top: 0;
        }

        @media (max-width: 600px) {
          .new-road {
            width: 100vw !important;
            max-width: 100vw !important;
            overflow-x: auto !important;
            display: flex !important;
            background: linear-gradient(180deg, #23243a 80%, #23243a 100%) !important;
            border: 4px solid #ffe066;
            border-radius: 18px;
            padding: 12px 0 0 0;
            min-height: 110px;
            box-sizing: border-box;
            scroll-behavior: smooth;
          }
          .new-road .lane {
            width: 22vw !important;   /* 4 lanes + margin ≈ 100vw */
            min-width: 70px !important;
            max-width: 110px !important;
            height: 90px !important;
            margin: 0 2vw !important;
            background: none !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
          }
          .lane .chicken {
            width: 40px;
            height: 40px;
            font-size: 2rem;
          }
          .lane .multiplier {
            width: 60px !important;
            height: 60px !important;
            font-size: 1.1rem !important;
          }
        }
        @media (max-width: 400px) {
          .new-road .lane {
            width: 23vw !important;
            min-width: 48px !important;
            max-width: 80px !important;
            height: 60px !important;
            margin: 0 1vw !important;
          }
          .lane .chicken {
            width: 24px;
            height: 24px;
            font-size: 1.2rem;
          }
          .lane .multiplier {
            width: 24px !important;
            height: 24px !important;
            font-size: 0.8rem !important;
          }
        }
    </style>
    
    <script>
        // Initialize balance from PHP
        window.initBalance = <?php echo $balance; ?>;
        window.initWager = 1;
        
        // Popup functions
        function showCrashPopup(level, amount) {
            document.getElementById('crashLevel').textContent = level;
            document.getElementById('lostAmount').textContent = '₹' + amount.toFixed(2);
            document.getElementById('crashPopup').style.display = 'flex';
        }
        
        function showCashoutPopup(level, amount) {
            document.getElementById('cashoutLevel').textContent = level;
            document.getElementById('wonAmount').textContent = '₹' + amount.toFixed(2);
            document.getElementById('cashoutPopup').style.display = 'flex';
        }
        
        function closeCrashPopup() {
            document.getElementById('crashPopup').style.display = 'none';
            resetGame();
        }
        
        function closeCashoutPopup() {
            document.getElementById('cashoutPopup').style.display = 'none';
            resetGame();
        }
    </script>
    <script src="assets/script.js"></script>
</body>
</html> 
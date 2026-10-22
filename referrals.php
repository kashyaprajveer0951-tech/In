<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
require 'backend/db.php';

// Get user data including referral info
$user_data = [];
$user_query = $conn->prepare("SELECT id, username, balance, referral_code, total_referrals, total_referral_earnings FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
if ($user_row = $user_result->fetch_assoc()) {
    $user_data = $user_row;
}

// Get user's referrals
$referrals = [];
$ref_query = $conn->prepare("
    SELECT r.id, r.referred_id, r.referral_code, r.status, r.created_at, u.username as referred_username
    FROM referrals r 
    JOIN users u ON r.referred_id = u.id 
    WHERE r.referrer_id = ? 
    ORDER BY r.created_at DESC
");
$ref_query->bind_param("i", $user_id);
$ref_query->execute();
$ref_result = $ref_query->get_result();
while ($ref_row = $ref_result->fetch_assoc()) {
    $referrals[] = $ref_row;
}

// Get referral rewards history
$rewards = [];
$reward_query = $conn->prepare("
    SELECT rr.id, rr.amount, rr.type, rr.status, rr.created_at, u.username as referred_username
    FROM referral_rewards rr
    JOIN users u ON rr.referred_id = u.id
    WHERE rr.referrer_id = ?
    ORDER BY rr.created_at DESC
    LIMIT 20
");
$reward_query->bind_param("i", $user_id);
$reward_query->execute();
$reward_result = $reward_query->get_result();
while ($reward_row = $reward_result->fetch_assoc()) {
    $rewards[] = $reward_row;
}

// Handle copy referral code
$copy_message = '';
if (isset($_POST['copy_code'])) {
    $copy_message = 'Referral code copied to clipboard!';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referrals - Chicken Road</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0a0f1c 0%, #1a1f2e 25%, #2d3748 50%, #1a1f2e 75%, #0a0f1c 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #fff;
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
        
        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(10, 15, 28, 0.95);
            backdrop-filter: blur(20px);
            padding: 12px 20px;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo {
            font-size: 1.8rem;
            animation: bounce 2s infinite;
            filter: drop-shadow(0 0 10px rgba(251, 191, 36, 0.5));
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-3px); }
            60% { transform: translateY(-2px); }
        }
        
        .brand-name {
            font-size: 1.3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
        }
        
        .nav-links {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        
        .nav-link {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        /* Main Container */
        .main-container {
            padding: 100px 20px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .page-subtitle {
            color: #94a3b8;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #22c55e, #16a34a, #22c55e);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
            display: block;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #22c55e;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Referral Code Section */
        .referral-code-section {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 40px;
            border: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .referral-code-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #fbbf24, #f59e0b, #fbbf24);
        }
        
        .code-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: #f8fafc;
        }
        
        .code-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .referral-code {
            background: rgba(34, 197, 94, 0.1);
            border: 2px solid rgba(34, 197, 94, 0.3);
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 1.2rem;
            font-weight: 700;
            color: #22c55e;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            min-width: 200px;
            text-align: center;
        }
        
        .referral-link {
            background: rgba(59, 130, 246, 0.1);
            border: 2px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #3b82f6;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            min-width: 200px;
            text-align: center;
            line-height: 1.4;
        }
        
        .copy-btn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px 24px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .copy-btn:hover {
            background: linear-gradient(135deg, #16a34a, #15803d);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
        }
        
        .copy-message {
            color: #22c55e;
            font-weight: 600;
            margin-top: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .copy-message.show {
            opacity: 1;
        }
        
        .share-links {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .share-btn {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .share-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .quick-share-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 16px 12px;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .quick-share-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .quick-share-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
        }
        
        /* Content Tabs */
        .content-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            background: rgba(30, 41, 59, 0.5);
            padding: 8px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .tab-btn {
            background: transparent;
            color: #94a3b8;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }
        
        .tab-btn.active {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }
        
        .tab-btn:hover:not(.active) {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Table Styles */
        .data-table {
            width: 100%;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .data-table th {
            background: rgba(51, 65, 85, 0.8);
            color: #f8fafc;
            font-weight: 700;
            padding: 16px;
            text-align: left;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-table td {
            padding: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: #e2e8f0;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr:hover {
            background: rgba(255,255,255,0.05);
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-completed {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }
        
        .status-pending {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }
        
        .status-paid {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-text {
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        
        .empty-subtext {
            font-size: 0.9rem;
            opacity: 0.7;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 8px 12px;
            }
            
            .brand-name {
                font-size: 1rem;
            }
            
            .nav-links {
                gap: 6px;
                flex-wrap: wrap;
            }
            
            .nav-link {
                padding: 6px 10px;
                font-size: 0.75rem;
                min-width: auto;
            }
            
            .main-container {
                padding: 70px 12px 20px;
            }
            
            .page-title {
                font-size: 1.8rem;
                margin-bottom: 12px;
            }
            
            .page-subtitle {
                font-size: 1rem;
                line-height: 1.5;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                margin-bottom: 24px;
            }
            
            .stat-card {
                padding: 16px 12px;
                border-radius: 12px;
            }
            
            .stat-icon {
                font-size: 2rem;
                margin-bottom: 8px;
            }
            
            .stat-number {
                font-size: 1.5rem;
                margin-bottom: 6px;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            .referral-code-section {
                padding: 20px 16px;
                border-radius: 16px;
                margin-bottom: 24px;
            }
            
            .code-title {
                font-size: 1.3rem;
                margin-bottom: 20px;
            }
            
            .code-container {
                flex-direction: column;
                gap: 12px;
                margin-bottom: 16px;
            }
            
            .referral-code {
                font-size: 1.1rem;
                padding: 14px 16px;
                min-width: auto;
                width: 100%;
                letter-spacing: 1px;
            }
            
            .referral-link {
                font-size: 0.85rem;
                padding: 14px 16px;
                min-width: auto;
                width: 100%;
                line-height: 1.3;
            }
            
            .copy-btn {
                padding: 14px 20px;
                font-size: 0.95rem;
                width: 100%;
            }
            
            .share-links {
                flex-direction: column;
                gap: 10px;
                margin-top: 16px;
            }
            
            .share-btn {
                padding: 14px 16px;
                font-size: 0.9rem;
                width: 100%;
                text-align: center;
            }
            
            .quick-share-btn {
                padding: 12px 8px;
                font-size: 0.85rem;
                gap: 6px;
            }
            
            .quick-share-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            
            .content-tabs {
                flex-direction: row;
                gap: 4px;
                padding: 6px;
                margin-bottom: 20px;
            }
            
            .tab-btn {
                padding: 10px 12px;
                font-size: 0.85rem;
                flex: 1;
                text-align: center;
            }
            
            .data-table {
                font-size: 0.85rem;
                border-radius: 12px;
            }
            
            .data-table th,
            .data-table td {
                padding: 10px 6px;
                font-size: 0.8rem;
            }
            
            .data-table th {
                font-size: 0.75rem;
                padding: 8px 6px;
            }
            
            .status-badge {
                padding: 3px 8px;
                font-size: 0.7rem;
            }
            
            .empty-state {
                padding: 40px 20px;
            }
            
            .empty-icon {
                font-size: 3rem;
                margin-bottom: 12px;
            }
            
            .empty-text {
                font-size: 1rem;
                margin-bottom: 6px;
            }
            
            .empty-subtext {
                font-size: 0.85rem;
            }
        }
        
        /* Extra Small Mobile */
        @media (max-width: 480px) {
            .header {
                padding: 6px 10px;
            }
            
            .brand-name {
                font-size: 0.9rem;
            }
            
            .nav-links {
                gap: 4px;
            }
            
            .nav-link {
                padding: 5px 8px;
                font-size: 0.7rem;
            }
            
            .main-container {
                padding: 65px 8px 16px;
            }
            
            .page-title {
                font-size: 1.6rem;
            }
            
            .page-subtitle {
                font-size: 0.9rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .stat-card {
                padding: 14px 10px;
            }
            
            .stat-icon {
                font-size: 1.8rem;
            }
            
            .stat-number {
                font-size: 1.3rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
            }
            
            .referral-code-section {
                padding: 16px 12px;
            }
            
            .code-title {
                font-size: 1.2rem;
                margin-bottom: 16px;
            }
            
            .referral-code {
                font-size: 1rem;
                padding: 12px 14px;
            }
            
            .referral-link {
                font-size: 0.8rem;
                padding: 12px 14px;
            }
            
            .copy-btn {
                padding: 12px 16px;
                font-size: 0.9rem;
            }
            
            .share-btn {
                padding: 12px 14px;
                font-size: 0.85rem;
            }
            
            .quick-share-btn {
                padding: 10px 6px;
                font-size: 0.8rem;
                gap: 4px;
            }
            
            .quick-share-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px;
            }
            
            .content-tabs {
                padding: 4px;
                gap: 2px;
            }
            
            .tab-btn {
                padding: 8px 8px;
                font-size: 0.8rem;
            }
            
            .data-table {
                font-size: 0.8rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 8px 4px;
                font-size: 0.75rem;
            }
            
            .data-table th {
                font-size: 0.7rem;
                padding: 6px 4px;
            }
        }
        
        /* Landscape orientation for mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            .main-container {
                padding: 60px 16px 16px;
            }
            
            .page-title {
                font-size: 1.5rem;
                margin-bottom: 8px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 8px;
            }
            
            .stat-card {
                padding: 12px 8px;
            }
            
            .stat-icon {
                font-size: 1.5rem;
                margin-bottom: 4px;
            }
            
            .stat-number {
                font-size: 1.2rem;
                margin-bottom: 4px;
            }
            
            .stat-label {
                font-size: 0.7rem;
            }
            
            .referral-code-section {
                padding: 16px;
            }
            
            .code-container {
                flex-direction: row;
                gap: 8px;
            }
            
            .referral-code,
            .referral-link {
                flex: 1;
                min-width: 0;
            }
            
            .copy-btn {
                width: auto;
                padding: 12px 16px;
            }
            
            .quick-share-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 6px;
            }
            
            .quick-share-btn {
                padding: 8px 4px;
                font-size: 0.75rem;
                gap: 2px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <span class="logo">🐔</span>
                <span class="brand-name">CHICKEN ROAD</span>
            </div>
            <div class="nav-links">
                <a href="game.php" class="nav-link">🎮 Play Game</a>
                <a href="payment/recharge.php" class="nav-link">💳 Recharge</a>
                <a href="withdrawal.php" class="nav-link">💰 Withdraw</a>
                <a href="logout.php" class="nav-link">🚪 Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">🎯 Referral Program</h1>
            <p class="page-subtitle">Invite friends and earn rewards! Share your referral code and start earning today.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">👥</span>
                <div class="stat-number"><?php echo $user_data['total_referrals'] ?? 0; ?></div>
                <div class="stat-label">Total Referrals</div>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">💰</span>
                <div class="stat-number">₹<?php echo number_format($user_data['total_referral_earnings'] ?? 0, 2); ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">🎁</span>
                <div class="stat-number">₹50</div>
                <div class="stat-label">Per Referral</div>
            </div>
            
            <div class="stat-card">
                <span class="stat-icon">🏆</span>
                <div class="stat-number">₹<?php echo number_format($user_data['balance'] ?? 0, 2); ?></div>
                <div class="stat-label">Current Balance</div>
            </div>
        </div>

        <!-- Referral Code Section -->
        <div class="referral-code-section">
            <h2 class="code-title">Your Referral Code & Link</h2>
            
            <!-- Referral Code -->
            <div style="margin-bottom: 24px;">
                <div style="font-size: 1rem; font-weight: 600; color: #e2e8f0; margin-bottom: 12px;">Referral Code:</div>
                <div class="code-container">
                    <div class="referral-code" id="referralCode"><?php echo $user_data['referral_code'] ?? 'N/A'; ?></div>
                    <button class="copy-btn" onclick="copyReferralCode()">📋 Copy Code</button>
                </div>
            </div>
            
            <!-- Referral Link -->
            <div style="margin-bottom: 24px;">
                <div style="font-size: 1rem; font-weight: 600; color: #e2e8f0; margin-bottom: 12px;">Referral Link:</div>
                <div class="code-container">
                    <div class="referral-link" id="referralLink"><?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/register.php?ref=' . ($user_data['referral_code'] ?? ''); ?></div>
                    <button class="copy-btn" onclick="copyReferralLink()">🔗 Copy Link</button>
                </div>
            </div>
            
            <div class="copy-message" id="copyMessage">Copied to clipboard!</div>
            
            <div class="share-links">
                <a href="https://wa.me/?text=Join%20Chicken%20Road%20and%20win%20big!%20Use%20my%20referral%20link:%20<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/register.php?ref=' . ($user_data['referral_code'] ?? '')); ?>" class="share-btn" target="_blank">📱 WhatsApp</a>
                <a href="https://t.me/share/url?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/register.php?ref=' . ($user_data['referral_code'] ?? '')); ?>&text=Join%20Chicken%20Road%20and%20win%20big!%20Use%20my%20referral%20link" class="share-btn" target="_blank">💬 Telegram</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/register.php?ref=' . ($user_data['referral_code'] ?? '')); ?>" class="share-btn" target="_blank">📘 Facebook</a>
                <a href="https://twitter.com/intent/tweet?text=Join%20Chicken%20Road%20and%20win%20big!%20Use%20my%20referral%20link&url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/register.php?ref=' . ($user_data['referral_code'] ?? '')); ?>" class="share-btn" target="_blank">🐦 Twitter</a>
            </div>
            
            <!-- Quick Share Section -->
            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 1rem; font-weight: 600; color: #e2e8f0; margin-bottom: 16px;">Quick Share Options:</div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;" class="quick-share-grid">
                    <button class="quick-share-btn" onclick="shareToWhatsApp()">
                        <span style="font-size: 1.2rem;">📱</span>
                        <span>WhatsApp</span>
                    </button>
                    <button class="quick-share-btn" onclick="shareToTelegram()">
                        <span style="font-size: 1.2rem;">💬</span>
                        <span>Telegram</span>
                    </button>
                    <button class="quick-share-btn" onclick="shareToFacebook()">
                        <span style="font-size: 1.2rem;">📘</span>
                        <span>Facebook</span>
                    </button>
                    <button class="quick-share-btn" onclick="shareToTwitter()">
                        <span style="font-size: 1.2rem;">🐦</span>
                        <span>Twitter</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Content Tabs -->
        <div class="content-tabs">
            <button class="tab-btn active" onclick="showTab('referrals')">👥 My Referrals</button>
            <button class="tab-btn" onclick="showTab('rewards')">💰 Rewards History</button>
        </div>

        <!-- Referrals Tab -->
        <div class="tab-content active" id="referrals-tab">
            <?php if (empty($referrals)): ?>
                <div class="empty-state">
                    <div class="empty-icon">👥</div>
                    <div class="empty-text">No referrals yet</div>
                    <div class="empty-subtext">Share your referral code with friends to start earning!</div>
                </div>
            <?php else: ?>
                <div class="data-table">
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Referral Code</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referrals as $referral): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($referral['referred_username']); ?></td>
                                    <td><?php echo htmlspecialchars($referral['referral_code']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $referral['status']; ?>">
                                            <?php echo ucfirst($referral['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($referral['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Rewards Tab -->
        <div class="tab-content" id="rewards-tab">
            <?php if (empty($rewards)): ?>
                <div class="empty-state">
                    <div class="empty-icon">💰</div>
                    <div class="empty-text">No rewards yet</div>
                    <div class="empty-subtext">Refer friends to start earning rewards!</div>
                </div>
            <?php else: ?>
                <div class="data-table">
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Referred User</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rewards as $reward): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reward['referred_username']); ?></td>
                                    <td>₹<?php echo number_format($reward['amount'], 2); ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $reward['type'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $reward['status']; ?>">
                                            <?php echo ucfirst($reward['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($reward['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Copy referral code function
        function copyReferralCode() {
            const code = document.getElementById('referralCode').textContent;
            navigator.clipboard.writeText(code).then(() => {
                const message = document.getElementById('copyMessage');
                message.textContent = 'Referral code copied to clipboard!';
                message.classList.add('show');
                setTimeout(() => {
                    message.classList.remove('show');
                }, 2000);
            });
        }
        
        // Copy referral link function
        function copyReferralLink() {
            const link = document.getElementById('referralLink').textContent;
            navigator.clipboard.writeText(link).then(() => {
                const message = document.getElementById('copyMessage');
                message.textContent = 'Referral link copied to clipboard!';
                message.classList.add('show');
                setTimeout(() => {
                    message.classList.remove('show');
                }, 2000);
            });
        }

        // Tab switching function
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');
        }

        // Auto-refresh stats every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
        
        // Quick share functions
        function shareToWhatsApp() {
            const link = document.getElementById('referralLink').textContent;
            const text = `Join Chicken Road and win big! Use my referral link: ${link}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }
        
        function shareToTelegram() {
            const link = document.getElementById('referralLink').textContent;
            const text = `Join Chicken Road and win big! Use my referral link`;
            window.open(`https://t.me/share/url?url=${encodeURIComponent(link)}&text=${encodeURIComponent(text)}`, '_blank');
        }
        
        function shareToFacebook() {
            const link = document.getElementById('referralLink').textContent;
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`, '_blank');
        }
        
        function shareToTwitter() {
            const link = document.getElementById('referralLink').textContent;
            const text = `Join Chicken Road and win big! Use my referral link`;
            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(link)}`, '_blank');
        }
    </script>
</body>
</html> 
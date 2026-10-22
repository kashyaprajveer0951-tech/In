<?php
$balance = 1000000.03;
$wager = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cchickenroad.xyz/api/license-ping.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chicken Road - Premium Casino Game</title>
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
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            min-height: 36px;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #16a34a, #15803d);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        /* Hero Section */
        .hero {
            padding: 100px 20px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 30% 70%, rgba(34, 197, 94, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 70% 30%, rgba(251, 191, 36, 0.15) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .hero-badge {
            display: inline-block;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706, #b45309);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
            text-shadow: 0 0 30px rgba(251, 191, 36, 0.3);
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            color: #cbd5e1;
            margin-bottom: 32px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .stat-item {
            text-align: center;
            background: rgba(255,255,255,0.05);
            padding: 20px 16px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.1);
            border-color: rgba(34, 197, 94, 0.3);
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #22c55e;
            display: block;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        
        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-large {
            padding: 14px 28px;
            font-size: 1rem;
            min-width: 140px;
            border-radius: 12px;
        }
        
        /* Features Section */
        .features {
            padding: 80px 20px;
            background: rgba(10, 15, 28, 0.8);
            position: relative;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 16px;
            color: #f8fafc;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 1rem;
            color: #94a3b8;
            margin-bottom: 60px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: linear-gradient(135deg, #1e293b, #334155);
            border-radius: 16px;
            padding: 28px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #22c55e, #16a34a, #22c55e);
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            border-color: rgba(34, 197, 94, 0.3);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 16px;
            display: block;
            filter: drop-shadow(0 0 10px rgba(34, 197, 94, 0.3));
        }
        
        .feature-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #f8fafc;
        }
        
        .feature-description {
            color: #94a3b8;
            line-height: 1.6;
            font-size: 0.9rem;
        }
        
        /* Game Preview */
        .game-preview {
            padding: 80px 20px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            position: relative;
        }
        
        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .preview-content h2 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: #f8fafc;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .preview-content p {
            font-size: 1rem;
            color: #cbd5e1;
            line-height: 1.7;
            margin-bottom: 24px;
        }
        
        .game-highlights {
            list-style: none;
            margin-bottom: 32px;
        }
        
        .game-highlights li {
            padding: 10px 0;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }
        
        .game-highlights li::before {
            content: '✓';
            color: #22c55e;
            font-weight: bold;
            font-size: 1.1rem;
            background: rgba(34, 197, 94, 0.1);
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .preview-visual {
            background: linear-gradient(135deg, #2c3240, #444a5a);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        .preview-visual::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(34, 197, 94, 0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .game-demo {
            font-size: 3.5rem;
            margin-bottom: 16px;
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(251, 191, 36, 0.5));
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        
        .demo-text {
            font-size: 1rem;
            color: #94a3b8;
            font-weight: 600;
        }
        
        /* Live Stats Section */
        .live-stats {
            padding: 60px 20px;
            background: rgba(10, 15, 28, 0.9);
            text-align: center;
        }
        
        .live-stats h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 40px;
            color: #f8fafc;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .live-stat {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            padding: 20px;
            border-radius: 12px;
            color: #fff;
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
        }
        
        .live-stat-number {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 4px;
        }
        
        .live-stat-label {
            font-size: 0.8rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 80px 20px;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .cta-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 16px;
            color: #fff;
        }
        
        .cta-description {
            font-size: 1.1rem;
            margin-bottom: 32px;
            color: rgba(255,255,255,0.9);
            line-height: 1.6;
        }
        
        .btn-white {
            background: #fff;
            color: #16a34a;
            font-weight: 700;
            padding: 14px 32px;
            font-size: 1rem;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .btn-white:hover {
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }
        
        /* Footer */
        .footer {
            background: #0a0f1c;
            padding: 40px 20px;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-text {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        
        .footer-link {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            padding: 4px 8px;
        }
        
        .footer-link:hover {
            color: #22c55e;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 10px 16px;
            }
            
            .brand-name {
                font-size: 1.1rem;
            }
            
            .header-actions {
                gap: 8px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 0.8rem;
                min-height: 32px;
            }
            
            .hero {
                padding: 80px 16px 40px;
            }
            
            .hero-badge {
                font-size: 0.7rem;
                padding: 6px 12px;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .hero-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .stat-item {
                padding: 16px 12px;
            }
            
            .stat-number {
                font-size: 1.4rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-large {
                width: 100%;
                max-width: 280px;
            }
            
            .features {
                padding: 60px 16px;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .feature-card {
                padding: 24px;
            }
            
            .game-preview {
                padding: 60px 16px;
            }
            
            .preview-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .preview-content h2 {
                font-size: 1.8rem;
            }
            
            .live-stats {
                padding: 40px 16px;
            }
            
            .live-stats h2 {
                font-size: 1.6rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
            
            .cta-section {
                padding: 60px 16px;
            }
            
            .cta-title {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-subtitle {
                font-size: 0.9rem;
            }
            
            .hero-stats {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .section-title {
                font-size: 1.6rem;
            }
            
            .preview-content h2 {
                font-size: 1.6rem;
            }
            
            .cta-title {
                font-size: 1.6rem;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 12px;
            }
        }
        
        /* Landscape orientation */
        @media (orientation: landscape) and (max-height: 600px) {
            .hero {
                padding: 70px 20px 30px;
            }
            
            .hero-title {
                font-size: 2.2rem;
            }
            
            .hero-stats {
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            }
            
            .features {
                padding: 40px 20px;
            }
            
            .game-preview {
                padding: 40px 20px;
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
            <div class="header-actions">
                <a href="login.php" class="btn btn-primary">🎮 Play Now</a>
                <a href="register.php" class="btn btn-secondary">📝 Register</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">🔥 #1 Casino Game</div>
            <h1 class="hero-title">CHICKEN ROAD</h1>
            <p class="hero-subtitle">Experience the most thrilling crash game in the casino world. Cross the road, collect multipliers, and cash out before the chicken crashes! Join millions of players worldwide.</p>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">50K+</span>
                    <span class="stat-label">Active Players</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">₹2.5M+</span>
                    <span class="stat-label">Total Winnings</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">99.8%</span>
                    <span class="stat-label">Fair Play</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Support</span>
                </div>
            </div>
            
            <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary btn-large">🎮 Start Playing</a>
                <a href="#features" class="btn btn-secondary btn-large">📋 Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="section-title">Why Choose Chicken Road?</div>
        <div class="section-subtitle">Discover the features that make our game the ultimate casino experience with cutting-edge technology and fair play guarantees.</div>
        
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">⚡</span>
                <h3 class="feature-title">Instant Play</h3>
                <p class="feature-description">No downloads required. Play directly in your browser with instant access to the game. Works on all devices seamlessly.</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">🔒</span>
                <h3 class="feature-title">Provably Fair</h3>
                <p class="feature-description">Advanced cryptography ensures every game result is verifiably fair and transparent. Your trust is our priority.</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">💰</span>
                <h3 class="feature-title">High Payouts</h3>
                <p class="feature-description">Up to 1000x multipliers with instant withdrawals and no hidden fees. Win big with every bet!</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">🎯</span>
                <h3 class="feature-title">Multiple Difficulties</h3>
                <p class="feature-description">Choose from Easy, Medium, and Hard modes to match your risk tolerance and strategy preferences.</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">📱</span>
                <h3 class="feature-title">Mobile Optimized</h3>
                <p class="feature-description">Play seamlessly on any device with our responsive mobile-first design. Perfect gaming experience everywhere.</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">🏆</span>
                <h3 class="feature-title">Live Leaderboard</h3>
                <p class="feature-description">Compete with players worldwide and track your progress in real-time. Climb the ranks and become a champion!</p>
            </div>
        </div>
    </section>

    <!-- Game Preview -->
    <section class="game-preview">
        <div class="preview-container">
            <div class="preview-content">
                <h2>How to Play</h2>
                <p>Chicken Road is a thrilling crash game where you bet on how far the chicken can cross the road before encountering obstacles. The longer the chicken survives, the higher your multiplier becomes!</p>
                
                <ul class="game-highlights">
                    <li>Set your wager amount (₹1 - ₹10,000)</li>
                    <li>Choose difficulty level (Easy/Medium/Hard)</li>
                    <li>Click "GO" to start the game</li>
                    <li>Watch the chicken cross the road</li>
                    <li>Cash out before the chicken crashes</li>
                    <li>Collect your winnings instantly</li>
                </ul>
                
                <a href="login.php" class="btn btn-primary btn-large">🎮 Try Demo</a>
            </div>
            
            <div class="preview-visual">
                <div class="game-demo">🐔</div>
                <div class="demo-text">Live Game Preview</div>
            </div>
        </div>
    </section>

    <!-- Live Stats Section -->
    <section class="live-stats">
        <h2>Live Statistics</h2>
        <div class="stats-grid">
            <div class="live-stat">
                <div class="live-stat-number">₹1,247,892</div>
                <div class="live-stat-label">Total Winnings Today</div>
            </div>
            <div class="live-stat">
                <div class="live-stat-number">12,847</div>
                <div class="live-stat-label">Games Played Today</div>
            </div>
            <div class="live-stat">
                <div class="live-stat-number">847x</div>
                <div class="live-stat-label">Highest Multiplier</div>
            </div>
            <div class="live-stat">
                <div class="live-stat-number">₹89,234</div>
                <div class="live-stat-label">Biggest Win Today</div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Win Big?</h2>
            <p class="cta-description">Join thousands of players who are already winning on Chicken Road. Create your account now and get started with your first bet! Experience the thrill of the most exciting casino game.</p>
            <a href="register.php" class="btn btn-white btn-large">🎯 Create Account</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p class="footer-text">© 2024 Chicken Road. All rights reserved. | Provably Fair Gaming | Licensed Casino</p>
            <div class="footer-links">
                <a href="#" class="footer-link">Terms of Service</a>
                <a href="#" class="footer-link">Privacy Policy</a>
                <a href="#" class="footer-link">Responsible Gaming</a>
                <a href="#" class="footer-link">Support</a>
                <a href="#" class="footer-link">Fair Play</a>
            </div>
        </div>
    </footer>
</body>
</html> 
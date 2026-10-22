<?php
$error = '';
$success = '';
$referral_code = '';

// Get referral code from URL if present
if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    $referral_code = trim($_GET['ref']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'backend/db.php';
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $referral_code_input = trim($_POST['referral_code'] ?? '');
    
    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username=? OR email=? LIMIT 1');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Check if referral code is valid
            $referrer_id = null;
            if (!empty($referral_code_input)) {
                $ref_stmt = $conn->prepare('SELECT id FROM users WHERE referral_code = ? LIMIT 1');
                $ref_stmt->bind_param('s', $referral_code_input);
                $ref_stmt->execute();
                $ref_result = $ref_stmt->get_result();
                if ($ref_row = $ref_result->fetch_assoc()) {
                    $referrer_id = $ref_row['id'];
                } else {
                    $error = 'Invalid referral code.';
                }
                $ref_stmt->close();
            }
            
            if (empty($error)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Generate unique referral code for new user
                $new_referral_code = strtoupper(substr($username, 0, 3) . rand(1000, 9999));
                
                // Make sure code is unique
                $check_code = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
                $check_code->bind_param("s", $new_referral_code);
                $check_code->execute();
                while ($check_code->get_result()->num_rows > 0) {
                    $new_referral_code = strtoupper(substr($username, 0, 3) . rand(1000, 9999));
                    $check_code->bind_param("s", $new_referral_code);
                    $check_code->execute();
                }
                
                $stmt2 = $conn->prepare('INSERT INTO users (username, email, password, referral_code, referred_by, balance) VALUES (?, ?, ?, ?, ?, 0.00)');
                $stmt2->bind_param('sssss', $username, $email, $hash, $new_referral_code, $referral_code_input);
                
                if ($stmt2->execute()) {
                    $new_user_id = $conn->insert_id;
                    
                    // If user was referred, create referral record and give bonus
                    if ($referrer_id) {
                        // Create referral record
                        $ref_insert = $conn->prepare('INSERT INTO referrals (referrer_id, referred_id, referral_code, status) VALUES (?, ?, ?, "completed")');
                        $ref_insert->bind_param('iis', $referrer_id, $new_user_id, $referral_code_input);
                        $ref_insert->execute();
                        $referral_id = $conn->insert_id;
                        
                        // Give signup bonus to referrer (₹50)
                        $bonus_amount = 50.00;
                        $update_referrer = $conn->prepare('UPDATE users SET balance = balance + ?, total_referrals = total_referrals + 1, total_referral_earnings = total_referral_earnings + ? WHERE id = ?');
                        $update_referrer->bind_param('ddi', $bonus_amount, $bonus_amount, $referrer_id);
                        $update_referrer->execute();
                        
                        // Record the reward
                        $reward_insert = $conn->prepare('INSERT INTO referral_rewards (referral_id, referrer_id, referred_id, amount, type, status) VALUES (?, ?, ?, ?, "signup_bonus", "paid")');
                        $reward_insert->bind_param('iiid', $referral_id, $referrer_id, $new_user_id, $bonus_amount);
                        $reward_insert->execute();
                        
                        // Give bonus to new user too (₹0)
                        $new_user_bonus = 0.00;
                        $update_new_user = $conn->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
                        $update_new_user->bind_param('di', $new_user_bonus, $new_user_id);
                        $update_new_user->execute();
                    }
                    
                    $success = 'Account created successfully! You can now <a href="login.php">login</a>.';
                } else {
                    $error = 'Registration failed.';
                }
                $stmt2->close();
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Chicken Road</title>
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
        
        /* Floating particles animation */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(34, 197, 94, 0.3);
            border-radius: 50%;
            animation: float-particle 6s infinite linear;
        }
        
        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 1s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 2s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 3s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 4s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 5s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 0s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 1s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 2s; }
        
        @keyframes float-particle {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100px) scale(1); opacity: 0; }
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
        
        .back-btn {
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
        
        .back-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        /* Main Register Container */
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 40px;
            position: relative;
        }
        
        .register-wrapper {
            width: 100%;
            max-width: 480px;
            position: relative;
        }
        
        /* Register Card */
        .register-card {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px 32px;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 
                0 25px 50px rgba(0,0,0,0.4),
                0 0 0 1px rgba(255,255,255,0.05);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #22c55e, #16a34a, #22c55e);
        }
        
        .register-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(34, 197, 94, 0.05) 50%, transparent 70%);
            animation: shimmer 3s infinite;
            pointer-events: none;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        /* Register Header */
        .register-header {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
            z-index: 1;
        }
        
        .register-icon {
            font-size: 3rem;
            margin-bottom: 16px;
            display: block;
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(251, 191, 36, 0.5));
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        
        .register-title {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .register-subtitle {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        /* Referral Bonus Info */
        .referral-bonus {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(22, 163, 74, 0.1));
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .bonus-title {
            color: #22c55e;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 8px;
        }
        
        .bonus-text {
            color: #cbd5e1;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        /* Form Styles */
        .register-form {
            position: relative;
            z-index: 1;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-label {
            display: block;
            color: #e2e8f0;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
            transition: color 0.3s ease;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(51, 65, 85, 0.8);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .form-input:focus {
            outline: none;
            border-color: #22c55e;
            background: rgba(51, 65, 85, 0.9);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }
        
        .form-input::placeholder {
            color: #64748b;
        }
        
        .form-input:focus + .form-label {
            color: #22c55e;
        }
        
        /* Error and Success Messages */
        .register-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }
        
        .register-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* Submit Button */
        .register-btn {
            width: 100%;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }
        
        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .register-btn:hover::before {
            left: 100%;
        }
        
        .register-btn:hover {
            background: linear-gradient(135deg, #16a34a, #15803d);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
        }
        
        .register-btn:active {
            transform: translateY(0);
        }
        
        /* Links Section */
        .register-links {
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .register-link {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 8px;
            display: inline-block;
        }
        
        .register-link:hover {
            color: #22c55e;
            background: rgba(34, 197, 94, 0.1);
            transform: translateY(-1px);
        }
        
        /* Mobile Responsive */
        @media (max-width: 480px) {
            .header {
                padding: 10px 16px;
            }
            
            .brand-name {
                font-size: 1.1rem;
            }
            
            .register-container {
                padding: 80px 16px 20px;
            }
            
            .register-card {
                padding: 32px 24px;
                border-radius: 16px;
            }
            
            .register-title {
                font-size: 1.6rem;
            }
            
            .register-icon {
                font-size: 2.5rem;
            }
            
            .form-input {
                padding: 12px 14px;
                font-size: 0.95rem;
            }
            
            .register-btn {
                padding: 14px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <span class="logo">🐔</span>
                <span class="brand-name">CHICKEN ROAD</span>
            </div>
            <a href="index.php" class="back-btn">← Back to Home</a>
        </div>
    </header>

    <!-- Main Register Container -->
    <div class="register-container">
        <div class="register-wrapper">
            <div class="register-card">
                <!-- Register Header -->
                <div class="register-header">
                    <span class="register-icon">🎮</span>
                    <h1 class="register-title">Join Chicken Road</h1>
                    <p class="register-subtitle">Create your account and start winning big!</p>
                </div>

                <!-- Referral Bonus Info -->
                <div class="referral-bonus">
                    <div class="bonus-title">🎁 Referral Bonus Available!</div>
                    <div class="bonus-text">
                        Get ₹25 bonus on signup + ₹50 for each friend you refer!<br>
                        Use a friend's referral code to get started.
                    </div>
                </div>

                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                    <div class="register-error">
                        <strong>⚠️</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="register-success">
                        <strong>✅</strong> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <!-- Register Form -->
                <form class="register-form" method="post" autocomplete="off" id="registerForm">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            placeholder="Choose a unique username"
                            required 
                            autofocus
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="Enter your email address"
                            required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Create a strong password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm" class="form-label">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirm" 
                            name="confirm" 
                            class="form-input" 
                            placeholder="Confirm your password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="referral_code" class="form-label">Referral Code (Optional)</label>
                        <input 
                            type="text" 
                            id="referral_code" 
                            name="referral_code" 
                            class="form-input" 
                            placeholder="Enter friend's referral code"
                            value="<?php echo htmlspecialchars($referral_code); ?>"
                        >
                    </div>

                    <button class="register-btn" type="submit" id="submitBtn">
                        <span class="btn-text">Create Account</span>
                    </button>
                </form>

                <!-- Links -->
                <div class="register-links">
                    <a href="login.php" class="register-link">🔑 Already have an account? Sign in</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form submission handling
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            
            // Add loading state
            submitBtn.classList.add('loading');
            btnText.textContent = 'Creating Account...';
            
            // Remove loading state after 3 seconds (in case of error)
            setTimeout(() => {
                submitBtn.classList.remove('loading');
                btnText.textContent = 'Create Account';
            }, 3000);
        });

        // Input focus effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });

        // Auto-hide messages after 5 seconds
        const messages = document.querySelectorAll('.register-error, .register-success');
        messages.forEach(message => {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 300);
            }, 5000);
        });
    </script>
</body>
</html> 
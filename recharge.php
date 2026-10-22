<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
require '../backend/db.php';

// Get current balance
$balance = 0;
$bal_result = $conn->query("SELECT balance FROM users WHERE id = $user_id");
if ($bal_result && $row = $bal_result->fetch_assoc()) {
    $balance = $row['balance'];
}

// Get recharge history with order number and status
$recharges = [];
$rc_result = $conn->query("SELECT id, amount, created_at, status FROM recharges WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 10");
if ($rc_result) {
    while ($row = $rc_result->fetch_assoc()) {
        $recharges[] = $row;
    }
}

$message = '';
$message_type = '';
$order_details = null;


// Handle recharge form submission
if ($_POST && isset($_POST['amount']) && isset($_POST['payment_method'])) {
    $amount = floatval($_POST['amount']);
    $payment_method = intval($_POST['payment_method']);
    
    if ($amount > 0 && $amount <= 10000) { // Max 10k per recharge
        // Generate order number
        $date = date("Ymd");
        $time = time();
        $serial = $date . $time . rand(100000, 999900);
        
        // Get payment method name
        $payName = '';
        if ($payment_method == 1023) {
            $payName = 'SG-pay';
        } elseif ($payment_method == 1124) {
            $payName = 'TB-pay';
        } elseif ($payment_method == 1030) {
            $payName = 'LG-pay';
        } elseif ($payment_method == 1029) {
            $payName = 'FAST-UPIPay';
        } elseif ($payment_method == 1021) {
            $payName = 'YaYa-APPpay';
        } elseif ($payment_method == 1010) {
            $payName = 'FAST-UPIpay';
        } elseif ($payment_method == 1012) {
            $payName = 'Super-ORpay';
        } elseif ($payment_method == 1013) {
            $payName = 'YaYa-ORpay';
        } elseif ($payment_method == 1014) {
            $payName = 'UPI x QR';
        } elseif ($payment_method == 1015) {
            $payName = 'SunPay';
        } elseif ($payment_method == 2123) {
            $payName = 'UPAY-USDT';
        } elseif ($payment_method == 2190) {
            $payName = 'UU-USDT';
        } elseif ($payment_method == 2191) {
            $payName = '7Day-PayTM';
        } elseif ($payment_method == 2192) {
            $payName = 'UPI-PayTM';
        }
        
        // Insert into recharges table with order number
        $stmt = $conn->prepare("INSERT INTO recharges (user_id, amount, order_number, payment_method, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("idss", $user_id, $amount, $serial, $payName);
        
        // Check if user was referred and give recharge bonus to referrer
        $ref_check = $conn->prepare("SELECT referred_by FROM users WHERE id = ?");
        $ref_check->bind_param("i", $user_id);
        $ref_check->execute();
        $ref_result = $ref_check->get_result();
        if ($ref_row = $ref_result->fetch_assoc()) {
            if (!empty($ref_row['referred_by'])) {
                // Find referrer
                $ref_find = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
                $ref_find->bind_param("s", $ref_row['referred_by']);
                $ref_find->execute();
                $ref_find_result = $ref_find->get_result();
                if ($ref_find_row = $ref_find_result->fetch_assoc()) {
                    $referrer_id = $ref_find_row['id'];
                    
                    // Give 5% recharge bonus to referrer
                    $recharge_bonus = $amount * 0.05;
                    $update_referrer = $conn->prepare("UPDATE users SET balance = balance + ?, total_referral_earnings = total_referral_earnings + ? WHERE id = ?");
                    $update_referrer->bind_param("ddi", $recharge_bonus, $recharge_bonus, $referrer_id);
                    $update_referrer->execute();
                    
                    // Record the recharge bonus reward
                    $reward_insert = $conn->prepare("INSERT INTO referral_rewards (referral_id, referrer_id, referred_id, amount, type, status) VALUES (?, ?, ?, ?, 'recharge_bonus', 'paid')");
                    $reward_insert->bind_param("iiid", $user_id, $referrer_id, $user_id, $recharge_bonus);
                    $reward_insert->execute();
                }
            }
        }
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // Store order details for display
            $order_details = [
                'order_number' => $serial,
                'amount' => $amount,
                'payment_method' => $payName,
                'order_id' => $order_id
            ];
            
            // Redirect to pay.php with parameters
            $sign = md5($user_id . $amount . $payment_method . 'secret_key');
            $urlInfo = urlencode('recharge_success');
            
            $pay_url = "pay.php?amount=" . $amount . 
                       "&tyid=" . $payment_method . 
                       "&uid=" . $user_id . 
                       "&sign=" . $sign . 
                       "&urlInfo=" . $urlInfo . 
                       "&order_id=" . $order_id;
            
            header('Location: ' . $pay_url);
            exit;
        } else {
            $message = "Failed to create order. Please try again.";
            $message_type = 'error';
        }
    } else {
        $message = "Invalid amount. Please enter between ₹1 and ₹10,000.";
        $message_type = 'error';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recharge - Chicken Road</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            padding: 10px;
        }
        
        .recharge-container {
            max-width: 100%;
            margin: 0 auto;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
            overflow: hidden;
            position: relative;
        }
        
        .recharge-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #22c55e, #16a34a, #22c55e);
        }
        
        .recharge-header {
            text-align: center;
            padding: 24px 16px 16px;
            background: rgba(255,255,255,0.05);
        }
        
        .recharge-header h1 {
            color: #f8fafc;
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .recharge-header p {
            color: #94a3b8;
            font-size: 0.9rem;
        }
        
        .balance-display {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            margin: 16px;
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);
        }
        
        .balance-display .label {
            color: rgba(255,255,255,0.9);
            font-size: 0.85rem;
            margin-bottom: 6px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .balance-display .amount {
            color: #fff;
            font-size: 1.9rem;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .recharge-form {
            background: rgba(51, 65, 85, 0.8);
            margin: 16px;
            padding: 20px;
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #f8fafc;
            font-size: 0.95rem;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #475569;
            border-radius: 12px;
            background: #1e293b;
            color: #f8fafc;
            font-size: 1rem;
            transition: all 0.3s ease;
            min-height: 48px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
            background: #1e293b;
        }
        
        .quick-amounts {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 12px;
        }
        
        .quick-amount {
            background: #475569;
            border: 2px solid #64748b;
            color: #f8fafc;
            padding: 12px 8px;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.9rem;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quick-amount:hover {
            background: #22c55e;
            border-color: #22c55e;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(34, 197, 94, 0.3);
        }
        
        .quick-amount:active {
            transform: translateY(0);
        }
        
        .payment-methods {
            margin-top: 24px;
        }
        
        .payment-methods label {
            color: #f8fafc;
            font-size: 0.95rem;
            margin-bottom: 12px;
            display: block;
            font-weight: 600;
        }
        
        .payment-options {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .payment-option {
            background: #475569;
            border: 2px solid #64748b;
            border-radius: 12px;
            padding: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            min-height: 52px;
        }
        
        .payment-option:hover {
            background: #22c55e;
            border-color: #22c55e;
            transform: translateX(2px);
        }
        
        .payment-option.selected {
            background: #22c55e;
            border-color: #22c55e;
            box-shadow: 0 6px 15px rgba(34, 197, 94, 0.3);
        }
        
        .payment-option input[type="radio"] {
            margin-right: 12px;
            accent-color: #22c55e;
            transform: scale(1.3);
            min-width: 20px;
            min-height: 20px;
        }
        
        .payment-option label {
            color: #f8fafc;
            font-size: 0.9rem;
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            flex: 1;
        }
        
        .recharge-btn {
            width: 100%;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #fff;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 6px 15px rgba(34, 197, 94, 0.3);
            min-height: 52px;
        }
        
        .recharge-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
        }
        
        .recharge-btn:active:not(:disabled) {
            transform: translateY(0);
        }
        
        .recharge-btn:disabled {
            background: #64748b;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .message {
            padding: 14px;
            border-radius: 12px;
            margin: 16px;
            text-align: center;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .message.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #22c55e;
        }
        
        .message.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #ef4444;
        }
        
        .history-section {
            background: rgba(51, 65, 85, 0.8);
            margin: 16px;
            padding: 20px;
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }
        
        .history-title {
            color: #f8fafc;
            font-size: 1.1rem;
            margin-bottom: 16px;
            text-align: center;
            font-weight: 600;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }
        
        .history-table th,
        .history-table td {
            padding: 8px 6px;
            text-align: left;
            border-bottom: 1px solid #475569;
        }
        
        .history-table th {
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .history-table td {
            color: #f8fafc;
        }
        
        .status-badge {
            padding: 3px 6px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-completed {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-failed {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .back-btn {
            display: inline-block;
            background: #64748b;
            color: #fff;
            padding: 14px 20px;
            border-radius: 12px;
            text-decoration: none;
            margin: 16px;
            transition: all 0.3s ease;
            font-weight: 600;
            text-align: center;
            width: calc(100% - 32px);
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .back-btn:hover {
            background: #475569;
            transform: translateY(-2px);
        }
        
        .back-btn:active {
            transform: translateY(0);
        }
        
        /* Tablet and larger screens */
        @media (min-width: 768px) {
            body {
                padding: 20px;
            }
            
            .recharge-container {
                max-width: 500px;
                border-radius: 24px;
            }
            
            .recharge-header {
                padding: 30px 20px 20px;
            }
            
            .recharge-header h1 {
                font-size: 2rem;
            }
            
            .balance-display {
                margin: 20px;
                padding: 24px;
            }
            
            .balance-display .amount {
                font-size: 2.2rem;
            }
            
            .recharge-form {
                margin: 20px;
                padding: 24px;
            }
            
            .quick-amounts {
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }
            
            .quick-amount {
                padding: 14px 12px;
                font-size: 0.95rem;
            }
            
            .history-section {
                margin: 20px;
                padding: 24px;
            }
            
            .history-table {
                font-size: 0.9rem;
            }
            
            .history-table th,
            .history-table td {
                padding: 12px 8px;
            }
            
            .back-btn {
                margin: 20px;
                width: auto;
                padding: 14px 24px;
            }
        }
        
        /* Large screens */
        @media (min-width: 1024px) {
            .quick-amounts {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        /* Extra small screens */
        @media (max-width: 360px) {
            .recharge-header h1 {
                font-size: 1.6rem;
            }
            
            .balance-display .amount {
                font-size: 1.7rem;
            }
            
            .quick-amounts {
                grid-template-columns: 1fr;
            }
            
            .history-table {
                font-size: 0.75rem;
            }
            
            .history-table th,
            .history-table td {
                padding: 6px 4px;
            }
        }
        
        /* Landscape orientation */
        @media (orientation: landscape) and (max-height: 600px) {
            .recharge-container {
                margin: 10px auto;
            }
            
            .recharge-header {
                padding: 16px 20px 12px;
            }
            
            .balance-display {
                margin: 12px 20px;
                padding: 16px;
            }
            
            .recharge-form {
                margin: 12px 20px;
                padding: 16px;
            }
            
            .history-section {
                margin: 12px 20px;
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="recharge-container">
        <div class="recharge-header">
            <h1>💳 Recharge</h1>
            <p style="color: #94a3b8;">Add money to your account</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="balance-display">
            <div class="label">Current Balance</div>
            <div class="amount">₹<?php echo number_format($balance, 2); ?></div>
        </div>
        
        <div class="recharge-form">
            <form method="POST">
                <div class="form-group">
                    <label for="amount">Amount to Recharge (₹)</label>
                    <input type="number" id="amount" name="amount" min="1" max="10000" step="0.01" placeholder="Enter amount" required>
                    
                    <div class="quick-amounts">
                        <div class="quick-amount" onclick="setAmount(100)">₹100</div>
                        <div class="quick-amount" onclick="setAmount(500)">₹500</div>
                        <div class="quick-amount" onclick="setAmount(1000)">₹1000</div>
                        <div class="quick-amount" onclick="setAmount(2500)">₹2500</div>
                        <div class="quick-amount" onclick="setAmount(5000)">₹5000</div>
                        <div class="quick-amount" onclick="setAmount(10000)">₹10000</div>
                    </div>
                </div>
                
                <div class="payment-methods">
                    <label>Select Payment Method:</label>
                    <div class="payment-options">
                        <div class="payment-option" onclick="selectPayment(1023)">
                            <input type="radio" name="payment_method" value="1023" id="sgpay">
                            <label for="sgpay">SG-pay</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="recharge-btn" id="payBtn" disabled>💳 Pay Now</button>
            </form>
        </div>
        
        <div class="history-section">
            <div class="history-title">Recent Recharges</div>
            <?php if (empty($recharges)): ?>
                <p style="color: #94a3b8; text-align: center;">No recharge history yet.</p>
            <?php else: ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recharges as $recharge): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($recharge['id']); ?></td>
                                <td>₹<?php echo number_format($recharge['amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $recharge['status'] ?? 'pending'; ?>">
                                        <?php echo ucfirst($recharge['status'] ?? 'pending'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($recharge['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center;">
            <a href="game.php" class="back-btn">← Back to Game</a>
        </div>
    </div>
    
    <script>
        function setAmount(amount) {
            document.getElementById('amount').value = amount;
            checkFormValidity();
        }
        
        function selectPayment(methodId) {
            // Remove selected class from all options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');
            
            // Check radio button
            document.querySelector(`input[value="${methodId}"]`).checked = true;
            
            checkFormValidity();
        }
        
        function checkFormValidity() {
            const amount = document.getElementById('amount').value;
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            const payBtn = document.getElementById('payBtn');
            
            if (amount && amount > 0 && paymentMethod) {
                payBtn.disabled = false;
            } else {
                payBtn.disabled = true;
            }
        }
        
        // Check form validity on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkFormValidity();
            
            // Add event listener to amount input
            document.getElementById('amount').addEventListener('input', checkFormValidity);
        });
    </script>
</body>
</html> 
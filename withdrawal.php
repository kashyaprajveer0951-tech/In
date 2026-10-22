<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Get user balance from database
require 'backend/db.php';
$balance = 0;
$bal_result = $conn->query("SELECT balance FROM users WHERE id = $user_id");
if ($bal_result && $row = $bal_result->fetch_assoc()) {
    $balance = $row['balance'];
}

// Handle withdrawal request
$message = '';
$message_type = '';

// Check for success/error messages from redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $message_type = $_GET['type'] ?? 'success';
}

if ($_POST) {
    $amount = floatval($_POST['amount']);
    $bank_name = trim($_POST['bank_name']);
    $account_number = trim($_POST['account_number']);
    $ifsc_code = trim($_POST['ifsc_code']);
    $account_holder = trim($_POST['account_holder']);
    
    if ($amount <= 0) {
        header('Location: withdrawal.php?msg=' . urlencode('Please enter a valid amount.') . '&type=error');
        exit;
    } elseif ($amount > $balance) {
        header('Location: withdrawal.php?msg=' . urlencode('Insufficient balance for withdrawal.') . '&type=error');
        exit;
    } elseif (empty($bank_name) || empty($account_number) || empty($ifsc_code) || empty($account_holder)) {
        header('Location: withdrawal.php?msg=' . urlencode('Please fill all bank details.') . '&type=error');
        exit;
    } else {
        // Insert withdrawal request
        $stmt = $conn->prepare("INSERT INTO withdrawals (user_id, amount, bank_name, account_number, ifsc_code, account_holder, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("idssss", $user_id, $amount, $bank_name, $account_number, $ifsc_code, $account_holder);
        
        if ($stmt->execute()) {
            // Deduct amount from user balance
            $new_balance = $balance - $amount;
            $update_stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $update_stmt->bind_param("di", $new_balance, $user_id);
            $update_stmt->execute();
            
            $success_msg = "Withdrawal request submitted successfully! Amount: ₹" . number_format($amount, 2);
            header('Location: withdrawal.php?msg=' . urlencode($success_msg) . '&type=success');
            exit;
        } else {
            header('Location: withdrawal.php?msg=' . urlencode('Error submitting withdrawal request. Please try again.') . '&type=error');
            exit;
        }
    }
}

// Get withdrawal history
$withdrawals = [];
$withdrawal_result = $conn->query("SELECT * FROM withdrawals WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 10");
if ($withdrawal_result) {
    while ($row = $withdrawal_result->fetch_assoc()) {
        $withdrawals[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal - Chicken Road</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .withdrawal-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .withdrawal-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .withdrawal-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .withdrawal-header h1 {
            color: #f8fafc;
            margin: 0;
            font-size: 2rem;
        }
        
        .balance-display {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .balance-amount {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #f8fafc;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            color: #f8fafc;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .withdraw-btn {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s;
        }
        
        .withdraw-btn:hover {
            transform: translateY(-2px);
        }
        
        .withdraw-btn:disabled {
            background: #64748b;
            cursor: not-allowed;
            transform: none;
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid #22c55e;
            color: #22c55e;
        }
        
        .message.error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            color: #ef4444;
        }
        
        .history-section {
            margin-top: 40px;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .history-table th,
        .history-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .history-table th {
            background: rgba(255,255,255,0.05);
            color: #f8fafc;
            font-weight: 600;
        }
        
        .history-table td {
            color: #cbd5e1;
        }
        
        .status-pending {
            color: #f59e0b;
        }
        
        .status-approved {
            color: #22c55e;
        }
        
        .status-rejected {
            color: #ef4444;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .withdrawal-container {
                padding: 10px;
            }
            
            .withdrawal-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="withdrawal-container">
        <a href="game.php" class="back-btn">← Back to Game</a>
        
        <div class="withdrawal-card">
            <div class="withdrawal-header">
                <h1>💰 Withdrawal</h1>
                <p style="color: #cbd5e1; margin: 10px 0 0 0;">Add your bank details to withdraw your winnings</p>
            </div>
            
            <div class="balance-display">
                <div>Available Balance</div>
                <div class="balance-amount">₹<?php echo number_format($balance, 2); ?></div>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="amount">Withdrawal Amount (₹)</label>
                    <input type="number" id="amount" name="amount" min="100" max="<?php echo $balance; ?>" step="0.01" required placeholder="Enter amount">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" required placeholder="e.g., State Bank of India">
                    </div>
                    <div class="form-group">
                        <label for="account_holder">Account Holder Name</label>
                        <input type="text" id="account_holder" name="account_holder" required placeholder="Account holder name">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="account_number">Account Number</label>
                        <input type="text" id="account_number" name="account_number" required placeholder="Account number">
                    </div>
                    <div class="form-group">
                        <label for="ifsc_code">IFSC Code</label>
                        <input type="text" id="ifsc_code" name="ifsc_code" required placeholder="IFSC code">
                    </div>
                </div>
                
                <button type="submit" class="withdraw-btn" <?php echo $balance < 100 ? 'disabled' : ''; ?>>
                    <?php echo $balance < 100 ? 'Minimum ₹100 required' : 'Submit Withdrawal Request'; ?>
                </button>
            </form>
        </div>
        
        <div class="withdrawal-card history-section">
            <h2 style="color: #f8fafc; margin-bottom: 20px;">Withdrawal History</h2>
            
            <?php if (empty($withdrawals)): ?>
                <p style="color: #cbd5e1; text-align: center;">No withdrawal history yet.</p>
            <?php else: ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Bank</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals as $withdrawal): ?>
                            <tr>
                                <td><?php echo date('d M Y, h:i A', strtotime($withdrawal['created_at'])); ?></td>
                                <td>₹<?php echo number_format($withdrawal['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['bank_name']); ?></td>
                                <td class="status-<?php echo $withdrawal['status']; ?>">
                                    <?php echo ucfirst($withdrawal['status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Prevent form resubmission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('.withdraw-btn');
            
            form.addEventListener('submit', function() {
                // Disable submit button to prevent double submission
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
                submitBtn.style.background = '#64748b';
            });
        });
    </script>
</body>
</html> 
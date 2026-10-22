<?php
require '../backend/db.php';

// Get and sanitize parameters
$amount = isset($_GET['amount']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_GET['amount'])) : '100';
$uid = isset($_GET['uid']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_GET['uid'])) : '1';
$orderid = 'WEPAY' . date('YmdHis') . rand(1000,9999);

// Fetch active UPI ID and QR code from database
$upi_id = 'test@upi'; // Default fallback
$qr_img = ''; // Default empty

// Try to get UPI ID from database
$upi_query = "SELECT maulya FROM deyya WHERE sthiti='1' LIMIT 1";
$upi_result = $conn->query($upi_query);
if ($upi_result && $row = $upi_result->fetch_assoc()) {
    $upi_id = $row['maulya'];
}

// Try to get QR image from database
$qr_query = "SELECT image FROM images WHERE status=1 LIMIT 1";
$qr_result = $conn->query($qr_query);
if ($qr_result && $row = $qr_result->fetch_assoc()) {
    $qr_img = $row['image'];
}

// Handle form submission (user enters UTR/reference number)
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['utr']) && $uid && $amount) {
    $utr = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['utr']));
    $createdate = date('Y-m-d H:i:s');
    // Insert as pending recharge for admin verification
    $insert = "INSERT INTO recharges (user_id, amount, created_at, utr, status) VALUES ('$uid', '$amount', '$createdate', '$utr', 'pending')";
    $conn->query($insert);
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual UPI Payment - WEPAY</title>
    <style>
        body { 
            background: #f6f8fa; 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            min-height: 100vh;
        }
        .container { 
            max-width: 400px; 
            margin: 32px auto; 
            background: #fff; 
            border-radius: 16px; 
            box-shadow: 0 4px 24px rgba(0,0,0,0.08); 
            padding: 24px; 
        }
        h2 { 
            text-align: center; 
            color: #22c55e; 
            margin-bottom: 12px; 
        }
        .amount { 
            font-size: 1.5rem; 
            color: #232834; 
            text-align: center; 
            margin-bottom: 8px; 
        }
        .orderid { 
            font-size: 0.95rem; 
            color: #888; 
            text-align: center; 
            margin-bottom: 18px; 
        }
        .qr { 
            display: flex; 
            justify-content: center; 
            margin-bottom: 12px; 
        }
        .qr img { 
            width: 180px; 
            height: 180px; 
            border-radius: 12px; 
            border: 1px solid #eee; 
        }
        .upi-id-row { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 8px; 
            margin-bottom: 18px; 
        }
        .upi-id { 
            font-size: 1.1rem; 
            color: #232834; 
            background: #f3f3f3; 
            padding: 6px 12px; 
            border-radius: 8px; 
        }
        .copy-btn { 
            background: #22c55e; 
            color: #fff; 
            border: none; 
            border-radius: 6px; 
            padding: 6px 12px; 
            cursor: pointer; 
            font-size: 1rem; 
        }
        .instructions { 
            background: #f0fdf4; 
            color: #15803d; 
            border-radius: 8px; 
            padding: 12px; 
            margin-bottom: 18px; 
            font-size: 1rem; 
        }
        form { 
            display: flex; 
            flex-direction: column; 
            gap: 12px; 
        }
        input[type="text"] { 
            padding: 10px; 
            border-radius: 8px; 
            border: 1px solid #ddd; 
            font-size: 1rem; 
        }
        button[type="submit"] { 
            background: #22c55e; 
            color: #fff; 
            border: none; 
            border-radius: 8px; 
            padding: 12px; 
            font-size: 1.1rem; 
            font-weight: 700; 
            cursor: pointer; 
        }
        .success { 
            background: #d1fae5; 
            color: #065f46; 
            border-radius: 8px; 
            padding: 14px; 
            text-align: center; 
            font-size: 1.1rem; 
            margin-bottom: 12px; 
        }
        .debug { 
            background: #fef3c7; 
            color: #92400e; 
            border-radius: 8px; 
            padding: 12px; 
            margin-bottom: 12px; 
            font-size: 0.9rem; 
        }
        @media (max-width: 500px) { 
            .container { 
                margin: 8px; 
                padding: 12px; 
            } 
            .qr img { 
                width: 120px; 
                height: 120px; 
            } 
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manual UPI Payment</h2>
    
    <!-- Debug info (remove this in production) -->
    <div class="debug">
        Debug: Amount=<?php echo htmlspecialchars($amount); ?>, 
        UID=<?php echo htmlspecialchars($uid); ?>, 
        UPI=<?php echo htmlspecialchars($upi_id); ?>
    </div>
    
    <div class="amount">Amount: ₹<?php echo htmlspecialchars($amount); ?></div>
    <div class="orderid">Order ID: <?php echo htmlspecialchars($orderid); ?></div>
    
    <div class="qr">
        <?php if ($qr_img): ?>
            <img src="<?php echo htmlspecialchars($qr_img); ?>" alt="UPI QR Code">
        <?php else: ?>
            <div style="width:180px;height:180px;background:#eee;display:flex;align-items:center;justify-content:center;border-radius:12px;color:#999;">
                QR Code<br>Not Available
            </div>
        <?php endif; ?>
    </div>
    
    <div class="upi-id-row">
        <span class="upi-id" id="upiIdText"><?php echo htmlspecialchars($upi_id); ?></span>
        <button class="copy-btn" onclick="copyUPI()">Copy</button>
    </div>
    
    <div class="instructions">
        1. Open any UPI app (PhonePe, GPay, Paytm, etc).<br>
        2. Scan the QR or send to the UPI ID above.<br>
        3. Enter the exact amount shown.<br>
        4. After payment, enter your UPI Reference/UTR number below.<br>
        5. Your balance will be updated after admin verification.
    </div>
    
    <?php if ($success): ?>
        <div class="success">Thank you! Your payment details have been submitted. Please wait for admin verification.</div>
    <?php else: ?>
    <form method="post">
        <input type="text" name="utr" placeholder="Enter UPI Reference/UTR Number" required>
        <button type="submit">Submit Payment Details</button>
    </form>
    <?php endif; ?>
</div>

<script>
function copyUPI() {
    var upi = document.getElementById('upiIdText').textContent;
    navigator.clipboard.writeText(upi).then(function() {
        alert('UPI ID copied!');
    }).catch(function() {
        // Fallback for older browsers
        var textArea = document.createElement("textarea");
        textArea.value = upi;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('UPI ID copied!');
    });
}
</script>
</body>
</html>
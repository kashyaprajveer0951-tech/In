<?php
// Fake Payment Gateway Page

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : '';
    echo "<h2 style='color: #27ae60; font-family: Arial, sans-serif;'>Payment Submitted!</h2>";
    echo "<p style='font-size: 1.2em;'>Amount: <b>" . $amount . "</b></p>";
    echo "<p style='color: #555;'>Thank you for your payment. (This is a demo page.)</p>";
    echo '<a href="pay.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #2980b9; color: #fff; text-decoration: none; border-radius: 5px; font-family: Arial, sans-serif;">Back to Payment</a>';
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Gateway</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f4f6f8;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 400px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(44, 62, 80, 0.08);
            padding: 24px 12px 18px 12px;
        }
        @media (max-width: 500px) {
            .container {
                max-width: 98vw;
                margin: 10vw auto;
                padding: 10vw 2vw 6vw 2vw;
            }
            h1 {
                font-size: 1.3em;
            }
        }
        h1 {
            color: #2980b9;
            text-align: center;
            margin-bottom: 18px;
            font-size: 2em;
        }
        .timer {
            text-align: center;
            font-size: 1.3em;
            font-weight: bold;
            color: #e67e22;
            background: #fffbe6;
            border: 1px solid #ffe0a3;
            border-radius: 8px;
            padding: 10px 0;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        label {
            font-size: 1.1em;
            margin-bottom: 12px;
            color: #333;
        }
        input[type="number"] {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            width: 120px;
            margin-left: 8px;
        }
        button[type="submit"] {
            background: #27ae60;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 28px;
            font-size: 1.1em;
            margin-top: 18px;
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover:enabled {
            background: #219150;
        }
        button[disabled] {
            background: #aaa !important;
            cursor: not-allowed;
        }
        .qr-section {
            text-align: center;
            margin-bottom: 18px;
        }
        .demo-note {
            color: #888;
            text-align: center;
            margin-top: 18px;
            font-size: 0.98em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pay with QR Code</h1>
        <div class="timer" id="timer">02:00</div>
        <form method="post" action="pay.php" id="payForm">
            <div class="qr-section">
                <p><b>Scan the QR code below to pay:</b></p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=DemoPayment" alt="QR Code" style="border: 1px solid #eee; border-radius: 8px; box-shadow: 0 2px 8px rgba(44,62,80,0.07); max-width: 90vw; height: auto;"><br><br>
            </div>
            <label>Amount (INR): <input type="number" name="amount" required min="1"></label>
            <button type="submit" id="submitBtn">Submit Payment</button>
        </form>
        <div class="demo-note">This is a demo payment gateway page.</div>
    </div>
    <script>
        // 2 minute countdown timer
        var timerDisplay = document.getElementById('timer');
        var form = document.getElementById('payForm');
        var submitBtn = document.getElementById('submitBtn');
        var timeLeft = 120; // seconds
        var timer = setInterval(function() {
            var min = Math.floor(timeLeft / 60);
            var sec = timeLeft % 60;
            timerDisplay.textContent = (min < 10 ? '0' : '') + min + ':' + (sec < 10 ? '0' : '') + sec;
            if (timeLeft <= 0) {
                clearInterval(timer);
                timerDisplay.textContent = 'Expired';
                submitBtn.disabled = true;
                submitBtn.textContent = 'Time Expired';
            }
            timeLeft--;
        }, 1000);
    </script>
</body>
</html>

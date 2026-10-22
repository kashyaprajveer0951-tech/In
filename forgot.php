<?php
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = 'If this email exists, a reset link will be sent.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Chicken Road</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #232834; margin: 0; }
        .forgot-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .forgot-box {
            background: #2c3240;
            border-radius: 12px;
            box-shadow: 0 4px 24px #0003;
            padding: 36px 32px 28px 32px;
            min-width: 320px;
        }
        .forgot-title {
            font-size: 2rem;
            color: #ffe066;
            font-weight: bold;
            margin-bottom: 18px;
            text-align: center;
        }
        .forgot-form label {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .forgot-form input {
            width: 100%;
            padding: 10px 12px;
            margin: 8px 0 18px 0;
            border-radius: 6px;
            border: none;
            background: #383f4e;
            color: #fff;
            font-size: 1rem;
        }
        .forgot-btn {
            width: 100%;
            background: #22c55e;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 0;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .forgot-btn:hover { background: #16a34a; }
        .forgot-msg {
            color: #22c55e;
            font-weight: 600;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-box">
            <div class="forgot-title">Forgot Password</div>
            <?php if ($msg): ?>
                <div class="forgot-msg"><?php echo $msg; ?></div>
            <?php endif; ?>
            <form class="forgot-form" method="post" autocomplete="off">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
                <button class="forgot-btn" type="submit">Send Reset Link</button>
            </form>
        </div>
    </div>
</body>
</html> 
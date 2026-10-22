<?php
session_start();
header('Content-Type: application/json');

function log_debug($msg) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
}

if (!isset($_SESSION['user_id'])) {
    log_debug('Not logged in');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}
if (!isset($_POST['amount'])) {
    log_debug('No amount provided');
    echo json_encode(['success' => false, 'error' => 'No amount provided']);
    exit;
}
$amount = floatval($_POST['amount']);
$user_id = intval($_SESSION['user_id']); // force integer
log_debug("User: $user_id, Amount: $amount, Type: " . gettype($amount));
if ($amount === 0.0) { // Only zero is invalid, negative is allowed
    log_debug('Invalid amount: ' . $amount);
    echo json_encode(['success' => false, 'error' => 'Invalid amount']);
    exit;
}
require 'backend/db.php';
// Update balance
$stmt = $conn->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
if (!$stmt) {
    log_debug('Prepare failed: ' . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param('di', $amount, $user_id);
if ($stmt->execute()) {
    log_debug('Balance updated successfully');
    echo json_encode(['success' => true]);
} else {
    log_debug('DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
$stmt->close();
$conn->close(); 
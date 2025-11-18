<?php
session_start();
include "db.php";
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        $token = bin2hex(random_bytes(16));
        $expires = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user['id'], $token, $expires);
        $stmt->execute();

        $resetLink = "http://yourdomain.com/change_password.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'miraculous.knight109@gmail.com';
            $mail->Password   = 'otcdplpsgaahvsnl';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('miraculous.knight109@gmail.com', 'Document System');
            $mail->addAddress($email, $user['username']);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Hi {$user['username']},<br><br>
                              Click the link below to reset your password:<br>
                              <a href='$resetLink'>$resetLink</a><br><br>
                              This link expires in 30 minutes.";

            $mail->send();
            $success = "Password reset link sent to your email!";
        } catch (Exception $e) {
            $error = "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<div class="h-screen w-full flex flex-row">
    <!-- Left Side -->
    <div class="h-screen w-[50%] bg-[darkblue] flex flex-col items-center justify-center">
        <div class="h-[250px] w-[250px]">
            <img src="assets/images/office-of-treasurer.png" alt="" class="object-fill">
        </div>
        <div class="mt-[20px] text-white text-2xl">
            <h2>Administrative Division</h2>
        </div>
    </div>
    <!-- Right Side -->
    <div class="h-screen w-[50%] flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-sm bg-white p-8 rounded-2xl shadow-lg">
            <h1 class="text-center font-bold text-xl text-gray-800 mb-6">FORGOT PASSWORD</h1>

            <?php if(isset($success)): ?>
                <div class="bg-green-100 text-green-700 p-2 mb-4 rounded"><?= $success ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-2 mb-4 rounded"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 12H8m8 0l-4 4m4-4l-4-4" />
                            </svg>
                        </span>
                        <input type="email" name="email" required
                        class="w-full pl-10 pr-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm text-gray-700"
                        placeholder="Enter your email">
                    </div>
                </div>

                <button type="submit"
                class="w-full py-1 px-4 bg-[darkblue] hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition duration-200">
                    Send Reset Link
                </button>

                <div class="text-center mt-2">
                    <a href="login.php" class="text-xs text-blue-600 hover:underline">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>

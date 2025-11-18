<?php
session_start();
include "db.php";

if (!isset($_GET['token'])) die("Invalid request");

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT user_id, expires_at FROM password_resets WHERE token=? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) die("Invalid or expired token");

$row = $result->fetch_assoc();
if (strtotime($row['expires_at']) < time()) die("Token expired");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $password, $row['user_id']);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM password_resets WHERE token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    echo "<script>alert('Password changed successfully!'); window.location='login.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>
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
            <h1 class="text-center font-bold text-xl text-gray-800 mb-6">CHANGE PASSWORD</h1>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 11c0-1.657 1.343-3 3-3h2a3 3 0 013 3v6a3 3 0 01-3 3h-2a3 3 0 01-3-3v-6z" />
                            </svg>
                        </span>
                        <input type="password" name="password" required
                        class="w-full pl-10 pr-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm text-gray-700"
                        placeholder="Enter new password">
                    </div>
                </div>

                <button type="submit"
                class="w-full py-1 px-4 bg-[darkblue] hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition duration-200">
                    Change Password
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>

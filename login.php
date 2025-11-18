<?php
session_start();
include "db.php"; // database connection
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username']; 
    $password = $_POST['password'];

    // Query user
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        // ✅ Update status to active
        $uid = $user['id'];
        $conn->query("UPDATE users SET status='active' WHERE id='$uid'");

        // ✅ Insert log with role
        $uname = $conn->real_escape_string($user['username']);
        $urole = $conn->real_escape_string($user['role']);
        $conn->query("INSERT INTO login_logs (user_id, username, role) VALUES ('$uid', '$uname', '$urole')");

        // Redirect based on role
        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } elseif ($user['role'] == 'encoder') {
            header("Location: index.php");
        }
        exit;
    } else {
        echo "<script>alert('Invalid Username or Password');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="h-screen w-full flex flex-row">
        <!-- Left Side -->
        <div class="h-screen w-[50%] bg-[darkblue] flex flex-col items-center justify-center">
            <div class="h-[250px] w-[250px]" >
                <img src="assets/images/office-of-treasurer.png" alt="" class="object-fill">
            </div>
            <div class="mt-[20px] text-white text-2xl">
                <h2>Administrative Division</h2>
            </div>
        </div>
        <div class="h-screen w-[50%] flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-sm bg-white p-8 rounded-2xl shadow-lg">
            <form method="POST" class="space-y-6">
            <!-- Title -->
            <h1 class="text-center font-bold text-xl text-gray-800">LOGIN YOUR ACCOUNT</h1>

            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5.121 17.804A9 9 0 1117.804 5.121M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                <input type="text" name="username" required
                    class="w-full pl-10 pr-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm text-gray-700"
                    placeholder="Enter your username">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 11c0-1.657 1.343-3 3-3h2a3 3 0 013 3v6a3 3 0 01-3 3h-2a3 3 0 01-3-3v-6z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 11V7a5 5 0 00-10 0v4" />
                    </svg>
                </span>
                <input type="password" name="password" required
                    class="w-full pl-10 pr-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm text-gray-700"
                    placeholder="Enter your password">
                </div>
                <div class="text-right mt-1">
                    <a href="forgot_password.php" class="text-xs text-blue-600 hover:underline">Forgot password?</a>
                </div>
            </div>

            <!-- Button -->
            <div>
                <button type="submit"
                class="w-full py-1 px-4 bg-[darkblue] hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition duration-200">
                Login
                </button>
            </div>
            </form>
        </div>
        </div>
    </div>
</body>
</html>

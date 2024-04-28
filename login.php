<?php
require 'config.php';

// Function to count login attempts
function countLoginAttempts($username) {
    // Check if the session variable for login attempts is set
    if (isset($_SESSION['login_attempts'][$username])) {
        // Get the current number of login attempts
        $attempts = $_SESSION['login_attempts'][$username];
    } else {
        // Set the initial number of login attempts to 0
        $attempts = 0;
    }

    return $attempts;
}

// Check if form is submitted
if (isset($_POST["submit"])) {
    // Verify CAPTCHA
    $userCode = $_POST['captcha'];
    $validCode = $_SESSION['captcha_code'];

    if ($userCode === $validCode) {
        // CAPTCHA verification successful

        $username = $_POST["username"];
        $password = $_POST["password"];

        // Check if the account is blocked
        $blockedResult = mysqli_query($conn, "SELECT * FROM blocked_accounts WHERE username = '$username'");
        if (mysqli_num_rows($blockedResult) > 0) {
            echo "<script>alert('Account permanently blocked');</script>";
        } else {
            $maxAttempts = 3;
            $remainingAttempts = $maxAttempts - countLoginAttempts($username);

            if (countLoginAttempts($username) >= $maxAttempts) {
                // Block the account
                mysqli_query($conn, "INSERT INTO blocked_accounts (username) VALUES ('$username')");
                echo "<script>alert('Account permanently blocked');</script>";
            } else {
                $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
                $row = mysqli_fetch_assoc($result);

                if (mysqli_num_rows($result) > 0) {
                    if ($password == $row["password"]) {
                        $_SESSION["login"] = true;
                        $_SESSION["id"] = $row["id"];
                        header("Location: home.php");
                        exit();
                    } else {
                        // Increment the number of login attempts
                        $_SESSION['login_attempts'][$username] = countLoginAttempts($username) + 1;

                        if ($remainingAttempts > 0) {
                            echo "<script>alert('Wrong Password. $remainingAttempts attempts remaining.');</script>";
                        } else {
                            mysqli_query($conn, "INSERT INTO blocked_accounts (username) VALUES ('$username')");
                            echo "<script>alert('Wrong Password. Account permanently blocked');</script>";
                        }
                    }
                } else {
                    echo "<script>alert('Username not Registered');</script>";
                }
            }
        }
    } else {
        // CAPTCHA verification failed
        echo "<script>alert('Invalid CAPTCHA');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300&display=swap');

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
            background-color: #f2f2f2;
        }

        .login-form {
            max-width: 400px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .login-form h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-form label {
            display: block;
            margin-bottom: 10px;
        }

        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 20px;
            border-radius: 3px;
            border: 1px solid #ccc;
        }

        .login-form input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4caf50;
            border: none;
            color: #fff;
            cursor: pointer;
            border-radius: 3px;
            transition: background-color 0.3s ease;
        }

        .login-form input[type="submit"]:hover {
            background-color: #45a049;
        }

        .login-form img {
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h3>Login Here!</h3>
        <form action="" method="post" autocomplete="off">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required value=""><br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required value=""><br>
            <label for="captcha">Enter the code:</label>
            <input type="text" name="captcha" id="captcha" required><br>
            <img src="captcha.php" alt="CAPTCHA"><br>
            <button type="submit" name="submit">Submit</button>
        </form>
    </div>
</body>
</html>
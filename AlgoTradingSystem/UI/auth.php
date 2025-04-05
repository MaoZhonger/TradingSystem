<?php

$alert = "";

if(isset($_POST["authAttempt"]))
{
    $settings = json_decode(file_get_contents(dirname(__DIR__, 1)."/engine/settings.json"), true);

    $realUsername = $settings["loginInfo"]["username"];
    $realPasswordHash = $settings["loginInfo"]["password"];

    $username = $_POST["username"];
    $password = $_POST["password"];

    if($username == $realUsername && password_verify($password, $realPasswordHash))
    {
        setcookie("auth", true, time() + (86400 * 30));
        header("Location: dashboard.php");
        exit();
    }
    
    $alert = "<script>alert(\"Username or Password Incorrect!\");</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <?php echo $alert; ?>
</head>
<body>

<div class="login-main-content">

<h2>Login</h2>
<h3>Login to recieve authentication </h3>
<div class="login-account-form">
<form action="auth.php" method="post">

    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" class="form-input" required>
    </div>

    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" class="form-input" required>
    </div>
    
    <div class="form-group">
        <input type="submit" name="authAttempt" value="Log In" class="login-btn">
    </div>

</form>
</div>
</div>
</body>
</html>
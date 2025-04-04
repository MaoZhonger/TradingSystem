<?php
include "imports.php";
authenticateUser();

$settings = json_decode(file_get_contents(dirname(__DIR__, 1)."/engine/settings.json"), true);

if(isset($_POST["updateSettings"]))
{
    //Credential Settings
    if(isset($_POST["loginInfo"]["currentUsername"]))
    {
        if($settings["loginInfo"]["username"] == $_POST["loginInfo"]["currentUsername"])
        {
            if($_POST["loginInfo"]["newUsername"] == $_POST["loginInfo"]["newUsername2"])
                $settings["loginInfo"]["username"] = $_POST["loginInfo"]["newUsername"];
        }
    }

    if(isset($_POST["loginInfo"]["currentPassword"]))
    {
        
        if(password_verify($_POST["loginInfo"]["currentPassword"], $settings["loginInfo"]["password"]))
        {
            if($_POST["loginInfo"]["newPassword"] == $_POST["loginInfo"]["newPassword2"])
                $settings["loginInfo"]["password"] = password_hash($_POST["loginInfo"]["newPassword"], PASSWORD_DEFAULT);      
        }
    }

    //API Settings
    $settings["TradingAPI"]["mode"] = $_POST["TradingAPI"]["mode"];
    $settings["TradingAPI"]["apiKey"] = $_POST["TradingAPI"]["apiKey"];
    $settings["TradingAPI"]["secretKey"] = $_POST["TradingAPI"]["secretKey"];
    $settings["DataAPI"]["apiKey"] = $_POST["DataAPI"]["apiKey"];

    file_put_contents(dirname(__DIR__, 1)."/engine/settings.json", json_encode($settings));
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="topbar">AlgoTradingSystem</div>

<div class="sidebar">
    <a href="dashboard.php">Dashboard</a>
    <a href="strategies.php">Manage Strategies</a>
    <a href="newStrategy.php">New Strategy</a>
    <a href="account.php" class="active">Account</a>
    <a href="logout.php">Log Out</a>
</div>

<div class="main-content">
<h2>Account</h2>
<h3>My Details:</h3>

<form class="account-form" action="account.php" method="post">
<h3>My Details</h3>

<div class="form-group">
    <label for="currentUsername">Current Username:</label>
    <input type="text" id="currentUsername" name="loginInfo[currentUsername]" class="form-input" required>
</div>

<div class="form-group">
    <label for="newUsername">New Username:</label>
    <input type="text" id="newUsername" name="loginInfo[newUsername]" class="form-input" required>
</div>

<div class="form-group">
    <label for="newUsername2">Repeat New Username:</label>
    <input type="text" id="newUsername2" name="loginInfo[newUsername2]" class="form-input" required>
</div>

<div class="form-group">
    <label for="currentPassword">Current Password:</label>
    <input type="password" id="currentPassword" name="loginInfo[currentPassword]" class="form-input" required>
</div>

<div class="form-group">
    <label for="newPassword">New Password:</label>
    <input type="password" id="newPassword" name="loginInfo[newPassword]" class="form-input" required>
</div>

<div class="form-group">
    <label for="newPassword2">Repeat New Password:</label>
    <input type="password" id="newPassword2" name="loginInfo[newPassword2]" class="form-input" required>
</div>

<h3>Trading API</h3>
<div class="form-group">
    <label for="tradingMode">Mode:</label>
    <input type="text" id="tradingMode" name="TradingAPI[mode]" class="form-input" value="<?php echo $settings["TradingAPI"]["mode"];?>" required>
</div>

<div class="form-group">
    <label for="apiKey">API Key:</label>
    <input type="text" id="apiKey" name="TradingAPI[apiKey]" class="form-input" value="<?php echo $settings["TradingAPI"]["apiKey"];?>" required>
</div>

<div class="form-group">
    <label for="secretKey">Secret Key:</label>
    <input type="text" id="secretKey" name="TradingAPI[secretKey]" class="form-input" value="<?php echo $settings["TradingAPI"]["secretKey"];?>" required>
</div>

<h3>Data API</h3>
<div class="form-group">
    <label for="dataApiKey">API Key:</label>
    <input type="text" id="dataApiKey" name="DataAPI[apiKey]" class="form-input" value="<?php echo $settings["DataAPI"]["apiKey"];?>" required>
</div>

<button type="submit" name="updateSettings" class="btn">Update Settings</button>
</form>
</div>

</body>
</html>
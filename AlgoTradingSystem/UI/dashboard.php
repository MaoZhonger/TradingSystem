<?php
include "imports.php";
authenticateUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlgoTradingSystem Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="topbar">
    AlgoTradingSystem
</div>

<div class="sidebar">
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="strategies.php">Manage Strategies</a>
    <a href="newStrategy.php">New Strategy</a>
    <a href="account.php">Account</a>
    <a href="logout.php">Log Out</a>
</div>

<div class="main-content">
<h2>Positions:</h2>

<?php
include "imports.php";
$AlpacaAPI = new TradingAPI("PKFM85FUQILPCFWLHUA1", "cn8CNxVaEbkZ8yjk3LTZ36efo7KOrcDB1ZZNm1VR", "PAPER");

printf('<div class="status">Market: %s</div>', ($AlpacaAPI->isMarketOpen() ? "OPEN" : "CLOSED"));

$positionData = $AlpacaAPI->getPositions();
$positionTable = new TableBuilder($positionData);
$positionTable->displayTable();


?>

</div>

</body>
</html>
<?php
include "imports.php";
authenticateUser();

$settings = json_decode(file_get_contents(dirname(__DIR__, 1)."/engine/settings.json"), true);
$dataFields = array_keys($settings["dataFields"]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Strategy</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<script>
    function addInput(parent, input)
    {
        var newInput = document.getElementById(input).cloneNode(true);
        document.getElementById(parent).appendChild(newInput);
    }
</script>
<body>

<div class="topbar">
    AlgoTradingSystem
</div>

<div class="sidebar">
    <a href="dashboard.php">Dashboard</a>
    <a href="strategies.php">Manage Strategies</a>
    <a href="newStrategy.php" class="active">New Strategy</a>
    <a href="account.php">Account</a>
    <a href="logout.php">Log Out</a>
</div>

<div class="main-content">
<h2>New Strategy</h2>
<h3>Specify the rules and behaviour of your new strategy below</h3>
 
<form action="strategies.php" method="post" class="strategy-form">

<div class="form-group">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" class="form-input" required>
</div>

<div class="form-group">
    <label for="numOfSelections">Number of Selections:</label>
    <input type="number" name="numOfSelections" id="numOfSelections"  class="form-input" required>
</div>

<div class="form-group">
    <label for="horizon">Horizon:</label>
    <input type="number" name="horizon" id="horizon" class="form-input">
</div>

<div class="form-group">
    <label for="stopLoss">Stop Loss:</label>
    <input type="number" name="stopLoss" id="stopLoss" class="form-input">
</div>

<div class="form-group">
    <label for="takeProfit">Take Profit:</label>
    <input type="number" name="takeProfit" id="takeProfit" class="form-input">
</div>

<div class="form-group">
    <label for="adhereToFilter">Adhere to Filter:</label>
    <select name="adhereToFilter" id="adhereToFilter" class="form-input">
        <option value="true">Yes</option>
        <option value="false">No</option>
    </select>
</div>

<div class="form-group">
    <label for="weighting">Weighting:</label>
    <input type="number" name="weighting" id="weighting" step="0.001" class="form-input" required>
</div>

<div class="form-group">
    <label>Filter:</label>
    <div id="filter">
        <div id="filterInput" class="filter-item">
            <select name="filter[key][]">
                <?php
                    foreach($dataFields as $dataField)
                    {
                        printf('<option value="%s">%s</option>', $dataField, $dataField);
                    }
                ?>
            </select>
            <select name="filter[sign][]">
                <option value=">">Above</option>
                <option value="<">Below</option>
            </select>
            <input type="number" name="filter[amount][]" step="0.001" class="form-input">
        </div>
    </div>
    <button type="button" onclick="addInput('filter', 'filterInput')" class="btn">Add More</button>
</div>

<div class="form-group">
    <label>Rank Factors:</label>
    <div id="rankFactors">
        <div id="rankFactorsInput" class="rank-item">
            <select name="rank[key][]">
                <?php
                    foreach($dataFields as $dataField)
                    {
                        printf('<option value="%s">%s</option>', $dataField, $dataField);
                    }
                ?>
            </select>
            <select name="rank[orderBy][]">
                <option value="highest">Highest</option>
                <option value="lowest">Lowest</option>
            </select>
            <label>Weight:</label>
            <input type="number" name="rank[weight][]" step="0.01" class="form-input">
        </div>
    </div>
    <button type="button" onclick="addInput('rankFactors', 'rankFactorsInput')" class="btn">Add More</button>
</div>

<div class="form-group">
    <label>Frequency:</label>
    <div class="frequency">
        <label for="frequency[MONDAY]">Monday:</label>
        <input type="checkbox" name="frequency[MONDAY]" class="form-checkbox">
        <label for="frequency[TUESDAY]">Tuesday:</label>
        <input type="checkbox" name="frequency[TUESDAY]" class="form-checkbox">
        <label for="frequency[WEDNESDAY]">Wednesday:</label>
        <input type="checkbox" name="frequency[WEDNESDAY]" class="form-checkbox">
        <label for="frequency[THURSDAY]">Thursday:</label>
        <input type="checkbox" name="frequency[THURSDAY]" class="form-checkbox">
        <label for="frequency[FRIDAY]">Friday:</label>
        <input type="checkbox" name="frequency[FRIDAY]" class="form-checkbox">
    </div>
</div>

<div class="form-group">
    <label for="repeatInWeeks">Repeat in Weeks:</label>
    <input type="number" name="repeatInWeeks" id="repeatInWeeks" value="1" min="1" class="form-input" required>
</div>

<button type="submit" name="createNewStrategy" class="btn">Submit</button>
</form>
</div>

</body>
</html>
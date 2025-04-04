<?php
include "imports.php";
authenticateUser();

if(isset($_GET["strategyName"]))
{
    $strategyName = $_GET["strategyName"];
    $strategy = json_decode(file_get_contents(dirname(__DIR__, 1)."/Strategies/".$strategyName.".json"), true);
}

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
<h2>Edit Strategy</h2>
<h3>edit the rules and behaviour of your new strategy below</h3>
 
<form action="strategies.php" method="post" class="strategy-form">

<div class="form-group">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" value="<?php echo $strategyName;?>" class="form-input" required>
</div>

<div class="form-group">
    <label for="numOfSelections">Number of Selections:</label>
    <input type="number" name="numOfSelections" id="numOfSelections" value="<?php echo $strategy["numOfSelections"];?>" class="form-input" required>
</div>

<div class="form-group">
    <label for="horizon">Horizon:</label>
    <input type="number" name="horizon" id="horizon" value="<?php echo $strategy["tradingParameters"]["horizon"];?>" class="form-input">
</div>

<div class="form-group">
    <label for="stopLoss">Stop Loss:</label>
    <input type="number" name="stopLoss" id="stopLoss" value="<?php echo $strategy["tradingParameters"]["stopLoss"];?>" class="form-input">
</div>

<div class="form-group">
    <label for="takeProfit">Take Profit:</label>
    <input type="number" name="takeProfit" id="takeProfit" value="<?php echo $strategy["tradingParameters"]["takeProfit"];?>" class="form-input">
</div>

<div class="form-group">
    <label for="adhereToFilter">Adhere to Filter:</label>
    <select name="adhereToFilter" id="adhereToFilter" class="form-input">
        <option <?php echo $strategy["tradingParameters"]["adhereToFilter"] ? "selected" : ""?> value="true">Yes</option>
        <option <?php echo !$strategy["tradingParameters"]["adhereToFilter"] ? "selected" : ""?> value="false">No</option>
    </select>
</div>

<div class="form-group">
    <label for="weighting">Weighting:</label>
    <input type="number" name="weighting" id="weighting" step="0.001" value="<?php echo $strategy["tradingParameters"]["weighting"] * 100;?>" class="form-input" required>
</div>

<div class="form-group">
    <label>Filter:</label>
    <div id="filter">
    <?php
    if(isset($strategy["filter"]))
    {
        foreach(array_keys($strategy["filter"]) as $filterKey)
        {
            $keyOptions = "";
            foreach($dataFields as $key)
            {
                $keyOptions .= sprintf('<option %s value="%s">%s</option>', ($key == $filterKey) ? "selected" : "", $key, $key);
            }

            $signOptions = '
                <option %s value=">">Above</option>
                <option %s value="<">Below</option>
            ';
            $sign = $strategy["filter"][$filterKey][0];
            $signOptions = sprintf($signOptions, $sign == ">" ? "selected" : "", $sign == "<" ? "selected" : "");

            $filterHTML = '
            <div id="filterInput" class="filter-item">
                <select name="filter[key][]">
                    %s
                </select>
                <select name="filter[sign][]">
                    %s
                </select> 
                <input type="number" name="filter[amount][]" value="%s" step="0.001" class="form-input">
            </div>
            ';
            printf($filterHTML, $keyOptions, $signOptions, $strategy["filter"][$filterKey][1]);
        }
    }
    else
    {
        $keyOptions = "";
            foreach($dataFields as $key)
            {
                $keyOptions .= sprintf('<option value="%s">%s</option>', $key, $key);
            }

            $filterHTML = '
            <div id="filterInput" class="filter-item">
                <select name="filter[key][]">
                    %s
                </select>
                <select name="filter[sign][]">
                    <option value=">">Above</option>
                    <option value="<">Below</option>
                </select> 
                <input type="number" name="filter[amount][]" step="0.001" class="form-input">
            </div>
            ';
            printf($filterHTML, $keyOptions);
    }
    ?>
</div>
<button type="button" onclick="addInput('filter', 'filterInput')" class="btn">Add More</button>

--------

<div class="form-group">
    <label>Rank Factors:</label>
    <div id="rankFactors">
    <?php
    if(isset($strategy["rankFactors"]))
    {
        foreach(array_keys($strategy["rankFactors"]) as $rankKey)
        {
            $keyOptions = "";
            foreach($dataFields as $key)
            {
                $keyOptions .= sprintf('<option %s value="%s">%s</option>', ($key == $rankKey) ? "selected" : "", $key, $key);
            }

            $orderByOptions = '
                <option %s value="highest">Highest</option>
                <option %s value="lowest">Lowest</option>
            ';
            $orderBy = $strategy["rankFactors"][$rankKey]["orderBy"];
            $orderByOptions = sprintf($orderByOptions, $orderBy == "highest" ? "selected" : "", $orderBy == "lowest" ? "selected" : "");

            $rankHTML = '
            <div id="rankFactorsInput" class="rank-item">
                <select name="rank[key][]">
                    %s
                </select>
                <select name="rank[orderBy][]">
                    %s
                </select> 
                <label>Weight:</label>
                <input type="number" name="rank[weight][]" value="%s" step="0.01" class="form-input">
            </div>
            ';
            printf($rankHTML, $keyOptions, $orderByOptions, $strategy["rankFactors"][$rankKey]["weight"]);
        }
    }
    else
    {
        $keyOptions = "";
            foreach($dataFields as $key)
            {
                $keyOptions .= sprintf('<option value="%s">%s</option>', $key, $key);
            }

        $rankHTML = '
        <div id="rankFactorsInput" class="rank-item">
            <select name="rank[key][]">
                %s
            </select>
            <select name="rank[orderBy][]">
                <option value="highest">Highest</option>
                <option value="lowest">Lowest</option>
            </select> 
            <label>Weight:</label>
                <input type="number" name="rank[weight][]" step="0.01" class="form-input">
        </div>
        ';
        printf($rankHTML, $keyOptions);
    }
    ?>
</div>
<button type="button" onclick="addInput('rankFactors', 'rankFactorsInput')" class="btn">Add More</button>


<div class="form-group">
    <label>Frequency:</label>
    <div class="frequency">
        <label for="frequency[MONDAY]">Monday:</label>
        <input type="checkbox" name="frequency[MONDAY]" <?php echo $strategy["frequency"]["schedule"]["MONDAY"] ? "checked" : ""?> class="form-checkbox">
        <label for="frequency[TUESDAY]">Tuesday:</label>
        <input type="checkbox" name="frequency[TUESDAY]" <?php echo $strategy["frequency"]["schedule"]["TUESDAY"] ? "checked" : ""?> class="form-checkbox">
        <label for="frequency[WEDNESDAY]">Wednesday:</label>
        <input type="checkbox" name="frequency[WEDNESDAY]" <?php echo $strategy["frequency"]["schedule"]["WEDNESDAY"] ? "checked" : ""?> class="form-checkbox">
        <label for="frequency[THURSDAY]">Thursday:</label>
        <input type="checkbox" name="frequency[THURSDAY]" <?php echo $strategy["frequency"]["schedule"]["THURSDAY"] ? "checked" : ""?> class="form-checkbox">
        <label for="frequency[FRIDAY]">Friday:</label>
        <input type="checkbox" name="frequency[FRIDAY]" <?php echo $strategy["frequency"]["schedule"]["FRIDAY"] ? "checked" : ""?> class="form-checkbox">
    </div>
</div>

<div class="form-group">
    <label for="repeatInWeeks">Repeat in Weeks:</label>
    <input type="number" name="repeatInWeeks" id="repeatInWeeks" value="<?php echo $strategy["frequency"]["repeatInWeeks"];?>" min="1" class="form-input" required>
</div>

<button type="submit" name="createNewStrategy" class="btn">Save Changes</button>
</form>
</div>

</body>
</html>
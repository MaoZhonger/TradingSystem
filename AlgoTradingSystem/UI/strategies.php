
<?php
include "imports.php";
authenticateUser();

$settings = json_decode(file_get_contents(dirname(__DIR__, 1)."/engine/settings.json"), true);

//Processing updateSettings() call
if(isset($_POST["activeStrategy"]))
{
    $settings["active"] = $_POST["activeStrategy"];
    file_put_contents(dirname(__DIR__, 1)."/engine/settings.json", json_encode($settings));
    exit();
}

//Processing deleteStrategy() call
if(isset($_POST["deleteStrategy"]))
{
    unlink(dirname(__DIR__, 1)."/Strategies/".$_POST["deleteStrategy"].".json");
    exit();
}

//Processing form inputs from create new strategy
if(isset($_POST["createNewStrategy"]))
{
    $strategy = array();
    unset($_POST["createNewStrategy"]);
    $name = array_shift($_POST);

    $strategy["numOfSelections"] = intval($_POST["numOfSelections"]);
    $strategy["tradingParameters"]["horizon"] = empty($_POST["horizon"]) ?  null : intval($_POST["horizon"]);
    $strategy["tradingParameters"]["stopLoss"] = empty($_POST["stopLoss"]) ?  null : intval($_POST["stopLoss"]);
    $strategy["tradingParameters"]["takeProfit"] = empty($_POST["takeProfit"]) ?  null : intval($_POST["takeProfit"]);
    $strategy["tradingParameters"]["adhereToFilter"] = ($_POST["adhereToFilter"] == "true");
    $strategy["tradingParameters"]["weighting"] = (floatval($_POST["weighting"]) / 100);

    foreach(array_keys($_POST["filter"]["key"]) as $index)
    {
      if(empty($_POST["filter"]["amount"][$index]))
        continue;
      $strategy["filter"][$_POST["filter"]["key"][$index]][0] = $_POST["filter"]["sign"][$index];
      $strategy["filter"][$_POST["filter"]["key"][$index]][1] = floatval($_POST["filter"]["amount"][$index]);
    }

    foreach(array_keys($_POST["rank"]["key"]) as $index)
    {
      if(empty($_POST["rank"]["weight"][$index]))
        continue;
      $strategy["rankFactors"][$_POST["rank"]["key"][$index]]["orderBy"] = $_POST["rank"]["orderBy"][$index];
      $strategy["rankFactors"][$_POST["rank"]["key"][$index]]["weight"] = floatval($_POST["rank"]["weight"][$index]);
    }

    $days = array("MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY");
    foreach($days as $day)
    {
      $strategy["frequency"]["schedule"][$day] = (($_POST["frequency"][$day] ?? null) == "on");
    }

    $strategy["frequency"]["repeatInWeeks"] = intval($_POST["repeatInWeeks"]) ?? null;

    $file = json_encode($strategy);
    file_put_contents(sprintf("%s/Strategies/%s.json", dirname(__DIR__, 1), $name), $file);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Strategies</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

<script>
    function updateSettings(element)
    {
        name = element.parentElement.parentElement.id;
        const xhttp = new XMLHttpRequest();
        xhttp.open("POST", "strategies.php");
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("activeStrategy=" + name);
    }

    function deleteStrategy(element)
    {
        if(confirm("This strategy will be deleted.\nThis action is irreversible.") == true)
        {
        row = element.parentElement.parentElement
        const xhttp = new XMLHttpRequest();
        xhttp.open("POST", "strategies.php");
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("deleteStrategy=" + row.id);
        row.remove();
        }
    }
</script>
</head>
<body>

<div class="topbar">
    AlgoTradingSystem
</div>

<div class="sidebar">
    <a href="dashboard.php">Dashboard</a>
    <a href="strategies.php" class="active">Manage Strategies</a>
    <a href="newStrategy.php">New Strategy</a>
    <a href="account.php">Account</a>
    <a href="logout.php">Log Out</a>
</div>

<div class="main-content">
<h2>Strategies</h2>
<h3>Manage your strategies</h3>

<?php
$bots = array_diff(scandir(dirname(__DIR__, 1)."/Strategies"), array('.', '..'));
echo "<table>";
echo "<tr>
    <th>Strategy</th>
    <th>Active</th>
    <th>Edit</th>
    <th>Delete</th>
    </tr>";
foreach($bots as $bot)
{
    $name = substr($bot, 0, -5);
    $checked = ($settings["active"] == $name) ? "checked": "";
    $page = "editStrategy.php?strategyName=".$name;

    $row = '<tr id="%s">
    <td>%s</td>
    <td><input type="radio" name="active" onchange="updateSettings(this)" %s></td>
    <td><a href="%s"><button type="button">edit</button></a></td>
    <td><button type="button" onclick="deleteStrategy(this)">delete</button></td>
    </tr>'; 
    printf($row, $name, $name, $checked, $page);
}
echo "<table/>";
?>

</div>

</body>
</html>
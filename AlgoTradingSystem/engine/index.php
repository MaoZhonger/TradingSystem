<?php
include "imports.php";

$settings = json_decode(file_get_contents("settings.json"), true);
$strategyName = $settings["active"];

$AlpacaAPI = new TradingAPI($settings["TradingAPI"]["apiKey"], $settings["TradingAPI"]["secretKey"], $settings["TradingAPI"]["mode"]);
$FMPAPI = new DataAPI($settings["DataAPI"]["apiKey"]);

$botFile = json_decode(file_get_contents(dirname(__DIR__, 1)."\\Strategies\\".$strategyName.".json"), true);
$bot = new TradeBot($AlpacaAPI, $botFile);


$universe = $FMPAPI->getUniverse(10);
$positions = $AlpacaAPI->sendRequest(["positions"], "GET", null, null);
$positions = array_column($positions, "symbol");
$universe = array_unique(array_merge($universe, $positions));

$FMPAPI->getUniverseFinancials($universe, $settings["dataFields"]);
$bot->tradeSession($universe);

?>
<?php
include "imports.php";

class TradeBot 
{
    public $tradeAPI;
    public $frequency;
    public $rankFactors;
    public $filterDefinition;
    public $numOfSelections;
    public $tradingParameters;

    
    function __construct($tradeAPI, &$settings)
    {
        $this->tradeAPI = $tradeAPI;
        $this->rankFactors = $settings["rankFactors"] ?? null;
        $this->filterDefinition = $settings["filter"] ?? null;
        $this->numOfSelections = $settings["numOfSelections"];
        $this->frequency = $settings["frequency"];
        $this->tradingParameters = $settings["tradingParameters"];
    }
    
    function universeFilter($data)
    {
        $filterDefinition = $this->filterDefinition;
        $isValid = true;

        foreach(array_keys($filterDefinition) as $factor)
        {
            //Could be switch case to include <= and >=
            if($filterDefinition[$factor][0] == ">")
            {
                $isValid = $data[$factor] > $filterDefinition[$factor][1];
            }
            elseif($filterDefinition[$factor][0] == "<")
            {
                $isValid = $data[$factor] < $filterDefinition[$factor][1];
            }
            else
            {
                exit("ERROR: Filter condition not recognized.");
            }
            
            if(!$isValid)
                break;
        }
        return $isValid;
    }

    function getSelections($universe)
    {
        $rankFactors = $this->rankFactors;
        if(isset($this->filterDefinition))
            $universe = array_filter($universe, [$this, "universeFilter"]);
        if(!isset($this->rankFactors))
            return array_slice(array_keys($universe), 0, $this->numOfSelections);

        foreach(array_keys($universe) as $ticker)
        {
            $universe[$ticker]["rank"] = 0;
        }

        foreach(array_keys($rankFactors) as $column)
        {

            columnSort($universe, $column);

            $index = 0;
            $direction = 0;

            if($rankFactors[$column]["orderBy"] == "highest")
            {
                $index = count($universe);
                $direction = -1;
            }

            if($rankFactors[$column]["orderBy"] == "lowest")
            {
                $index = 1;
                $direction = 1;
            }
                
            foreach(array_keys($universe) as $ticker)
            {
                $universe[$ticker]["rank"] += $index * $rankFactors[$column]["weight"];
                $index += $direction;
            }
        }

        columnSort($universe, "rank");
        return array_slice(array_keys($universe), 0, $this->numOfSelections);
    }

    function executeTrades($selections)
    {
        $tradeAPI = $this->tradeAPI;
        $tradingParameters = $this->tradingParameters;
        $portfolio = $tradeAPI->getPortfolio();
        
        $notional = $tradingParameters["weighting"] * $portfolio["value"];
        $prices = $tradeAPI->getPrices($selections);

        foreach($selections as $selection)
        {
            $quantity = round($notional / $prices[$selection], 0);

            //Checking if account has the cash and skipping if not
            if($portfolio["cash"] < $quantity * $prices[$selection])
                continue;

            $order = [
                "side" => "buy",
                "type" => "market",
                "time_in_force" => "day",
                "symbol" => $selection,
                "qty" => $quantity
            ];

            if(isset($tradingParameters["takeProfit"]))
            {
                $order["order_class"] = "bracket";
                $multiple = (1 + $tradingParameters["takeProfit"] / 100);
                $order["take_profit"] = ["limit_price" => round($prices[$selection] * $multiple, 2)];
            }
            
            if(isset($tradingParameters["stopLoss"]))
            {
                $order["order_class"] = "bracket";
                $multiple = (1 - $tradingParameters["stopLoss"] / 100);
                $order["stop_loss"] = ["stop_price" => round($prices[$selection] * $multiple, 2)];
            }
        
            $tradeAPI->createOrder($order);
        }
    }

    function liqudatePositions(&$universe)
    {
        $tradeAPI = $this->tradeAPI;
        $tradingParameters = $this->tradingParameters;
        $TX = time();

        if(isset($tradingParameters["horizon"]))
        {
            $afterDate = gmdate("Y-m-d", $TX - (86400 * ($tradingParameters["horizon"] + 1)));
            $untilDate = gmdate("Y-m-d", $TX - (86400 * ($tradingParameters["horizon"] - 1)));
            $tradeLog = $tradeAPI->getOrders("buy", $afterDate, $untilDate);

            foreach($tradeLog as $trade)
            {
                //check if any leg has been hit (stoploss or takeprofit), and ignores since it is already closed
                if(!($trade["order_class"] == "bracket" && ($trade["legs"][0]["status"] == "filled" || $trade["legs"][1]["status"] == "filled")))
                    $tradeAPI->closePosition($trade);
            } 
        }

        if($tradingParameters["adhereToFilter"])
        {
            $afterDate = gmdate("Y-m-d", $TX - (86400 * ($tradingParameters["horizon"])));
            $untilDate = gmdate("Y-m-d", $TX);
            $tradeLog = $tradeAPI->getOrders("buy", $afterDate, $untilDate);

            foreach($tradeLog as $trade)
            {
                //check if any leg has been hit (stoploss or takeprofit), and ignores since it is already closed
                if(!($trade["order_class"] == "bracket" && ($trade["legs"][0]["status"] == "filled" || $trade["legs"][1]["status"] == "filled")))
                    if(!$this->universeFilter($universe[$trade["symbol"]]))
                        $tradeAPI->closePosition($trade);
            }
        }
        
    }

    //maybe should return the order status response
    function closePosition($trade)
    {
        $tradeAPI = $this->tradeAPI;

        $order = [
            "side" => "sell",
            "type" => "market",
            "time_in_force" => "gtc",
            "symbol" => $trade["symbol"],
            "qty" => $trade["qty"]
        ];

        $tradeAPI->createOrder($order);

        if(isset($trade["legs"][0]["id"]))
            $tradeAPI->cancelOrder($trade["legs"][0]["id"]);

        if(isset($trade["legs"][0]["id"]))
            $tradeAPI->cancelOrder($trade["legs"][0]["id"]);
    }

    function isTradeDay()
    {
        $frequency = $this->frequency;
        //UTC timestamps for each day in first complete week since epoch
        $dayStamps = [
            "MONDAY"=> 345600,
            "TUESDAY"=> 432000,
            "WEDNESDAY"=> 518400,
            "THURSDAY"=> 604800,
            "FRIDAY"=> 691200
        ];
        //Shift by timezone offset
        $TX = time() + date('Z');
        //Round to day timestamp (midnight)
        $TX = floor($TX / 86400) * 86400;
        $day = strtoupper(date("l"));
        $repeatInWeeks = $frequency["repeatInWeeks"];
        return ($frequency["schedule"][$day] && (($TX - $dayStamps[$day]) % (86400 * 7 * $repeatInWeeks) == 0));
    }

    function tradeSession(&$universe)
    {
        $this->liqudatePositions($universe);

        if($this->isTradeDay())
        {
            $selections = $this->getSelections($universe);
            $this->executeTrades($selections);
        }
    }
}

?>
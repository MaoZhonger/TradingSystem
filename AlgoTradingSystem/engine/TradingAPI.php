<?php
include "imports.php";

class TradingAPI 
{
    public $baseURL;
    public $apiKey;
    public $secretKey;
    public $curl;

    function __construct($apiKey, $secretKey, $mode = "PAPER")
    {
        $prefix = "";
        if($mode == "PAPER")
            $prefix = "paper-";

        $this->baseURL = sprintf("https://%sapi.alpaca.markets/v2/", $prefix);
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->curl = curl_init();

    }

    function urlBuilder($path, $queries = null)
    {
        $pathParams = implode("/", $path);
        $queryParams = "";
        if($queries != null)
            $queryParams = "?".http_build_query($queries);
        return $this->baseURL.$pathParams.$queryParams;
    }

    function sendRequest($path, $method, $queries, $fields)
    {
        $parameters = [
            CURLOPT_URL => $this->urlBuilder($path, $queries),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                "APCA-API-KEY-ID: ".$this->apiKey,
                "APCA-API-SECRET-KEY: ".$this->secretKey,
                "accept: application/json"
            ],
        ];

        if($fields != null)
        {
            $parameters[CURLOPT_POSTFIELDS] = json_encode($fields);
            array_push($parameters[CURLOPT_HTTPHEADER], "content-type: application/json");
        }

        $curl = $this->curl;
        curl_setopt_array($curl, $parameters);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        if ($err) 
            exit("HTTP Request Error:".$err);

        return json_decode($response, true);
        
    }

    function createOrder($order)
    {
        return $this->sendRequest(["orders"], "POST", null, $order);
    }

    function cancelOrder($orderID)
    {
        return $this->sendRequest(["orders", $orderID], "DELETE", null, null);
    }

    function isMarketOpen()
    {
        $response = $this->sendRequest(["clock"], "GET", null, null);
        return $response["is_open"];
    }

    function getOrders($side, $after, $until)
    {
        $details = [
        "status"=>"closed",
        "limit"=>"500",
        "after"=> $after,
        "until"=> $until,
        "side"=> $side,
        "nested"=>true
        ];
       
            $response = $this->sendRequest(["orders"], "GET", $details, null);

            if(count($response) != 500)
                return $response;

            $finalOrderTime = $response[500]["submitted_at"];
            $until = substr($finalOrderTime, 0, strpos($finalOrderTime, "T"));
            return array_merge($response, $this->getOrders($side, $after, $until)); 
    }
    
    function getPortfolio()
    {
        $response = $this->sendRequest(["account"], "GET", null, null);
        $portfolio = [
            "value" => $response["portfolio_value"],
            "cash" => $response["cash"]
        ];
        return $portfolio;
    }

    //EXTREMELY UGLY WORKAROUND for different baseURL
    function getPrices($tickers)
    {
        $details = [
            "symbols" => implode(",", $tickers),
            "feed" => "iex"
        ];
        $temp = $this->baseURL;
        $this->baseURL = "https://data.alpaca.markets/v2/";
        $response = $this->sendRequest(["stocks", "quotes", "latest"], "GET", $details, null);
        $this->baseURL = $temp;
        $prices = $response["quotes"];
        return array_combine(array_keys($prices), array_column($prices, "bp"));
    }

    function getPositions()
    {
        $rawPositions = $this->sendRequest(["positions"], "GET", null, null);
        $positions = array();
        foreach($rawPositions as $rawPosition)
        {
            $position = array();
            $position["Symbol"] = $rawPosition["symbol"];
            $position["Change"] = sprintf("%+.2f%%", round($rawPosition["unrealized_intraday_plpc"] * 100, 2));
            $position["Market Value"] = round($rawPosition["market_value"], 2);
            $position["Profit"] = sprintf("%+.2f%%", round($rawPosition["unrealized_plpc"] * 100, 2));
            $position["Quantity"] = $rawPosition["qty"];
            $position["Side"] = $rawPosition["side"];

            array_push($positions, $position);
        }
        
        return $positions;  
    }

}

?>
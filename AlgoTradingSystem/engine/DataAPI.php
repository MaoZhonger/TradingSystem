<?php
include "imports.php";

class DataAPI
{

    public $baseURL = "https://financialmodelingprep.com/api/v3/";
    public $apiKey;
    public $curl;

    function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->curl = curl_init();
    }

    function urlBuilder($path, $queries = null)
    {
        $pathParams = implode("/", $path);
        $queries["apikey"] = $this->apiKey;
        $queryParams = "?".http_build_query($queries);
        return $this->baseURL.$pathParams.$queryParams;
    }

    function sendRequest($endpoint, $queries)
    {
        $curl = $this->curl;

        curl_setopt($curl, CURLOPT_URL, $this->urlBuilder($endpoint, $queries));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        if ($err)
            exit("HTTP Request Error:".$err);
        
        return json_decode($response, true);
    }

    function getUniverse($numofStocks)
    {
        $universe = $this->sendRequest(["symbol", "NASDAQ"], null);
        columnSort($universe, "marketCap");
        $universe = array_column($universe, "symbol");
        return array_slice($universe, 0, $numofStocks);
    }

    function getUniverseFinancials(&$universe, &$dataFields)
    {
        $universe = array_fill_keys($universe, null);

        foreach(array_keys($universe) as $security)
        {
        $rawFinancials = $this->sendRequest(["ratios-ttm", $security], null);
        $rawFinancials = $rawFinancials[0];
            foreach($dataFields as $dataField => $data)
            {
                $multiplier = $data["percentage"] ? 100 : 1;
                $universe[$security][$dataField] = round($rawFinancials[$data["apiField"]] * $multiplier, 2);
            }
        }
    }

    function saveFinancialData(&$financials)
    {
        file_put_contents("FMPDataCache.csv", $this->arrayToCSV($financials));
    }

    function loadFinancialData()
    {
        return $this->CSVToArray(file_get_contents("FMPDataCache.csv"));
    }

    //Tools for conversion between CSV and Associative Arrays.
    function arrayToCSV($arr)
    {
        $csv = array();
        $csv[0] = implode(",", array_keys($arr[array_key_first($arr)]));
        
        foreach(array_keys($arr) as $key)
        {
            array_unshift($arr[$key], $key);
            $csv[] = implode(",", $arr[$key]);
            
        }
        return implode("\n", $csv);
    }

    function CSVToArray($csv)
    {
        $csv = explode("\n", $csv);
        $arr = array();
        $headers = explode(",", array_shift($csv));

        foreach($csv as $row)
        {
            $row = explode(",", $row);
            $arr[$row[0]] = array_combine($headers, array_slice($row, 1));
        }
        return $arr;
    }

}

?>
<?php

function fetch_quote($symbol)
{
    $data = ["function" => "GLOBAL_QUOTE", "symbol" => $symbol, "datatype" => "json"];
    $endpoint = "https://hyprace-api.p.rapidapi.com/v1/grands-prix";
    $isRapidAPI = true;
    $rapidAPIHost = "hyprace-api.p.rapidapi.com";
    $result = get($endpoint, "F1_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
    if (isset($result["Global Quote"])) {
        $quote = $result["Global Quote"];
        $quote = array_reduce(
            array_keys($quote),
            function ($temp, $key) use ($quote) {
                $k = explode(" ", $key)[1];
                if ($k === "change") {
                    $k = "per_change";
                }
                $temp[$k] = str_replace('%', '', $quote[$key]);
                return $temp;
            }
        );
        $result = $quote;
    }
    return $result;
}
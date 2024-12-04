<?php

function fetch_race() {
    $data = []; // Prepare the data (if needed, populate this array)
    $endpoint = "https://hyprace-api.p.rapidapi.com/v1/grands-prix?isCurrent=true";
    $isRapidAPI = true;
    $rapidAPIHost = "hyprace-api.p.rapidapi.com";

    // Uncomment and ensure the `get` function works as expected
    $result = get($endpoint, "F1_API_KEY", $data, $isRapidAPI, $rapidAPIHost);

    // Debugging: Log the raw API result for inspection
    error_log("Raw API Result: " . var_export($result, true));

    // Check if the result is properly structured and status is 200
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        // Decode JSON response
        $decodedResult = json_decode($result["response"], true);

        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Decode Error: " . json_last_error_msg());
            $result = ["error" => "Invalid JSON response from API."];
        } else {
            $result = $decodedResult;
        }
    } else {
        // Handle cases where API call fails or structure is unexpected
        $result = ["error" => "API call failed or returned an unexpected response."];
        error_log("API Call Error: Status not 200 or 'response' missing.");
    }
}
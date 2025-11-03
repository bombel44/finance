<?php
// --- Yahoo Finance API Proxy ---

// 1. Set the content type header to JSON
// This tells the browser that the response is JSON.
header('Content-Type: application/json');

// 2. Get the target URL from the client's request
if (!isset($_GET['url'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'No URL provided.']);
    exit;
}

$target_url = $_GET['url'];

// 3. VERY IMPORTANT: Whitelist the allowed domains
// This is a critical security step to prevent your script
// from being used as an "open proxy" to attack other sites.
$allowed_domains = [
    'https://query1.finance.yahoo.com',
    'https://query2.finance.yahoo.com'
];

$is_allowed = false;
foreach ($allowed_domains as $domain) {
    // Check if the $target_url *starts with* one of the allowed domains
    if (strpos($target_url, $domain) === 0) {
        $is_allowed = true;
        break;
    }
}

if (!$is_allowed) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Access to this domain is not allowed.']);
    exit;
}

// 4. Initialize a cURL session to fetch the data
$ch = curl_init();

// 5. Set the cURL options
curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Return the transfer as a string
curl_setopt($ch, CURLOPT_TIMEOUT, 10);      // Timeout in 10 seconds

// Set a generic User-Agent. Some APIs block requests without one.
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');

// 6. Execute the cURL request
$response_body = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// 7. Check for cURL errors
if (curl_errno($ch)) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
    exit;
}

// 8. Close cURL session
curl_close($ch);

// 9. Pass through the HTTP status code from Yahoo
http_response_code($http_code);

// 10. Echo the response body from Yahoo back to the client
// The client's 'fetch' will receive this.
echo $response_body;

?>


<?php
// Simple PHP transparent proxy
// Configuration
$upstreamServer = 'https://generativelanguage.googleapis.com'; // Replace with your upstream server URL

// Disable PHP output buffering to ensure real-time streaming
while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(true);

// Ensure cURL is available
if (!extension_loaded('curl')) {
    http_response_code(500);
    die('cURL extension is required but not installed.');
}

// Get the original request details
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestHeaders = getallheaders();
$requestBody = file_get_contents('php://input');

// Build the full upstream URL
$upstreamUrl = $upstreamServer . $requestUri;

// Initialize cURL session
$ch = curl_init();

// Set basic cURL options
curl_setopt($ch, CURLOPT_URL, $upstreamUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, false); // Disable automatic header handling, we use CURLOPT_HEADERFUNCTION
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for development (remove in production)

// Prepare headers for forwarding
$forwardHeaders = [];
foreach ($requestHeaders as $name => $value) {
    // Skip certain headers that shouldn't be forwarded
    $lowerName = strtolower($name);
    if (in_array($lowerName, ['host', 'connection'])) {
        continue;
    }
    $forwardHeaders[] = "$name: $value";
}

// Add appropriate Content-Length header if body is present
if (!empty($requestBody)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    $forwardHeaders[] = "Content-Length: " . strlen($requestBody);
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $forwardHeaders);

// Variables to track response headers
$responseHeadersList = [];
$headersSent = false;

// Callback function to handle response headers (CURLOPT_HEADERFUNCTION)
curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($_, $header) use (&$responseHeadersList) {
    $trimmedHeader = trim($header);

    if (!empty($trimmedHeader) && !str_starts_with($trimmedHeader, 'HTTP/')) {
        // Regular header (e.g., Content-Type: text/html) - skip status line
        $responseHeadersList[] = $trimmedHeader;
    }

    return strlen($header);
});

// Callback function to handle response body (CURLOPT_WRITEFUNCTION)
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $bodyData) use (&$headersSent, &$responseHeadersList) {
    if (!$headersSent) {
        // First time we get body data, which means all headers have been received

        // Send status code to client
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        http_response_code($statusCode);

        // Send all response headers to client
        foreach ($responseHeadersList as $header) {
            header($header, false);
        }

        $headersSent = true;
    }

    // Send body data in real-time
    echo $bodyData;
    flush();

    return strlen($bodyData);
});


// Execute the request with real-time output
curl_exec($ch);

if (curl_errno($ch)) {
    // Handle proxy error if not already started sending response
    if (!$headersSent) {
        http_response_code(502);
        echo "Proxy Error: " . curl_error($ch);
    }
}

// Cleanup
$ch = null;

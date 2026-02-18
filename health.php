<?php
// Simple health check endpoint
// Returns 200 OK without loading WordPress
http_response_code(200);
header('Content-Type: text/plain');
echo 'OK';

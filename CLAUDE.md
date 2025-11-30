# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview
A simple, transparent PHP proxy with real-time streaming and a built-in test interface.

## Files
- `proxy.php`: Main proxy script that handles all request forwarding
- `index.html`: Built-in test interface (static HTML with Bulma CSS)
- `.htaccess`: Apache configuration for URL rewriting and proxy settings

## Architecture
1. **Request Flow**:
   - Apache receives requests and rewrites them to `proxy.php` via `.htaccess` rules
   - `proxy.php` extracts original request details (URI, method, headers, body)
   - It forwards the request to the configured upstream server using cURL
   - Response headers and body are streamed back to the client in real-time

2. **Key Features**:
   - Real-time streaming using cURL's `CURLOPT_WRITEFUNCTION` callback
   - API key integration via custom headers
   - Gemini API ready (pre-configured)

## Configuration
### Upstream Server
Edit `$upstreamServer` in `proxy.php`:
```php
$upstreamServer = 'https://generativelanguage.googleapis.com';
```


## Running
### Apache Server
1. Place all files in Apache web server directory
2. Ensure `mod_rewrite` is enabled and `AllowOverride All` is set
3. PHP 7.2+ with cURL extension required

### Test Interface
Access `index.html` in browser to test the proxy with a GUI.

### Direct Usage
```bash
curl -X POST "http://your-proxy-domain.com/v1beta/models/gemini-2.5-flash-lite:generateContent" \
  -H "Content-Type: application/json" \
  -H "x-goog-api-key: YOUR_API_KEY" \
  -d '{"contents": [{"parts": [{"text": "Hello, world!"}]}]}'
```

## Security Notes
- Enable `CURLOPT_SSL_VERIFYPEER` in production
- Always use HTTPS in production
- API keys are stored in memory in the test interface (not persisted)

## Apache Configuration
- `.htaccess` enables URL rewriting (all non-file/directory requests go to proxy.php)
- Sets longer timeout (300s) for streaming responses
- Configures PHP memory and execution time limits

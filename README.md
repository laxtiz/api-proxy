# PHP API Proxy

A simple, transparent PHP proxy with real-time streaming and a built-in test interface.

## Features

- **Transparent Proxying**: Forwards all HTTP requests to configured upstream server
- **Real-time Streaming**: Uses cURL callbacks to output responses as they are received
- **API Key Integration**: Supports passing API keys in custom headers
- **Test Interface**: Built-in HTML test page using Bulma CSS
- **JSON Formatting**: Automatically formats JSON responses for readability
- **Error Handling**: Shows status codes and detailed error messages
- **Gemini API Ready**: Pre-configured for Google's Gemini API with proper request format

## Files

- `proxy.php`: The main proxy script
- `index.html`: Built-in test interface
- `.htaccess`: Apache configuration file
- `README.md`: This documentation

## Configuration

### Upstream Server

Edit the `$upstreamServer` in `proxy.php` to set your target API:

```php
$upstreamServer = 'https://generativelanguage.googleapis.com';
```


## Usage

### Apache Server

Place all files in your Apache web server directory and make sure:

1. `mod_rewrite` is enabled
2. `AllowOverride All` is set for your directory
3. PHP is installed with cURL extension

### Test Interface

1. Access `index.html` in your browser
2. Enter your API key and header name (default: `x-goog-api-key` for Gemini API)
3. Set the API endpoint path (default: `/v1beta/models/gemini-2.5-flash-lite:generateContent`)
4. Enter the JSON request body
5. Click "Send Request" to test the proxy

### Direct Usage

Send requests directly to the proxy endpoint:

```bash
curl -X POST "http://your-proxy-domain.com/v1beta/models/gemini-2.5-flash-lite:generateContent" \
  -H "Content-Type: application/json" \
  -H "x-goog-api-key: YOUR_API_KEY" \
  -d '{"contents": [{"parts": [{"text": "Hello, world!"}]}]}'
```

## Security Notes

- **API Keys**: The built-in test interface stores API keys only in memory (not in localStorage or cookies)
- **SSL**: Always use HTTPS in production
- **SSL Verification**: Enable `CURLOPT_SSL_VERIFYPEER` in production: `curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);`
- **Input Validation**: Add additional input validation as needed

## Troubleshooting

### Network Errors

- Check if the upstream server is reachable
- Ensure your API key is valid
- Check Apache error logs

### Timeout Issues

- Increase the `Timeout` value in `.htaccess`
- Increase `max_execution_time` in `.htaccess` or php.ini
- Check if the upstream server has long response times

### API Key Issues

- Verify the API key header name matches what the API expects
- Ensure the API key is properly formatted
- Check if the API key has permissions for the requested endpoint

## Requirements

- PHP 7.2+ with cURL extension
- Apache web server with mod_rewrite enabled
- Modern browser for test interface

## License

MIT

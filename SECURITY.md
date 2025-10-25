# Security Policy

## Supported Versions

Currently supported versions of Laravel Translate:

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

We take security seriously. If you discover a security vulnerability, please follow these steps:

### 1. **Do Not** Open a Public Issue

Please do not open a public GitHub issue for security vulnerabilities.

### 2. Email Us Directly

Send details to: **subhashladumor1@gmail.com**

Include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### 3. Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days
- **Fix Release**: Depends on severity (critical issues within 72 hours)

## Security Considerations

### API Endpoints

This package uses third-party translation APIs. Be aware:

1. **Data Privacy**: Text sent to translation services may be logged by the service provider
2. **Sensitive Data**: Avoid translating sensitive/confidential information
3. **Rate Limiting**: Services may have rate limits

### Recommendations

1. **Use Caching**: Enable caching to minimize external API calls
2. **Sanitize Input**: Always validate and sanitize text before translation
3. **Environment Variables**: Never commit API keys to version control
4. **HTTPS Only**: Ensure all API communications use HTTPS
5. **Content Filtering**: Implement content filtering before translation

### Data Handling

The package:
- ✅ Does not store translations permanently (unless cached)
- ✅ Does not send translations to our servers
- ✅ Uses HTTPS for all API communications
- ✅ Supports offline translation via Argos

## Best Practices

### Production Usage

```php
// ✅ Good - Sanitize input
$cleanText = strip_tags($userInput);
$translation = Translate::translate($cleanText, 'es');

// ❌ Bad - Direct user input
$translation = Translate::translate($_POST['text'], 'es');
```

### Caching Sensitive Data

```php
// For sensitive data, disable caching
config(['translate.cache.enabled' => false]);
$translation = Translate::translate($sensitiveData, 'es');
config(['translate.cache.enabled' => true]);
```

### Rate Limiting

Implement rate limiting to prevent abuse:

```php
use Illuminate\Support\Facades\RateLimiter;

if (RateLimiter::tooManyAttempts('translate:'.$request->ip(), 60)) {
    abort(429, 'Too many translation requests');
}

RateLimiter::hit('translate:'.$request->ip());
```

## Acknowledgments

We appreciate responsible disclosure and will acknowledge security researchers who report vulnerabilities.

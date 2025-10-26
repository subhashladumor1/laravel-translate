# LibreTranslate API Key Requirement

## ⚠️ Important Change

**LibreTranslate public instance now requires an API key** as of their recent update. The free public endpoint is no longer available without authentication.

## Error Message

```
LibreTranslate API error (HTTP 400): {"error":"Visit https://portal.libretranslate.com to get an API key"}
```

## Solutions (Choose One)

### Solution 1: Get Free API Key (Recommended) ✅

LibreTranslate offers a **FREE tier** with generous limits.

#### Step 1: Get API Key
1. Visit https://portal.libretranslate.com
2. Sign up for a free account
3. Get your API key from the dashboard
4. **Free tier includes:**
   - ✅ 100,000 characters per month
   - ✅ No credit card required
   - ✅ All languages supported

#### Step 2: Configure in Laravel

**Add to `.env`:**
```env
TRANSLATE_LIBRE_ENABLED=true
TRANSLATE_LIBRE_API_KEY=your-api-key-here
```

**Or in `config/translate.php`:**
```php
'services' => [
    'libre' => [
        'enabled' => true,
        'api_key' => env('TRANSLATE_LIBRE_API_KEY', 'your-api-key'),
    ],
],
```

#### Step 3: Test
```bash
php artisan translate:test --service=libre
```

---

### Solution 2: Use Alternative Free Services (No API Key) ✅

The package now defaults to **Lingva** which doesn't require an API key.

#### Services That Don't Require API Key:

| Service | API Key | Speed | Quality | Limits |
|---------|---------|-------|---------|--------|
| **Lingva** ⭐ | ❌ No | ⚡⚡⚡⚡ | ⭐⭐⭐⭐ | Generous |
| **Google** | ❌ No | ⚡⚡⚡⚡ | ⭐⭐⭐⭐⭐ | Good |
| **MyMemory** | ❌ No | ⚡⚡ | ⭐⭐⭐ | 1000/day |

#### Configuration (Already Set by Default)

**`.env`:**
```env
# Default service (no API key required)
TRANSLATE_DEFAULT_SERVICE=lingva

# Disable LibreTranslate if you don't have API key
TRANSLATE_LIBRE_ENABLED=false
```

**Fallback chain (LibreTranslate last):**
```php
'fallback_chain' => ['lingva', 'google', 'mymemory', 'libre'],
```

---

### Solution 3: Self-Host LibreTranslate (Advanced) 🚀

Run your own LibreTranslate instance (no API key needed, unlimited usage).

#### Using Docker:

```bash
# Run LibreTranslate locally
docker run -ti --rm -p 5000:5000 libretranslate/libretranslate
```

#### Configure Laravel:

**`.env`:**
```env
TRANSLATE_LIBRE_ENABLED=true
TRANSLATE_LIBRE_ENDPOINT=http://localhost:5000
TRANSLATE_LIBRE_API_KEY=  # Leave empty for self-hosted
```

**Benefits:**
- ✅ No API key required
- ✅ Unlimited usage
- ✅ Complete privacy
- ✅ No rate limits
- ✅ Works offline

---

## What Changed in This Package

### 1. **Default Service Changed**
```php
// OLD
'default_service' => 'libre',

// NEW
'default_service' => 'lingva',  // No API key required
```

### 2. **LibreTranslate Disabled by Default**
```php
// OLD
'libre' => [
    'enabled' => true,
],

// NEW
'libre' => [
    'enabled' => false,  // Requires API key
    'api_key' => env('TRANSLATE_LIBRE_API_KEY'),
],
```

### 3. **Improved Error Messages**
```php
// Now shows helpful message:
"LibreTranslate API key required. 
Get one at https://portal.libretranslate.com (free tier available) or 
use a self-hosted instance. Set TRANSLATE_LIBRE_API_KEY in .env"
```

### 4. **Updated Fallback Chain**
```php
// OLD
'fallback_chain' => ['libre', 'lingva', 'mymemory', 'google'],

// NEW (libre last since it requires API key)
'fallback_chain' => ['lingva', 'google', 'mymemory', 'libre'],
```

---

## Quick Start Guide

### Option A: Use Without LibreTranslate (Easiest)

```bash
# 1. Clear caches
php artisan cache:clear
php artisan config:clear

# 2. Test services (should work immediately)
php artisan translate:test

# 3. Use translations
php artisan translate:string "Hello World" --target=es
```

**Expected result:** Uses Lingva, Google, or MyMemory (all work without API key)

---

### Option B: Add LibreTranslate with API Key

```bash
# 1. Get free API key from https://portal.libretranslate.com

# 2. Add to .env
echo "TRANSLATE_LIBRE_API_KEY=your-api-key-here" >> .env
echo "TRANSLATE_LIBRE_ENABLED=true" >> .env

# 3. Clear caches
php artisan cache:clear
php artisan config:clear

# 4. Test LibreTranslate
php artisan translate:test --service=libre
```

---

## Testing Services

### Test All Services
```bash
php artisan translate:test
```

**Expected Output (without LibreTranslate API key):**
```
Testing libre...
  ⚠ Service is disabled (enable in config)

Testing lingva...
  ✓ Success (850ms)
    Translation: "Hola Mundo"

Testing mymemory...
  ✓ Success (1100ms)
    Translation: "Hola Mundo"

Testing google...
  ✓ Success (950ms)
    Translation: "Hola Mundo"

═══════════════════════════════════
Test Summary
═══════════════════════════════════
✓ Successful: 3
✗ Failed: 0
```

### Test Individual Service
```bash
# Test Lingva (default, no API key)
php artisan translate:test --service=lingva

# Test Google (no API key)
php artisan translate:test --service=google

# Test LibreTranslate (requires API key)
php artisan translate:test --service=libre
```

---

## Configuration Examples

### Basic (No API Keys)

**`.env`:**
```env
TRANSLATE_DEFAULT_SERVICE=lingva
TRANSLATE_LINGVA_ENABLED=true
TRANSLATE_GOOGLE_ENABLED=true
TRANSLATE_MYMEMORY_ENABLED=true
TRANSLATE_LIBRE_ENABLED=false
```

### With LibreTranslate API Key

**`.env`:**
```env
TRANSLATE_DEFAULT_SERVICE=lingva
TRANSLATE_LIBRE_ENABLED=true
TRANSLATE_LIBRE_API_KEY=sk-xxxxxxxxxxxxxxxxxxxxx
```

### With Self-Hosted LibreTranslate

**`.env`:**
```env
TRANSLATE_DEFAULT_SERVICE=libre
TRANSLATE_LIBRE_ENABLED=true
TRANSLATE_LIBRE_ENDPOINT=http://localhost:5000
# No API key needed for self-hosted
```

### Production (Multiple Services)

**`.env`:**
```env
# Primary: Fast and free
TRANSLATE_DEFAULT_SERVICE=lingva

# Fallback chain
TRANSLATE_LINGVA_ENABLED=true
TRANSLATE_GOOGLE_ENABLED=true
TRANSLATE_MYMEMORY_ENABLED=true

# Optional: With API key for higher limits
TRANSLATE_LIBRE_ENABLED=true
TRANSLATE_LIBRE_API_KEY=sk-xxxxxxxxxxxxxxxxxxxxx
```

---

## Troubleshooting

### Issue: All translations failing

**Check:**
```bash
# Test each service
php artisan translate:test

# Check which are enabled
php artisan tinker
>>> config('translate.services')
```

**Solution:**
```bash
# Make sure at least one service is enabled
php artisan config:clear
```

### Issue: "LibreTranslate API key required"

**Solutions:**

1. **Get free API key:**
   - Visit https://portal.libretranslate.com
   - Add to `.env`: `TRANSLATE_LIBRE_API_KEY=your-key`

2. **Disable LibreTranslate:**
   ```env
   TRANSLATE_LIBRE_ENABLED=false
   ```

3. **Use self-hosted instance:**
   ```bash
   docker run -p 5000:5000 libretranslate/libretranslate
   ```
   ```env
   TRANSLATE_LIBRE_ENDPOINT=http://localhost:5000
   ```

### Issue: Slow translations

**Optimize:**
```env
# Use fastest services only
TRANSLATE_DEFAULT_SERVICE=lingva
```

```php
'fallback_chain' => ['lingva', 'google'],  // Remove slower services
```

---

## Migration Guide

### From Old Configuration

**Before (libre was default):**
```php
'default_service' => 'libre',
'fallback_chain' => ['libre', 'lingva', 'mymemory', 'google'],
```

**After (lingva is default):**
```php
'default_service' => 'lingva',
'fallback_chain' => ['lingva', 'google', 'mymemory', 'libre'],
```

### Update Your Code

**No code changes needed!** The package handles fallback automatically.

```php
// This still works the same
$translation = translateText('Hello World', 'es');

// Package will use:
// 1. lingva (if enabled)
// 2. google (if lingva fails)
// 3. mymemory (if google fails)
// 4. libre (if mymemory fails AND you have API key)
```

---

## FAQs

### Q: Do I need to pay for LibreTranslate?
**A:** No! Free tier includes 100,000 characters/month. Only pay if you need more.

### Q: Which service is fastest?
**A:** Lingva and Google are fastest (~800-1000ms). Both don't require API keys.

### Q: Can I use multiple services together?
**A:** Yes! Set fallback chain. If one fails, it tries the next automatically.

### Q: Is my data private?
**A:** Third-party APIs may log requests. For privacy, self-host LibreTranslate.

### Q: What if I hit rate limits?
**A:** Enable caching (recommended) or use multiple services in fallback chain.

---

## Performance Comparison

### With API Keys (All Services)

| Service | Speed | Free Tier | Requires API Key |
|---------|-------|-----------|------------------|
| Lingva | ⚡⚡⚡⚡ | Unlimited | ❌ No |
| Google | ⚡⚡⚡⚡ | Generous | ❌ No |
| LibreTranslate | ⚡⚡⚡ | 100K chars/mo | ✅ Yes |
| MyMemory | ⚡⚡ | 1000/day | ❌ No |

### Without API Keys (Default Config)

| Service | Speed | Status |
|---------|-------|--------|
| Lingva | ⚡⚡⚡⚡ | ✅ Working |
| Google | ⚡⚡⚡⚡ | ✅ Working |
| MyMemory | ⚡⚡ | ✅ Working |
| LibreTranslate | - | ⚠️ Disabled |

---

## Recommended Setup

### For Development (Simplest)
```env
TRANSLATE_DEFAULT_SERVICE=lingva
TRANSLATE_CACHE_ENABLED=true
```

### For Production (Best Reliability)
```env
TRANSLATE_DEFAULT_SERVICE=lingva
TRANSLATE_CACHE_ENABLED=true
TRANSLATE_CACHE_DRIVER=redis

# Enable multiple fallbacks
TRANSLATE_LINGVA_ENABLED=true
TRANSLATE_GOOGLE_ENABLED=true
TRANSLATE_MYMEMORY_ENABLED=true

# Optional: Add LibreTranslate with API key
TRANSLATE_LIBRE_ENABLED=true
TRANSLATE_LIBRE_API_KEY=sk-xxxxx
```

### For High Volume (Self-Hosted)
```bash
# Run LibreTranslate locally
docker run -d -p 5000:5000 libretranslate/libretranslate
```

```env
TRANSLATE_DEFAULT_SERVICE=libre
TRANSLATE_LIBRE_ENDPOINT=http://localhost:5000
TRANSLATE_LIBRE_ENABLED=true
# No API key needed
```

---

## Summary

✅ **No action required** - Package now uses Lingva by default (no API key)

✅ **Optional** - Get free LibreTranslate API key for additional service

✅ **Advanced** - Self-host LibreTranslate for unlimited private usage

**Quick test:**
```bash
php artisan translate:test
```

Should show 3 working services (Lingva, Google, MyMemory) without any API keys! 🎉

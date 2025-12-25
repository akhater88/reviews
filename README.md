# Google Business Integration - Installation Guide

## Overview

This feature allows restaurant owners to:
1. **Connect Google Business Account** - OAuth2 integration for automatic branch import
2. **Import branches from Google** - Pull locations automatically with review reply capability
3. **Add manual branches** - For branches not on Google or competitor tracking
4. **Track competitors** - Link competitor branches for comparison reports

## File Structure

```
├── app/
│   ├── Enums/
│   │   ├── BranchSource.php       # google_business | manual
│   │   ├── BranchType.php         # owned | competitor
│   │   └── SyncStatus.php         # pending | syncing | completed | failed
│   │
│   ├── Filament/
│   │   └── Pages/
│   │       └── GoogleSettings.php  # Main settings page
│   │
│   ├── Http/
│   │   └── Controllers/
│   │       └── Auth/
│   │           └── GoogleOAuthController.php
│   │
│   ├── Models/
│   │   ├── Branch.php             # Updated with new fields
│   │   └── GoogleToken.php        # OAuth token storage
│   │
│   └── Services/
│       └── Google/
│           ├── GoogleBusinessService.php  # Google API integration
│           └── OutscraperService.php      # Outscraper fallback
│
├── config/
│   └── services-google.php        # Configuration file
│
├── database/
│   └── migrations/
│       ├── 2025_12_25_001_add_source_and_competitor_to_branches_table.php
│       └── 2025_12_25_002_create_google_tokens_table.php
│
├── resources/
│   └── views/
│       └── filament/
│           └── pages/
│               └── google-settings.blade.php
│
└── routes/
    └── google-oauth.php
```

## Installation Steps

### 1. Copy Files

Copy all files to your TABsense project maintaining the directory structure.

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Add Routes

Add to your `routes/web.php`:

```php
require __DIR__.'/google-oauth.php';
```

### 4. Merge Config

Add to your `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    'places_api_key' => env('GOOGLE_PLACES_API_KEY'),
],

'outscraper' => [
    'api_key' => env('OUTSCRAPER_API_KEY'),
],
```

### 5. Update Environment

Add to your `.env`:

```env
# Google Business API
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
GOOGLE_PLACES_API_KEY=your-places-api-key

# Outscraper
OUTSCRAPER_API_KEY=your-outscraper-api-key
```

## Google Cloud Console Setup

### 1. Create Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable the following APIs:
   - Google My Business API
   - My Business Business Information API
   - Google Places API

### 2. Configure OAuth Consent Screen

1. Go to **APIs & Services > OAuth consent screen**
2. Choose **External** user type
3. Fill in required fields:
   - App name: TABsense
   - User support email: your email
   - Developer contact: your email
4. Add scopes:
   - `https://www.googleapis.com/auth/business.manage`
   - `https://www.googleapis.com/auth/userinfo.email`
   - `https://www.googleapis.com/auth/userinfo.profile`

### 3. Create OAuth Credentials

1. Go to **APIs & Services > Credentials**
2. Click **Create Credentials > OAuth client ID**
3. Choose **Web application**
4. Add Authorized redirect URIs:
   - `https://yourdomain.com/auth/google/callback`
   - `http://localhost:8000/auth/google/callback` (for development)
5. Copy **Client ID** and **Client Secret**

### 4. Create Places API Key

1. Go to **APIs & Services > Credentials**
2. Click **Create Credentials > API key**
3. Restrict the key to Places API only

## Outscraper Setup

1. Sign up at [Outscraper](https://outscraper.com/)
2. Get your API key from the dashboard
3. Add credits to your account (pay-per-use)

## Usage

### For Restaurant Owners

1. Navigate to **Settings > Google Settings**
2. Click **Connect Google Business Account**
3. Authorize the permissions
4. Import branches from the list
5. Or add branches manually for competitor tracking

### Branch Types

| Type | Source | Can Reply | Use Case |
|------|--------|-----------|----------|
| Owned + Google Business | Google OAuth | ✅ Yes | Your branches connected to Google |
| Owned + Manual | Outscraper | ❌ No | Your branches not on Google |
| Competitor + Manual | Outscraper | ❌ No | Track competitor performance |

### Syncing Reviews

- **Google Business branches**: Sync via official API (instant, reliable)
- **Manual branches**: Sync via Outscraper (may take longer, costs credits)

## Troubleshooting

### OAuth Errors

- **Invalid redirect URI**: Make sure the URI in Google Console matches exactly
- **Access denied**: User cancelled the authorization
- **Token expired**: Refresh token will be used automatically

### API Limits

- Google Business API: 10,000 requests/day
- Outscraper: Pay-per-use, check your balance

### Common Issues

1. **Branches not showing**: Click "Refresh Locations" button
2. **Can't reply to reviews**: Only Google Business branches support replies
3. **Sync failed**: Check API keys and network connectivity

## Next Steps

After installation, you should:

1. Create the **SyncBranchReviewsJob** for background syncing
2. Implement the **Branch Report Page** for analysis display
3. Add **scheduled sync** using Laravel Scheduler
4. Build the **AI analysis pipeline** integration

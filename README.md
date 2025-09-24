# Sendy API Utility

A simple utility for testing the Sendy API connection and endpoints. A prototype tool that serves as a foundation for creating a potential analytical dashboard plugin for Wordpress.

[https://sendy.co/api](https://sendy.co/api)

![Screenshot of Sendy API Utility](screenshot.png)

## API Endpoints

### Subscribers
- `/api/subscribers/active-subscriber-count.php` - Get total subscriber count for list
- `/api/subscribers/subscription-status.php` - Check subscription status of specific email

### Campaigns
- `/api/campaigns/summary.php` - Returns `"sent,opens,clicks,unsubscribes"`
- `/api/campaigns/opens.php` - Campaign open tracking data
- `/api/campaigns/clicks.php` - Link click tracking data

### Lists & Brands  
- `/api/lists/get-lists.php` - Get all lists for brand
- `/api/brands/get-brands.php` - Get all brands

### Reporting
- `/api/reporting/query.php` - Enhanced campaign analytics

## How to Run

```bash
php -S localhost:8080
```

Open `http://localhost:8080` in your browser.
# Sendy API Utility

A simple utility for testing the Sendy API connection and endpoints. A prototype tool that serves as a foundation for creating a potential analytical dashboard plugin for Wordpress.

[https://sendy.co/api](https://sendy.co/api)

![Screenshot of Sendy API Utility](screenshot.png)

### API Endpoints

- `/api/subscribers/active-subscriber-count.php`
- `/api/campaigns/get-campaigns.php`
- `/api/campaigns/summary.php`
- `/api/subscribers/subscription-status.php`

```
// Campaign performance summary
/api/campaigns/summary.php
// Returns: "1250,45,12,8" (sent,opens,clicks,unsubscribes)

// Detailed click tracking  
/api/campaigns/clicks.php
// Returns detailed click data per link

// Campaign opens
/api/campaigns/opens.php  
// Returns who opened when
```

### How to run:

`php -S localhost:8080`
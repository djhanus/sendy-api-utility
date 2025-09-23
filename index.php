<!DOCTYPE html>
<html>
<head>
    <title>Sendy API Utility</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="header"> 
        <img src="https://dlgo7qh09pizs.cloudfront.net/images/sendy-logo.png" alt="Sendy Logo" style="height:32px;vertical-align:middle;margin-right:10px;">

        <h1>
            API Utility
        </h1>
    </div>

    <h2>API Connection Test</h2>
    <form method="POST">
        <div class="form-group">
            <label>Application URL (Base URL):</label>
            <input type="url" name="api_url" value="<?= $_POST['api_url'] ?? 'http://your-sendy-url.com' ?>" placeholder="http://your-sendy-url.com">
        </div>
        
        <div class="form-group">
            <label>API Key:</label>
            <input type="text" name="api_key" value="<?= $_POST['api_key'] ?? '' ?>" placeholder="XXXXXXXXXXXXXXXXXX">
        </div>

        <div>
            <button type="submit" name="action" value="test_connection" class="connection-test">Test Connection</button>
        </div>

        <div class="form-group">
            <label>List ID:</label>
            <input type="text" name="list_id" value="<?= $_POST['list_id'] ?? '' ?>" placeholder="XXXXXXXXXXXXXXXXXXX">
        </div>

        <button type="submit" name="action" value="test_connection">Get Number of Subscribers on List</button>

        
        <div class="form-group">
            <label>Campaign ID:</label>
            <input type="text" name="campaign_id" value="<?= $_POST['campaign_id'] ?? '' ?>" placeholder="XXXX">
        </div>

        <button type="submit" name="action" value="get_campaigns">Get Campaigns##</button>
        <button type="submit" name="action" value="campaign_summary">Campaign Summary</button>
        <button type="submit" name="action" value="campaign_opens">Campaign Opens</button>
        <button type="submit" name="action" value="campaign_clicks">Campaign Clicks##</button>
        <button type="submit" name="action" value="enhanced_query">Enhanced Query (Detailed Analytics)</button>

        <div class="form-group">
            <label>Brand ID (for lists):</label>
            <input type="text" name="brand_id" value="<?= $_POST['brand_id'] ?? '' ?>" placeholder="Brand ID">
        </div>

        <button type="submit" name="action" value="get_brands">Get All Brands</button>
        <button type="submit" name="action" value="get_lists">Get All Lists in Brand ID</button>

        <div class="form-group">
            <label>Test Email Address:</label>
            <input type="text" name="test_email" value="<?= $_POST['test_email'] ?? '' ?>" placeholder="user@address.com">
        </div>

            <button type="submit" name="action" value="subscriber_status">Check Subscriber Status</button>


        <hr>

  

    </form>

    
    <?php
    if ($_POST && !empty($_POST['api_url']) && !empty($_POST['api_key'])) {
        $api_url = rtrim($_POST['api_url'], '/');
        $api_key = $_POST['api_key'];
        $list_id = $_POST['list_id'];
        $campaign_id = $_POST['campaign_id'];
        $action = $_POST['action'];
        
        echo "<div class='result'>";
        echo "<h3>Results for: " . ucfirst(str_replace('_', ' ', $action)) . "</h3>";
        
        switch($action) {
            case 'test_connection':
                $result = sendy_request($api_url . '/api/subscribers/active-subscriber-count.php', [
                    'api_key' => $api_key,
                    'list_id' => $list_id ?: 'test' // provide a dummy list_id
                ]);
                break;
                
            case 'get_campaigns':
                $result = sendy_request($api_url . '/api/campaigns/get-campaigns.php', [
                    'api_key' => $api_key
                ]);
                break;

            case 'subscriber_status':
                if(!$list_id || !$_POST['test_email']) {
                    echo "<p class='error'>Both List ID and email required</p>";
                    break;
                }
                $result = sendy_request($api_url . '/api/subscribers/subscription-status.php', [
                    'api_key' => $api_key,
                    'email' => $_POST['test_email'],
                    'list_id' => $list_id
                ]);
                break;
                
            case 'campaign_summary':
                if(!$campaign_id) {
                    echo "<p class='error'>Campaign ID required for this test</p>";
                    break;
                }
                $result = sendy_request($api_url . '/api/campaigns/summary.php', [
                    'api_key' => $api_key,
                    'campaign_id' => $campaign_id
                ]);
                echo "Response Array = (SENT,OPENS,CLICKS,UNSUBSCRIBES)";
                break;
                
            case 'campaign_opens':
                if(!$campaign_id) {
                    echo "<p class='error'>Campaign ID required for this test</p>";
                    break;
                }
                $result = sendy_request($api_url . '/api/campaigns/opens.php', [
                    'api_key' => $api_key,
                    'campaign_id' => $campaign_id
                ]);
                break;
                
            case 'campaign_clicks':
                if(!$campaign_id) {
                    echo "<p class='error'>Campaign ID required for this test</p>";
                    break;
                }
                echo "<p><strong>Debug:</strong> Testing campaign clicks for campaign ID: " . htmlspecialchars($campaign_id) . "</p>";
                
                // First try the clicks endpoint
                $result = sendy_request($api_url . '/api/campaigns/clicks.php', [
                    'api_key' => $api_key,
                    'campaign_id' => $campaign_id
                ]);
                
                // If that fails, try alternative endpoints
                if($result['http_code'] == 500 || $result['http_code'] == 404) {
                    echo "<p class='warning'>Standard clicks endpoint failed. Trying alternative...</p>";
                    
                    // Try without .php extension
                    $result2 = sendy_request($api_url . '/api/campaigns/clicks', [
                        'api_key' => $api_key,
                        'campaign_id' => $campaign_id
                    ]);
                    
                    if($result2['http_code'] != 500 && $result2['http_code'] != 404) {
                        $result = $result2;
                        echo "<p class='success'>Alternative endpoint worked!</p>";
                    } else {
                        echo "<p class='info'>Note: Campaign summary shows 40 clicks, but detailed click data endpoint may not be available in your Sendy version.</p>";
                        echo "<p class='info'>Some Sendy installations don't have the clicks detail endpoint - only the summary counts.</p>";
                    }
                }
                break;

            case 'enhanced_query':
                if(!$campaign_id) {
                    echo "<p class='error'>Campaign ID required for enhanced query</p>";
                    break;
                }
                echo "<p><strong>Enhanced Query:</strong> Getting detailed analytics for campaign ID: " . htmlspecialchars($campaign_id) . "</p>";
                echo "<p class='info'>This endpoint provides comprehensive data including link clicks, subscriber details, and more.</p>";
                
                $result = sendy_request($api_url . '/api/reporting/query.php', [
                    'api_key' => $api_key,
                    'campaign_id' => $campaign_id
                ]);
                
                // Enhanced formatting for JSON responses
                if($result['http_code'] == 200 && !empty($result['response'])) {
                    echo "<p class='success'>Enhanced query successful!</p>";
                    
                    // Try to decode and format JSON nicely
                    $json_data = json_decode($result['response'], true);
                    if(json_last_error() === JSON_ERROR_NONE && isset($json_data['campaigns'])) {
                        echo "<h4>📊 Enhanced Campaign Analytics:</h4>";
                        foreach($json_data['campaigns'] as $campaign) {
                            echo "<div class='campaign-details'>";
                            echo "<strong>Campaign ID:</strong> " . $campaign['id'] . "<br>";
                            echo "<strong>Brand ID:</strong> " . $campaign['brand_id'] . "<br>";
                            echo "<strong>Date Sent:</strong> " . $campaign['date_sent'] . "<br>";
                            echo "<strong>Total Sent:</strong> " . number_format($campaign['total_sent']) . "<br>";
                            echo "<strong>Unique Opens:</strong> " . number_format($campaign['unique_opens']) . " (" . number_format($campaign['open_percentage'], 2) . "%)<br>";
                            echo "<strong>Total Opens:</strong> " . number_format($campaign['total_opens']) . " (Rate: " . number_format($campaign['open_rate'], 2) . "%)<br>";
                            echo "<strong>Total Clicks:</strong> " . number_format($campaign['total_clicks']) . "<br>";
                            
                            if(isset($campaign['links']) && is_array($campaign['links'])) {
                                echo "<strong>🔗 Link Click Details:</strong><br>";
                                echo "<ul>";
                                foreach($campaign['links'] as $link) {
                                    echo "<li><strong>" . number_format($link['clicks']) . " clicks:</strong> <a href='" . htmlspecialchars($link['url']) . "' target='_blank'>" . htmlspecialchars($link['url']) . "</a></li>";
                                }
                                echo "</ul>";
                            }
                            echo "</div>";
                        }
                    }
                }
                break;

            case 'get_lists':
                if(!isset($_POST['brand_id']) || empty($_POST['brand_id'])) {
                    echo "<p class='error'>Brand ID required</p>";
                    break;
                }
                $result = sendy_request($api_url . '/api/lists/get-lists.php', [
                    'api_key' => $api_key,
                    'brand_id' => $_POST['brand_id']
                ]);
                break;

            case 'get_brands':
                $result = sendy_request($api_url . '/api/brands/get-brands.php', [
                    'api_key' => $api_key
                ]);
                break;

            case 'subscriber_status':
                if(!$list_id || !isset($_POST['test_email'])) {
                    echo "<p class='error'>Both List ID and email required</p>";
                    break;
                }
                $result = sendy_request($api_url . '/api/subscribers/subscription-status.php', [
                    'api_key' => $api_key,
                    'email' => $_POST['test_email'],
                    'list_id' => $list_id
                ]);
                break;
        }
        
        if(isset($result)) {
            echo "<pre>" . htmlspecialchars(print_r($result, true)) . "</pre>";
        }
        
        echo "</div>";
    }
    



    function sendy_request($url, $data) {
        echo "<p><strong>Trying URL:</strong> " . $url . "</p>"; // DEBUG LINE
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'http_code' => $http_code,
            'response' => $response,
            'error' => $error ?: null,
            'parsed' => parse_sendy_response($response)
        ];
    }

    function parse_sendy_response($response) {
        // Try JSON first (for lists/brands)
        $json = json_decode($response, true);
        if(json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }
        
        // Handle numeric responses
        if(is_numeric($response)) {
            return (int)$response;
        }
        
        // Handle error messages
        $errors = ['No data passed', 'Invalid API key', 'List does not exist', 'Campaign does not exist'];
        if(in_array(trim($response), $errors)) {
            return ['error' => trim($response)];
        }
        
        return trim($response);
    }
    ?>
</body>
</html>
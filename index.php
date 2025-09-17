<!DOCTYPE html>
<html>
<head>
    <title>Sendy API Sandbox</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>
        <img src="https://dlgo7qh09pizs.cloudfront.net/images/sendy-logo.png" alt="Sendy Logo" style="height:32px;vertical-align:middle;margin-right:10px;"><br><br>
        API Endpoint Connection Tester
    </h1>
    
    <form method="POST">
        <div class="form-group">
            <label>Sendy Application URL:</label>
            <input type="url" name="api_url" value="<?= $_POST['api_url'] ?? 'http://your-sendy-url' ?>" placeholder="http://your-sendy-url">
        </div>
        
        <div class="form-group">
            <label>API Key:</label>
            <input type="text" name="api_key" value="<?= $_POST['api_key'] ?? '' ?>" placeholder="XXXXXXXXXXXXXXXXXX">
        </div>
        
        <div class="form-group">
            <label>List ID (optional for some tests):</label>
            <input type="text" name="list_id" value="<?= $_POST['list_id'] ?? '' ?>" placeholder="List ID">
        </div>
        
        <div class="form-group">
            <label>Campaign ID (for campaign-specific tests):</label>
            <input type="text" name="campaign_id" value="<?= $_POST['campaign_id'] ?? '' ?>" placeholder="Campaign ID">
        </div>
        <div class="form-group">
            <label>Test Email Address:</label>
            <input type="email" name="test_email" value="<?= $_POST['test_email'] ?? '' ?>" placeholder="test@example.com">
        </div>
        
        <button type="submit" name="action" value="test_connection">Test Connection</button>
        
        <button type="submit" name="action" value="get_campaigns">Get Campaigns</button>
        <button type="submit" name="action" value="campaign_summary">Campaign Summary</button>
        <button type="submit" name="action" value="campaign_clicks">Campaign Clicks</button>

        <button type="submit" name="action" value="get_lists">Get All Lists</button>
        <button type="submit" name="action" value="get_brands">Get All Brands</button>

        <button type="submit" name="action" value="subscriber_status">Check Subscriber Status</button>

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
                break;
                
            case 'campaign_clicks':
                if(!$campaign_id) {
                    echo "<p class='error'>Campaign ID required for this test</p>";
                    break;
                }
                $result = sendy_request($api_url . '/api/campaigns/clicks.php', [
                    'api_key' => $api_key,
                    'campaign_id' => $campaign_id
                ]);
                break;

            case 'get_lists':
                if(!$list_id) {
                    echo "<p class='error'>Brand ID required - use List ID field for now</p>";
                    break;
                }
                $result = sendy_request($api_url . '/api/lists/get-lists.php', [
                    'api_key' => $api_key,
                    'brand_id' => $list_id // using list_id field as brand_id for testing
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
        // Sendy often returns plain text responses
        if(is_numeric($response)) {
            return (int)$response;
        }
        
        // Try to detect if it's JSON
        $json = json_decode($response, true);
        if(json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }
        
        // Check for common Sendy error messages
        $errors = ['No data passed', 'Invalid API key', 'List does not exist', 'Campaign does not exist'];
        if(in_array(trim($response), $errors)) {
            return ['error' => trim($response)];
        }
        
        return trim($response);
    }
    ?>
</body>
</html>
<?php
require __DIR__ . '/../../vendor/autoload.php';

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Calendar API PHP Quickstart');
    $client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
    $client->setAuthConfig(__DIR__ .'/../../credentials/credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.

    $tokenPath = __DIR__ . '/../../credentials/token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }

    return $client;
}

$client = getClient();

if (isset($_REQUEST['date']) && $_REQUEST['date']) {
    // Get the API client and construct the service object.
    $service = new Google_Service_Calendar($client);

    // Look for the events on a specific day.
    $dayInSeconds = 24 * 60 * 60;
    $calendarId = 'primary';
    $optParams = array(
        'maxResults' => 10,
        'orderBy' => 'startTime',
        'singleEvents' => true,
        'timeMin' => date('c', strtotime($_REQUEST['date'])),
        'timeMax' => date('c', strtotime($_REQUEST['date']) + $dayInSeconds),
    );
    $results = $service->events->listEvents($calendarId, $optParams);
    $events = $results->getItems();


    $output['status']['code'] = "200";
    $output['status']['name'] = "ok";
    $output['status']['description'] = "success";
    $output['data'] = $events;
}else{
    // In case the request doesn't have date information.
    $output['status']['code'] = "404";
    $output['status']['name'] = "failed";
    $output['status']['description'] = "missing date paramater";
}

// Returning the response as a JSON Object.
echo json_encode($output);
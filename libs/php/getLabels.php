<?php
require  __DIR__ . '/authentication.php';

$client = $_SERVER['google_client'];

// Get the API client and construct the service object.
$service = new Google_Service_Gmail($client);


// Print the labels in the user's account.
$user = 'me';
$results = $service->users_labels->listUsersLabels($user);

$output['status']['code'] = "200";
$output['status']['name'] = "ok";
$output['status']['description'] = "success";
$output['data'] = $results->getLabels();

echo json_encode($output);

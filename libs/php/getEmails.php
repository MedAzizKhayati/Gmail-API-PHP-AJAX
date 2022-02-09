<?php
require  __DIR__ . '/authentication.php';

$client = $_SERVER['google_client'];


if (isset($_REQUEST['label']) && $_REQUEST['label'] && isset($_REQUEST['date']) && $_REQUEST['date']) {

    // Get the API client and construct the service object.
    $service = new Google_Service_Gmail($client);

    // Print the names and IDs for up to 10 files.
    $user = 'me';
    $dayInSeconds = 24 * 3600;
    $optParams = array(
      'q' => 'after:'.(new DateTime($_REQUEST['date']))->format('Y/m/d'). 'before:'. (new DateTime($_REQUEST['date']))->add(DateInterval::createFromDateString('1 day'))->format('Y/m/d'),
      'labelIds' => [$_REQUEST['label']]
    );
    $results = $service->users_messages->listUsersMessages($user, $optParams);
    $emails = [];
    $optParams = array(
      'format' => 'full',
    );
    $i = 0;
    foreach ($results as $key => $message) {
      $id = $message->getId();
      $emails[$i++] = $service->users_messages->get($user, $id, $optParams);
    }

    $output['status']['code'] = "200";
    $output['status']['name'] = "ok";
    $output['status']['description'] = "success";
    $output['data'] = $emails; //$results->getMessages();
} else {
    // In case the request doesn't have date information.
    $output['status']['code'] = "404";
    $output['status']['name'] = "failed";
    $output['status']['description'] = "missing parent paramater";
}

// Returning the response as a JSON Object.
echo json_encode($output);


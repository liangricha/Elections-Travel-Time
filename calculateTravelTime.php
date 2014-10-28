<?php


$args = getopt('f:')
if ($args == FALSE || !isset(args['f']) || args['f'] == FALSE) {
  echo 'Need to specify address file using -f argument\n'
}

$file_name = args['f'];
$address_file = file_get_contents($file_name);
$addresses = preg_split('/$\R?^/m', $address_file);

date_default_timezone_set('America/New_York');
// We choose 9:00am to give us some time to ger to SEPTA.
$unix_timestamp = mktime(9, 0, 0, 11, 4, 2014);

$travel_info = array();

// API Params.
foreach ($addresses as $address) {
    $params = array(
        'origin'         => '500 College Avenue, Swarthmore, PA 19081',
        'destination'    => $address,
        'mode'           => 'transit',
        'departure_time' => $unix_timestamp,
        'sensor'         => 'true',
        //'key'            => 'AIzaSyC0w2eSvlh3eeLnj3c0c8XVPW6U_IYfYY4'
    );
        
    // Join parameters into URL string
    $params_string = '';
    foreach($params as $var => $val){
        $params_string .= '&' . $var . '=' . urlencode($val);  
    }
        
    // Request URL
    $url = 'https://maps.googleapis.com/maps/api/directions/json?'.ltrim($params_string, '&');

    // Make our API request and send using curl.
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($curl);
    curl_close($curl);

    $directions = json_decode($response);
    $full_dest_address = $directions->routes[0]->legs[0]->end_address;
    $travel_time_in_minutes = intval($directions->routes[0]->legs[0]->duration->value) / 60;

    $info_for_address = $full_dest_address . ' : ' . $travel_time_in_minutes . ' mins';
    array_push($travel_info, $info_for_address);
    // We are limited to 2 requests per second, so we halt for half a second before sending another request.
    sleep(0.5);
}

file_put_contents($file_name . '.out', implode('\n', $travel_info));

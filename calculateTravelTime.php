<?php

$args = getopt('f:');
if ($args == FALSE || !isset($args['f']) || $args['f'] == FALSE) {
    echo "Need to specify address file using -f argument.\n";
    return;
}

$file_name = $args['f'];
$address_file = file_get_contents($file_name);
$addresses = preg_split('/$\R?^/m', $address_file);

date_default_timezone_set('America/New_York');
// We choose 9:00am to give us some time to ger to SEPTA.
$unix_timestamp = mktime(8, 55, 0, 11, 4, 2014);

$travel_info = array();

// API Params.
foreach ($addresses as $address) {
    echo $address , "\n";
    $params = array(
        'origin'         => '500 College Avenue, Swarthmore, PA 19081',
        'destination'    => $address,
        'mode'           => 'transit',
        'departure_time' => $unix_timestamp,
        'alternatives'   => 'true',
        'key'            => 'AIzaSyC0w2eSvlh3eeLnj3c0c8XVPW6U_IYfYY4'
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
    $route_chosen = NULL;
    foreach ($directions->routes as $route) {
        $leg = $route->legs[0];
        
        //We prefer transit directions where we ride the 9:06am Septa.
        if (isset($leg->steps[1]->transit_details->departure_time) && $leg->steps[1]->transit_details->departure_time->text == '9:06am') {
            if ($route_chosen == NULL) {
                $route_chosen = $route;
            } else if ($leg->duration->value < $route_chosen->legs[0]->duration->value) {
                $route_chosen = $route;
            }
        }
    }
    
    //IF we don't encounter traveling directions where we ride the 9:06 Septa, simply choose the first, best, route.
    if ($route_chosen == NULL) {
        $route_chosen = $directions->routes[0];
    }

    $full_dest_address = $route_chosen->legs[0]->end_address;
    $travel_time_in_minutes = round(intval($route_chosen->legs[0]->duration->value) / 60);
    
    $info_for_address = $full_dest_address . ' : ' . $travel_time_in_minutes . ' mins';
    array_push($travel_info, $info_for_address);
    // We are limited to 2 requests per second, so we halt for half a second before sending another request.
    sleep(1);
}

$output_file_name = $file_name . '.out';
file_put_contents($output_file_name, implode("\n", $travel_info));

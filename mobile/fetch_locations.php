<?php
/*
 * This script will output events based upon GPS
 * coordinates and time
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Author: Adam Perryman
 * Date: 04/03/2012
 */
include_once(dirname ( __FILE__ ) . "/../lib/arc/ARC2.php");
include_once(dirname ( __FILE__ ) . "/../lib/graphite/graphite/Graphite.php");
include_once(dirname ( __FILE__ ) . "/../config/datastore.php");

// Fake time globals
$time_start = mktime(0,0,0,9,2,2011);
$time_end   = mktime(0,0,0,9,4,2011);

$req = array_merge($_GET, $_POST);

if ( !isset($req['req']) ) {
    exit(1);
} else {
    $request = json_decode($req['req'],true);
}

// Unused§
// if ( isset($request['id']) ) {
//     $id = $request['id'];
// }

if ( isset($request['geo']) ) {

    // Get lat
    if ( isset($request['geo']['lat']) ) {
        $gps_lat = $request['geo']['lat'];
    } else {
        $gps_lat = 50.9358682;
    }

    // Get long
    if ( isset($request['geo']['long']) ) {
        $gps_lng = $request['geo']['long'];
    } else {
        $gps_lng = -1.3988324;
    }

} else {
    exit(1);
}

// Unused
// $graphs = $request['graphs'];

// TODO Replace with the OO method for requesting graphs
$q = '
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX ev: <http://purl.org/NET/c4dm/event.owl#>
PREFIX time: <http://purl.org/NET/c4dm/timeline.owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT DISTINCT ?lat ?long ?label WHERE {
  ?place geo:lat ?lat ; geo:long ?long ; rdfs:label ?label .
  ?url ev:place ?place ; ev:time ?timeOb .
  ?timeOb time:end ?end ; time:start ?start .
  FILTER (
    xsd:dateTime(?start) > xsd:dateTime("'.date(DATE_W3C,$time_start).'") &&
    xsd:dateTime(?end) < xsd:dateTime("'.date(DATE_W3C,$time_end).'")
  )
}';
// Output
$events = array();

if ($rows = $store->query($q, 'rows')) {
    foreach ($rows as $row) {
        // do check for gps

        $limit = 10; //get events within this limit (miles)

        $deg_per_rad = 57.29578;  // number of degrees/radian (for conversion)
        $distance = (3958 * pi() * sqrt(
                        ($gps_lat - $row['lat'])
                        * ($gps_lat - $row['lat'])
                        + cos($gps_lat / $deg_per_rad)
                        * cos($row['lat'] / $deg_per_rad)
                        * ($gps_lng - $row['long'])
                        * ($gps_lng - $row['long'])
                    ) / 180); //calculation found on the internet from stackoverflow

        //if the event is within the limit, then add to array.
        if ($distance <= $limit) {
            $array = array(
                'label' => $row['label'],
                'long'  => $row['long'],
                'lat'   => $row['lat'],
            );
           array_push($events, $array);
        }
    }
}

echo json_encode($events);

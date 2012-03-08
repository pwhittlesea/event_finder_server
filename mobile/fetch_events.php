<?php
/*
 * This script will output events based upon GPS 
 * coordinates and time
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 04/03/2012
 */
include_once(dirname ( __FILE__ ) . "/../lib/arc/ARC2.php");
include_once(dirname ( __FILE__ ) . "/../lib/graphite/graphite/Graphite.php");
include_once(dirname ( __FILE__ ) . "/../config/datastore.php");

// Fake time globals
$time_start = mktime(0,0,0,9,2,2011);
$time_end   = mktime(0,0,0,9,4,2011);
$gps_x = 50.9358682;
$gps_y = -1.3988324;

// TODO Replace with the OO method for requesting graphs
$q = '
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX ev: <http://purl.org/NET/c4dm/event.owl#>
PREFIX time: <http://purl.org/NET/c4dm/timeline.owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT DISTINCT ?lat ?long ?url ?label ?start ?end WHERE {
  ?s geo:lat ?lat ; geo:long ?long .
  ?url ev:place ?s ; rdfs:label ?label ; ev:time ?timeOb .
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
        // todo calculate actual distance and then order events based on distance from point
        $p = 0.0001; // precision
        if ( ( abs($row['long']-$gps_y) < $p ) && ( abs($row['lat']-$gps_x) < $p ) ) {
            $array = array(
                'url'   => $row['url'],
                'long'  => $row['long'],
                'lat'   => $row['lat'],
                'label' => $row['label'],
                'start' => date(DATE_ATOM, strtotime($row['start'])),
                'end'   => date(DATE_ATOM, strtotime($row['end'])),
            );
           array_push($events, $array);
        }
    }
}

echo json_encode($events);
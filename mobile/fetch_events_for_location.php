<?php
/*
 * This script will output events based upon
 * a pre-defined location
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Author: Adam Perryman
 * Date: 28/04/2012
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

// Unused
// if ( isset($request['id']) ) {
//     $id = $request['id'];
// }

if ( !isset($request['place']) ) {
    exit(1);
}

// Unused
// $graphs = $request['graphs'];

// TODO Replace with the OO method for requesting graphs
$q = '
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX ev: <http://purl.org/NET/c4dm/event.owl#>
PREFIX time: <http://purl.org/NET/c4dm/timeline.owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT DISTINCT ?url ?label ?desc ?start ?end WHERE {
  ?url ev:place <'.$request['place'].'> ; rdfs:label ?label ; <http://purl.org/dc/terms/description> ?desc ; ev:time ?timeOb .
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
        $array = array(
            'url'   => $row['url'],
            'label' => $row['label'],
            'desc'  => $row['desc'],
            'start' => date(DATE_ATOM, strtotime($row['start'])),
            'end'   => date(DATE_ATOM, strtotime($row['end'])),
        );
       array_push($events, $array);
    }
}

echo json_encode($events);

<?php
/*
 * This script will crudely display data in the store
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 04/03/2012
 */
include_once("lib/arc/ARC2.php");
include_once("lib/graphite/graphite/Graphite.php");
include_once("config/datastore.php");

// Fake time globals
$time_start = mktime(0,0,0,9,2,2011);
$time_end   = mktime(0,0,0,9,4,2011);

$g = "
SELECT distinct ?g WHERE {
  GRAPH ?g { 
    ?s ?p ?o. 
  }
}
";

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
$r = '';
$k = '';

if ($rows = $store->query($g, 'rows')) {
    foreach ($rows as $row) {
        $k .= '<li>'.$row['g']. '</li>';
    }
}

// Fetch all matching rows from the store
if ($rows = $store->query($q, 'rows')) {
    foreach ($rows as $row) {
        $i++;
        $r .= '<li>(<a href="http://maps.google.co.uk/maps?q='.$row['lat'].','.$row['long'].'">' . $row['lat'] . ','. $row['long'] .
              '</a>) -> <a href="'. $row['url'] .'">'. $row['label']. '</a></li>';
    }
}

// Output all the things
echo $k ? "<ul>${k}</ul>" : 'no graphs found';
echo $r ? "<ul>${r}</ul>" : 'no events found';
echo "<pre>${q}</pre>";

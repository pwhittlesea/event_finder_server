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

// TODO Replace with the OO method for requesting graphs
$q = '
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX time: <http://purl.org/NET/c4dm/timeline.owl#>
SELECT * WHERE {
  ?a geo:long ?long .
  ?a geo:lat ?lat .
  ?a rdfs:label ?label .
  ?a time:end ?end .
  ?a time:start ?start .
  FILTER ( 
    xsd:dateTime(?end) > xsd:dateTime("'.date(DATE_W3C,$time_start).'") &&
    xsd:dateTime(?end) < xsd:dateTime("'.date(DATE_W3C,$time_end).'") 
  )
}';
// Iterator and output
$i = 0;
$r = '';

// Fetch all matching rows from the store
if ($rows = $store->query($q, 'rows')) {
    foreach ($rows as $row) {
        $i++; 
        $r .= '<li>(<a href="http://maps.google.co.uk/maps?q='.$row['lat'].','.$row['long'].'">' . $row['lat'] . ','. $row['long'] . 
              '</a>) -> <a href="'. $row['a'] .'">'. $row['label']. '</a></li>';
    }
}

// Output all the things
echo $i . ' in store';
echo $r ? '<ul>' . $r . '</ul>' : 'no events found';

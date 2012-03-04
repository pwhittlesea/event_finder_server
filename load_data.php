<?php
/*
 * This script will pull in data from a remote endpoint
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 04/03/2012
 */
include_once("lib/arc/ARC2.php");
include_once("lib/graphite/graphite/Graphite.php");
include_once("config/datastore.php");

$endpoint = 'http://sparql.data.southampton.ac.uk/';

// Fake time globals
$time_start = mktime(0,0,0,9,2,2011);
$time_end   = mktime(0,0,0,9,4,2011);

// TODO Replace with the OO method for requesting graphs
$query = '
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX ev: <http://purl.org/NET/c4dm/event.owl#>
PREFIX time: <http://purl.org/NET/c4dm/timeline.owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

CONSTRUCT {
  ?event rdfs:label ?label ; 
         time:end ?end ;
         time:start ?start ;
         ev:time ?time ; 
         geo:lat ?lat ; 
         geo:long ?long .
}
WHERE {
  ?s geo:lat ?lat ; 
     geo:long ?long .
  ?event ev:place ?s .
  ?event rdfs:label ?label .
  ?event ev:time ?timeOb .
  ?timeOb time:end ?end .
  ?timeOb time:start ?start .
  FILTER ( 
    xsd:dateTime(?end) > xsd:dateTime("'.date(DATE_W3C,$time_start).'") &&
    xsd:dateTime(?end) < xsd:dateTime("'.date(DATE_W3C,$time_end).'") 
  )
}';

// Construct an arbitrary request string
$add = "?query=".urlencode( $query )."&output=rdfxml&jsonp";

// Import the data into the local store
$store->query('LOAD <'. $endpoint . $add .'>');

// Has anything gone wrong
if ($errs = $store->getErrors()) {
  echo var_dump($errs);
}


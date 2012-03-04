<?php
/*
 * This script will pull in data from a remote endpoint
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 04/03/2012
 */
include_once("../lib/arc/ARC2.php");
include_once("../lib/graphite/graphite/Graphite.php");
include_once("../config/datastore.php");

// Read endpoint config from STDIN
if (isset($argc) && $argc > 1) {
    $config = parse_args($argv);
}

if (@$config["help"]) {
    print_help();
    exit(1);
}

// Ensure there are endpoints
if (!isset($config['endpoints'])) {
    echo "No Endpoints Specified: Exiting\n";
    exit(1);
}

// Ensure times are set
if (!isset($config['start'])) {
    $config['start'] = time();
} else {
    $config['start'] = strtotime($config['start'][0]);
}
if (!isset($config['end'])) {
    $config['end'] = time() + (60 * 60 * 24 * 2); // 48 hours later
} else {
    $config['end'] = strtotime($config['end'][0]);
}


foreach ($config['endpoints'] as $end) {
    fetch_triples($end);
}

echo "Loading Complete\n";

function fetch_triples($endpoint = null) {
    global $config,$store;

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
        xsd:dateTime(?end) > xsd:dateTime("'.date(DATE_W3C,$config['start']).'") &&
        xsd:dateTime(?end) < xsd:dateTime("'.date(DATE_W3C,$config['end']).'") 
      )
    }';
    
    // Construct an arbitrary request string
    $add = "?query=".urlencode( $query )."&output=rdfxml&jsonp";
    
    // Import the data into the local store
    $store->query('LOAD <'. $endpoint . $add .'> INTO <'. $endpoint .'>');
    
    // Has anything gone wrong
    if ($errs = $store->getErrors()) {
        foreach ($errs as $err) {
            echo "Error: ${err}\n";
        }
    } else {
        echo "Endpoint: ${endpoint} loaded\n";
    }
}

function parse_args($args) {
    $current_tag = "";
    for ($i=1;$i<count($args);$i++) {
        $arg = $args[$i];
        if (substr($arg,0,2) == "--") {
            $current_tag = substr($arg,2,strlen($arg));
            $config[$current_tag] = "set";
        } else {
            $things = explode(",",$arg);
            $config[$current_tag] = $things;
        }
    }
    return $config;
}

function print_help() {
    echo "usage: command [options]\n";
    echo "\n";
    echo "Options\n";
    echo "=======\n";
    echo "\n";
    echo "  --endpoints end1,end2,end3\n";
    echo "     list of endpoints to scrape\n";
    echo "\n";
    echo "  --start DD-MM-YYYY\n";
    echo "    OR\n";
    echo "  --start MM/DD/YYYY\n";
    echo "     start date to limit results\n";
    echo "\n";
    echo "  --end DD-MM-YYYY\n";
    echo "    OR\n";
    echo "  --end MM/DD/YYYY\n";
    echo "     end date to limit results\n";
    echo "\n";
}

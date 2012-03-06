<?php
/*
 * This script will crudely load location data into
 * the store
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 06/03/2012
 */
include_once("common.php");

// Ensure there are graphs
if (!isset($config['graph'])) {
    echo "No Dataset Specified: Exiting\n";
    exit(1);
} else {
    $config['graph'] = $config['graph'][0];
}

$graph = new Graphite();
$graph->ns( "geo","http://www.w3.org/2003/01/geo/wgs84_pos#" );
$graph->ns( "rdfs","http://www.w3.org/2000/01/rdf-schema#" );
$graph->ns( "ev","http://purl.org/NET/c4dm/event.owl#" );
$graph->ns( "time","http://purl.org/NET/c4dm/timeline.owl#" );
$graph->ns( "xsd","http://www.w3.org/2001/XMLSchema#" );

$placeQuery = "
  PREFIX event: <http://purl.org/NET/c4dm/event.owl#>
  SELECT DISTINCT ?p WHERE {
    GRAPH <".$config['graph']."> { 
      ?s a event:Event ;
         event:place ?p .
    }
  }
";

// Fetch all matching rows from the store
if ($rows = $store->query($placeQuery, 'rows')) {
    foreach ($rows as $row) {
        // Include place is graph
        $graph->load( $row['p'] );
    }
    
    // Once complete insert into the local store
    $arcTriples = $graph->toArcTriples();
    $store->insert($arcTriples,$config['graph'],0);
    unset($graph);
}

function print_help() {
    echo "usage: command [options]\n";
    echo "\n";
    echo "Options\n";
    echo "=======\n";
    echo "\n";
    echo "  --dataset data1\n";
    echo "     list of dataset to scrape\n";
    echo "\n";
}

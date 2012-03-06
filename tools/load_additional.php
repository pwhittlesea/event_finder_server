<?php
/*
 * This script will crudely load assiciated data into
 * the store
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 06/03/2012
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

$endpointQuery = "
  PREFIX rdfs: <http://rdfs.org/ns/void#>
  SELECT ?e WHERE {
    GRAPH <".$config['graph']."> { 
    ?p rdfs:sparqlEndpoint ?e . 
    }
  }
";

$calQuery = "
  PREFIX rdfs: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
  SELECT ?s WHERE {
    GRAPH <".$config['graph']."> { 
    ?s rdfs:type <http://purl.org/prog/Programme> .
    }
  }
";

$eventsQuery = "
  PREFIX event: <http://purl.org/NET/c4dm/event.owl#>
  PREFIX rdfs: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
  SELECT ?e WHERE {
    GRAPH <".$config['graph']."> { 
      ?e rdfs:type event:Event . 
    }
  }
";

// Fetch all matching rows from the store
if ($rows = $store->query($endpointQuery, 'rows')) {
    $endpoint = $rows[0]['e'];
} else {
    echo "ERROR: No Endpoint located\n";
    exit(1);
}

    
// Fetch all matching rows from the store
if ($rows = $store->query($eventsQuery, 'rows')) {
    foreach ($rows as $row) {
        graphite_it($row['e']);
    }
    echo $graph->serialize( "RDFXML" );
    unset($graph);
}

function graphite_it($cal = null) {
    global $graph,$endpoint;

    $cal = $graph->resource( $cal );
    $rdesc = $cal->prepareDescription();
    $rdesc->addRoute( '*' );
    $rdesc->addRoute( 'rdf:type' );
    $rdesc->addRoute( 'rdfs:label' );
    $rdesc->addRoute( 'ev:time/time:start' );
    $rdesc->addRoute( 'ev:time/time:end' );
    $rdesc->addRoute( 'ev:place/rdf:type' );
    $rdesc->addRoute( 'ev:place/rdfs:label' );
    $rdesc->addRoute( 'ev:place/geo:long' );
    $rdesc->addRoute( 'ev:place/geo:lat' );
    $n = $rdesc->loadSPARQL( $endpoint );
    unset($rdesc);
    unset($n);
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
    echo "  --dataset data1\n";
    echo "     list of dataset to scrape\n";
    echo "\n";
}

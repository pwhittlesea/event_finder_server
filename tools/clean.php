<?php
/*
 * This script will clean data from our store based on graph
 * endpoint/id and time
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 05/03/2012
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

// Ensure there are graphs
if (!isset($config['graphs'])) {
    echo "No Graphs Specified: Exiting\n";
    exit(1);
}

// Ensure times are set
if (!isset($config['before'])) {
    $config['before'] = time();
} else {
    $config['before'] = strtotime($config['before'][0]);
}


foreach ($config['graphs'] as $g) {
    clear_triples($g);
}

echo "Cleaning Complete\n";

function clear_triples($g = null) {
    global $config,$store;

    // TODO Replace with the OO method for requesting graphs
    $query = '
    PREFIX time: <http://purl.org/NET/c4dm/timeline.owl#>
    PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
    
    DELETE <'. $g .'> { 
      ?s ?p ?o 
    } WHERE { 
      ?s time:end ?end .
      FILTER ( 
        xsd:dateTime(?end) < xsd:dateTime("'.date(DATE_W3C,$config['before']).'") 
      )
    }';
    
    // Delte the data in the local store
    $store->query($query);
    
    // Has anything gone wrong
    if ($errs = $store->getErrors()) {
        foreach ($errs as $err) {
            echo "Error: ${err}\n";
        }
    } else {
        echo "Graph: ${g} cleaned\n";
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
    echo "  --graphs end1,end2,end3\n";
    echo "     list of graphs to clean\n";
    echo "\n";
    echo "  --before DD-MM-YYYY\n";
    echo "    OR\n";
    echo "  --before MM/DD/YYYY\n";
    echo "     clear records before this date\n";
    echo "\n";
}

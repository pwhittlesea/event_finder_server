<?php
/*
 * This script will crudely load data into the local store
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
if (!isset($config['dataset'])) {
    echo "No Dataset Specified: Exiting\n";
    exit(1);
} else {
    $config['dataset'] = $config['dataset'][0];
}

// Import the data into the local store
$store->query("LOAD <".$config['dataset']."> INTO <".$config['dataset'].">");
    
// Has anything gone wrong
if ($errs = $store->getErrors()) {
    foreach ($errs as $err) {
        echo "Error: ${err}\n";
    }
} else {
    echo "Dataset: ${uri} loaded\n";
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

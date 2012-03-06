<?php
/*
 * This script will crudely load data into the local store
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 06/03/2012
 */
include_once("common.php");

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
    echo "Dataset: ".$config['dataset']." loaded\n";
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

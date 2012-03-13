<?php
/*
 * This file will control our store
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 07/03/2012
 */
include_once(dirname ( __FILE__ ) . "/EF_LoadDataset.php");
include_once(dirname ( __FILE__ ) . "/EF_LoadExtras.php");
include_once(dirname ( __FILE__ ) . "/../config/datasets.php");

// Read endpoint config from STDIN
if (isset($argc) && $argc > 1) {
    $conf = parse_args($argv);
}

if ( @$conf['datasets'] ) {
    echo "Updating Datasets\n";
    $handlerDataset = EF_LoadDataset::getInstance();
    foreach ( $datasets as $set ) {
        $handlerDataset->datasetImport($set['d'], @$set['g'] ? $set['g'] : null);
    }
}

if ( @$conf['extras'] ) {
    echo "Updating Extras\n";
    $handlerExtras = EF_LoadExtras::getInstance();
    foreach ( $datasets as $set ) {
        $handlerExtras->extrasCollection(@$set['g'] ? $set['g'] : $set['d']);
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

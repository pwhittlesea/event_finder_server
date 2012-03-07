<?php
/*
 * This file will control our store
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 07/03/2012
 */
include_once(dirname ( __FILE__ ) . "/EF_LoadDataset.php");
include_once(dirname ( __FILE__ ) . "/EF_LoadExtras.php");

// datasets that are loaded by the system
$datasets = array(
    1 => array(
        'd' => 'http://id.southampton.ac.uk/dataset/events-diary/latest',
        ),
    2 => array(
        'd' => 'http://data.ox.ac.uk/graph/www-ox-ac-uk-events/data',
        ),
    3 => array(
        'd' => 'http://id.southampton.ac.uk/dataset/opendays-september-2011/latest',
        ),
    4 => array(
        'd' => 'http://programme.ecs.soton.ac.uk/glastonbury/2011/glastonbury2011.rdf',
        ),
    4 => array(
        'd' => 'http://programme.ecs.soton.ac.uk/glastonbury/2011/locations.rdf',
        'g' => 'http://programme.ecs.soton.ac.uk/glastonbury/2011/glastonbury2011.rdf',
        ),
    );

// Read endpoint config from STDIN
if (isset($argc) && $argc > 1) {
    $config = parse_args($argv);
}

if ( @$config['datasets'] ) {
    echo "Updating Datasets\n";
    $handlerDataset = EF_LoadDataset::getInstance();
    foreach ( $datasets as $set ) {
        $handlerDataset->datasetImport($set['d'], $set['g']);
    }
}

if ( @$config['extras'] ) {
    echo "Updating Extras\n";
    $handlerExtras = EF_LoadExtras::getInstance();
    foreach ( $datasets as $set ) {
        $handlerExtras->extrasCollection($set['d']);
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

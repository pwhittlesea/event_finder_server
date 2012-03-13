<?php
/*
 * This script will return the datasets available
 * to get events from.
 *
 * Author: Adam Perryman
 * Date: 12/03/2012
 */
include_once(dirname ( __FILE__ ) . "/../config/datasets.php");

$availableSets = '';

foreach ( $datasets as $set ) {
     $availableSets .= $set['d'] . ";";
}

echo $availableSets ? $availableSets : 'no datasets found';

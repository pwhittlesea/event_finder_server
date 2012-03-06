<?php
/*
 * Common funcitons shared among tools
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

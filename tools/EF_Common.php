<?php
/*
 * Common funcitons shared among tools
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 06/03/2012
 */
include_once(dirname ( __FILE__ ) . "/../lib/arc/ARC2.php");
include_once(dirname ( __FILE__ ) . "/../lib/graphite/graphite/Graphite.php");
include_once(dirname ( __FILE__ ) . "/../config/datastore.php");

class EF_Common { 

    // singleton instance   
    private static $instance; 

    protected static $config;

    // private constructor function 
    protected function __construct() {
        global $store;
        $this->store = $store;
    } 

    // getInstance method 
    public static function getInstance() { 
        if(!self::$instance) { 
            self::$instance = new self(); 
        } 
        return self::$instance; 
    } 
}

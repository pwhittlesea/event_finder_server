<?php
/*
 * This script will crudely load location data into
 * the store
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 07/03/2012
 */
include_once(dirname ( __FILE__ ) . "/EF_Common.php");
 
class EF_LoadExtras extends EF_Common { 

    // singleton instance   
    private static $instance; 

    // private constructor function 
    protected function __construct() { 
        parent::__construct();
    } 

    // getInstance method 
    public static function getInstance() { 
        if(!self::$instance) { 
            self::$instance = new self(); 
        } 
        return self::$instance; 
    } 
    
    // Fetch all matching rows from the store and add to graph
    private function loadToGraph($query = "", $graph) {     
        if ($rows = $this->store->query($query, 'rows')) {
            foreach ($rows as $row) {
                $graph->load( $row['extra'] );
            }
        }
    }
    
    // extrasCollection will gather additioal resources into graph
    public function extrasCollection($dataset = null) {
        // Ensure there are endpoints
        if ( $dataset == null ) {
            echo "No Graph Specified: Exiting\n";
            return 0;
        }
        
        $graph = new Graphite();
        $graph->ns( "geo","http://www.w3.org/2003/01/geo/wgs84_pos#" );
        $graph->ns( "rdfs","http://www.w3.org/2000/01/rdf-schema#" );
        $graph->ns( "ev","http://purl.org/NET/c4dm/event.owl#" );
        $graph->ns( "time","http://purl.org/NET/c4dm/timeline.owl#" );
        $graph->ns( "xsd","http://www.w3.org/2001/XMLSchema#" );

        $placeQuery = "PREFIX event: <http://purl.org/NET/c4dm/event.owl#>
          SELECT DISTINCT ?extra WHERE {
            GRAPH <${dataset}> { 
              ?s a event:Event ; event:place ?extra .
            }
          }";
        
        $timeQuery = "PREFIX event: <http://purl.org/NET/c4dm/event.owl#>
          SELECT DISTINCT ?extra WHERE {
            GRAPH <${dataset}> { 
              ?s a event:Event ; event:time ?extra .
            }
          }";
        
        $this->loadToGraph($timeQuery, $graph);
        $this->loadToGraph($placeQuery, $graph);
        
        // Once complete insert into the local store
        $arcTriples = $graph->toArcTriples();
        $this->store->insert($arcTriples, $dataset, 0);
        unset($graph);
        
        // Has anything gone wrong
        if ($errs = $this->store->getErrors()) {
            foreach ($errs as $err) {
                echo "Error: ${err}\n";
            }
            echo "Graph: ${dataset} failed\n";
            $this->store->resetErrors();
            return 0;
        } else {
            echo "Graph: ${dataset} updated\n";  
            return 1;
        }
    }

}

<?php
/*
 * This class will crudely load data into the local store
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 07/03/2012
 */
include_once(dirname ( __FILE__ ) . "/EF_Common.php");
 
class EF_LoadDataset extends EF_Common { 

    // singleton instance   
    private static $instance = null; 

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
    
    // datasetImport will import specified url into dataset
    public function datasetImport($dataset = null, $graph = null) {
        // Ensure there are endpoints
        if ( $dataset == null ) {
            echo "No Dataset Specified: Exiting\n";
            return 0;
        }
        
        // Allow graph to be overrriden
        if ( $graph == null )
            $graph = $dataset;

        // Import the data into the local store
        $this->store->query("LOAD <${dataset}> INTO <${graph}>");
    
        // Has anything gone wrong
        if ($errs = $this->store->getErrors()) {
            foreach ($errs as $err) {
                echo "Error: ${err}\n";
            }
            echo "Dataset: ${dataset} failed\n";
            $this->store->resetErrors();
            return 0;
        } else {
            echo "Dataset: ${dataset} loaded\n";  
            return 1;
        }
    }

}

<?php
/*
 * Allow SPARQL endpoint access to our server
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 05/03/2012
 */
 include_once("../lib/arc/ARC2.php");
include_once("../config/datastore.php");

/* instantiation */
$ep = ARC2::getStoreEndpoint($config);

/* request handling */
$ep->go();

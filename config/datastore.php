<?php
/*
 * This file contains the configuration for the datastore
 * on the local machine.
 *
 * Please modify the following details before deployment.
 *
 * Author: Phillip Whittlesea <pw.github@thega.me.uk>
 * Date: 04/03/2012
 */
$config = array(
  /* db */
  'db_name' => 'db_name',
  'db_user' => 'db_user',
  'db_pwd' => 'db_pass',
  /* store */
  'store_name' => 'event_finder',
  /* endpoint */
  'endpoint_features' => array(
    'select', 'construct', 'ask', 'describe',
  ),
  'endpoint_max_limit' => 250,
);
$store = ARC2::getStore($config);
if (!$store->isSetUp()) {
  $store->setUp();
}

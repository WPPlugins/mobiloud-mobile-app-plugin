<?php

include( "../../../wp-load.php" );

$debug = false;

header('Content-type: application/json');
$api = new MLApiController();
$api->set_error_handlers( $debug );

$response = $api->handle_request();

echo $response;
?>
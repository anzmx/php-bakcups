<?php
/* 
La funcion de este script era agregar automaticamente los puntos o monedas virtuales
que los usuarios compraban mediante mensajes de texto, utilizando la plataforma de pagos Paygol
se realizaba mediante Notificación Instantánea de Pago o IPN por sus siglas en ingles, que realizaba una solicitud GET a este archivo.
*/
require 'config.php';
require 'engine/database/connect.php';

// verificar conexion paygol
if(!in_array($_SERVER['REMOTE_ADDR'],
	array('109.70.3.48', '109.70.3.146', '109.70.3.58'))) {
	header("HTTP/1.0 403 Forbidden");
	die("Error: IP Desconocida");
}

// POST-GET values
function getValue($value) {
	return (!empty($value)) ? sanitize($value) : false;
}
function sanitize($data) {
	return htmlentities(strip_tags(mysql_webserv_escape_string($data)));
}

// parametros pago
$message_id	= getValue($_GET['message_id']);
$service_id	= getValue($_GET['service_id']);
$shortcode	= getValue($_GET['shortcode']);
$keyword	= getValue($_GET['keyword']);
$message	= getValue($_GET['message']);
$sender	 = getValue($_GET['sender']);
$operator	= getValue($_GET['operator']);
$country	= getValue($_GET['country']);
$custom	 = getValue($_GET['custom']);
$points	 = getValue($_GET['points']);
$price	 = getValue($_GET['price']);
$currency	= getValue($_GET['currency']);

// leer cfg.php
$paygol = $config['paygol'];

// service id paygol
if($service_id != $paygol['serviceID']) {
	header("HTTP/1.0 403 Forbidden");
	die("Error: serviceID does not match.");
}

$new_points = $paygol['points'];
// logs
mysql_insert("INSERT INTO `webserv_paygol` VALUES ('', '$custom', '$price', '$new_points', '$message_id', '$service_id', '$shortcode', '$keyword', '$message', '$sender', '$operator', '$country', '$currency')");
// agregar puntos
$account = mysql_select_single("SELECT `points` FROM `webserv_accounts` WHERE `account_id`='$custom';");
$new_points = $account['points'] + $new_points;
mysql_update("UPDATE `webserv_accounts` SET `points`='$new_points' WHERE `account_id`='$custom'");
?>
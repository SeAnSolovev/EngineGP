<?php
if (!DEFINED('EGP'))
    exit(header('Refresh: 0; URL=http://' . $_SERVER['HTTP_HOST'] . '/404'));

$sql->query('SELECT `name`, `slots_min`, `slots_max`, `install`, `fps`, `timext`, `discount`, `price` FROM `tarifs` WHERE `id`="' . $server['tarif'] . '" LIMIT 1');
$tarif = $sql->get();

// Подразделы
$aSub = array('extend', 'plan', 'slots');

include(SEC . 'megp/servers/games/tarif.php');
?>
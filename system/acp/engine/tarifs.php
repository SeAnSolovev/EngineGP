<?php
/*
 * EngineGP   (https://enginegp.ru or https://enginegp.com)
 *
 * @link      https://github.com/EngineGPDev/EngineGP
 * @link      https://gitforge.ru/EngineGP/EngineGP
 * @copyright Copyright (c) Solovev Sergei <inbox@seansolovev.ru>
 * @license   https://github.com/EngineGPDev/EngineGP/blob/main/LICENSE
 * @license   https://gitforge.ru/EngineGP/EngineGP/src/branch/main/LICENSE
 */

if (!DEFINED('EGP'))
    exit(header('Refresh: 0; URL=http://' . $_SERVER['HTTP_HOST'] . '/404'));

$info = '<i class="fa fa-shopping-bag"></i> Управление тарифами';

$aSection = array(
    'index',
    'add',
    'copy',
    'stats',
    'delete'
);

if (!in_array($section, $aSection))
    $section = 'index';

$html->get('menu', 'sections/tarifs');

$html->unit('s_' . $section, true);

unset($aSection[array_search($section, $aSection)]);

foreach ($aSection as $noactive)
    $html->unit('s_' . $noactive);

$sql->query('SELECT `id` FROM `tarifs`');
$html->set('tarifs', $sql->num());

$html->pack('menu');

include(SEC . 'tarifs/' . $section . '.php');

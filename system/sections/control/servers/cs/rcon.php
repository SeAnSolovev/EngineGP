<?php
/*
 * EngineGP   (https://enginegp.ru or https://enginegp.com)
 *
 * @copyright Copyright (c) 2018-present Solovev Sergei <inbox@seansolovev.ru>
 *
 * @link      https://github.com/EngineGPDev/EngineGP for the canonical source repository
 * @link      https://gitforge.ru/EngineGP/EngineGP for the canonical source repository
 *
 * @license   https://github.com/EngineGPDev/EngineGP/blob/main/LICENSE MIT License
 * @license   https://gitforge.ru/EngineGP/EngineGP/src/branch/main/LICENSE MIT License
 */

if (!DEFINED('EGP'))
    exit(header('Refresh: 0; URL=http://' . $_SERVER['HTTP_HOST'] . '/404'));

if ($go) {
    include(LIB . 'control/' . $server['game'] . '/rcon.php');

    if (isset($url['action']) and in_array($url['action'], array('kick', 'kill'))) {
        $player = isset($_POST['player']) ? $_POST['player'] : sys::outjs(array('e' => 'Необходимо выбрать игрока.'));

        if ($url['action'] == 'kick')
            rcon::cmd(array_merge($server, array('id' => $id)), 'amx_kick "' . $player . '" "EGP Panel"');
        else
            rcon::cmd(array_merge($server, array('id' => $id)), 'amx_slay "' . $player . '"');

        sys::outjs(array('s' => 'ok'));
    }

    include(LIB . 'geo.php');
    $SxGeo = new SxGeo(DATA . 'SxGeoCity.dat');

    $aPlayers = rcon::players(rcon::cmd(array_merge($server, array('id' => $id))));

    foreach ($aPlayers as $i => $aPlayer) {
        $html->get('player', 'sections/control/servers/' . $server['game'] . '/rcon');

        $html->set('i', $i);
        $html->set('name', $aPlayer['name']);
        $html->set('steamid', $aPlayer['steamid']);
        $html->set('time', $aPlayer['time']);
        $html->set('ping', $aPlayer['ping']);
        $html->set('ip', $aPlayer['ip']);
        $html->set('ico', $aPlayer['ico']);
        $html->set('country', $aPlayer['country']);

        $html->pack('players');
    }

    sys::outjs(array('s' => isset($html->arr['players']) ? $html->arr['players'] : ''));
}

$html->nav('Список подключенных серверов', $cfg['http'] . 'control');
$html->nav('Список игровых серверов #' . $id, $cfg['http'] . 'control/id/' . $id);
$html->nav($server['address'], $cfg['http'] . 'control/id/' . $id . '/server/' . $sid);
$html->nav('Rcon управление игроками');

$html->get('rcon', 'sections/control/servers/' . $server['game']);

$html->set('id', $id);
$html->set('server', $sid);

$html->pack('main');

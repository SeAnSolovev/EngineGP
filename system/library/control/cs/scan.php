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

include(LIB . 'control/scans.php');

use xPaw\SourceQuery\SourceQuery;

class scan extends scans
{
    public static function mon($id, $players_get = false)
    {
        global $cfg, $sql, $html, $mcache;

        $sq = new SourceQuery();

        if ($players_get)
            $nmch = 'ctrl_server_scan_mon_pl_' . $id;
        else
            $nmch = 'ctrl_server_scan_mon_' . $id;

        if (is_array($mcache->get($nmch)))
            return $mcache->get($nmch);

        $out = array();

        $info = scan::info($sq, $id);

        $sql->query('SELECT `unit`, `game`, `name`, `map`, `online`, `players`, `status` FROM `control_servers` WHERE `id`="' . $id . '" LIMIT 1');
        $server = $sql->get();

        if (!$info['status']) {
            $out['name'] = $server['name'];
            $out['status'] = sys::status($server['status'], $server['game'], $server['map']);
            $out['online'] = $server['online'];
            $out['image'] = '<img src="' . sys::status($server['status'], $server['game'], $server['map'], 'img') . '">';
            $out['buttons'] = sys::buttons($id, $server['status'], $server['game'], $server['unit']);

            if ($players_get)
                $out['players'] = base64_decode($server['players']);

            $mcache->set($nmch, $out, false, $cfg['mcache_server_mon']);

            return $out;
        }

        if ($players_get)
            $players = scan::info($sq, $id, true);

        $out['name'] = htmlspecialchars($info['name']);
        $out['status'] = sys::status('working', $server['game'], $info['map']);
        $out['online'] = $info['online'];
        $out['image'] = '<img src="' . sys::status('working', $server['game'], $info['map'], 'img') . '">';
        $out['buttons'] = sys::buttons($id, 'working', $server['game'], $server['unit']);
        $out['players'] = '';

        if ($players_get) {
            foreach ($players as $index => $player) {
                $html->get($server['game'], 'sections/servers/players');

                $html->set('i', $player['i']);
                $html->set('name', $player['name']);
                $html->set('score', $player['score']);
                $html->set('time', $player['time']);

                $html->pack('list');
            }

            $out['players'] = isset($html->arr['list']) ? $html->arr['list'] : '';
        }

        $sql->query('UPDATE `control_servers` set '
            . '`name`="' . $out['name'] . '", '
            . '`online`="' . $out['online'] . '", '
            . '`map`="' . $info['map'] . '", '
            . '`status`="working" WHERE `id`="' . $id . '" LIMIT 1');

        if ($players_get)
            $sql->query('UPDATE `control_servers` set `players`="' . base64_encode($out['players']) . '" WHERE `id`="' . $id . '" LIMIT 1');

        $mcache->set($nmch, $out, false, $cfg['mcache_server_mon']);

        return $out;
    }

    public static function info($sq, $id, $pl = false)
    {
        global $sql;

        $sql->query('SELECT `address` FROM `control_servers` WHERE `id`="' . $id . '" LIMIT 1');
        $server = $sql->get();

        list($ip, $port) = explode(':', $server['address']);

        $sq->Connect($ip, $port, 1, SourceQuery::GOLDSOURCE);

        if ($pl) {
            $players = $sq->GetPlayers();

            $i = 1;
            $data = array();

            foreach ($players as $n => $player) {
                $data[$i]['i'] = $i;
                $data[$i]['name'] = $player['Name'] == '' ? 'Подключается' : $player['Name'];
                $data[$i]['score'] = $player['Frags'];
                $data[$i]['time'] = $player['TimeF'];

                $i += 1;
            }

            return $data;
        }

        try {
            $data = $sq->GetInfo();

            $server['name'] = $data['HostName'];
            $server['map'] = $data['Map'];
            $server['online'] = $data['Players'];
            $server['status'] = strlen($server['map']) > 3;
        } catch (Exception $e) {
            // В случае, если не удалось получить данные из сокета, то подставляем значения из базы данных
            $server['name'] = isset($server['name']);
            $server['map'] = isset($server['map']);
            $server['online'] = isset($server['online']);
            $server['status'] = strlen($server['map']) > 3;
        }

        return $server;
    }
}

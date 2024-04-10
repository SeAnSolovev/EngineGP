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

class threads extends cron
{
    function __construct()
    {
        global $sql, $cfg, $argv;

        $aUnit = array();
        $sql->query('SELECT `id` FROM `units` ORDER BY `id` ASC');

        if (!$sql->num())
            return NULL;

        while ($unit = $sql->get())
            $aUnit[$unit['id']] = [];

        $sql->query('SELECT `id` FROM `servers` LIMIT 1');

        if (!$sql->num())
            return NULL;

        $sql->query('SELECT `id`, `unit`, `game` FROM `servers` ORDER BY `unit` DESC');

        $all = $sql->num();

        while($server = $sql->get()) {
            $aUnit[$server['unit']][$server['game']] ??= [];
            $aUnit[$server['unit']][$server['game']][] = $server['id'];
        }

        if ($argv[3] == 'scan_servers_route')
            cron::$seping = 50;

        foreach ($aUnit as $unit => $aGame) {
            foreach ($aGame as $game => $servers) {
                if(is_array($servers)) {
                    $servers = implode(' ', $servers);
                }
                $aData = explode(' ', $servers);

                $num = count($aData) - 1;
                $sep = $num > 0 ? ceil($num / cron::$seping) : 1;

                unset($aData[end($aData)]);

                $threads[] = cron::thread($sep, $game . ' ' . $unit, $aData);
            }
        }

        $cmd = '';

        foreach ($threads as $thread) {
            foreach ($thread as $screen => $servers)
                $cmd .= 'sudo -u www-data screen -dmS scan_' . (sys::first(explode(' ', $servers))) . '_' . $screen . ' taskset -c ' . $cfg['cron_taskset'] . ' sh -c \"cd /var/www/enginegp; php cron.php ' . $cfg['cron_key'] . ' ' . $argv[3] . ' ' . $servers . '\"; sleep 1;';
        }

        $start_point = $_SERVER['REQUEST_TIME'];
        exec('screen -dmS threads_' . date('His', $start_point) . ' sh -c "' . $cmd . '"');

        return NULL;
    }
}

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

$nmch = 'server_copy_check_' . $id;

if ($mcache->get($nmch))
    sys::outjs(array('e' => sys::text('other', 'mcache')));

$mcache->set($nmch, true, false, 10);

$copys = $sql->query('SELECT `id` FROM `copy` WHERE `user`="' . $server['user'] . '_' . $server['unit'] . '" AND `status`="0"');
if (!$sql->num($copys))
    sys::outjs(array('e' => 'no find'), $nmch);

while ($copy = $sql->get($copys)) {
    if (!sys::int($ssh->get('ps aux | grep copy_' . $server['uid'] . ' | grep -v grep | awk \'{print $2}\'')))
        $sql->query('UPDATE `copy` set `status`="1" WHERE `id`="' . $copy['id'] . '" LIMIT 1');
}

// Очистка кеша
$mcache->delete('server_copy_' . $id);

sys::outjs(array('s' => 'ok'), $nmch);

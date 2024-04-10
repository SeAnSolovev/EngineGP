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

$list = '';

$sql->query('SELECT `id` FROM `logs` WHERE `type`="extend"');

$aPage = sys::page($page, $sql->num(), 40);

sys::page_gen($aPage['ceil'], $page, $aPage['page'], 'acp/logs/section/extend');

$sql->query('SELECT `id`, `user`, `text`, `date`, `money` FROM `logs` WHERE `type`="extend" ORDER BY `id` DESC LIMIT ' . $aPage['num'] . ', 40');
while ($log = $sql->get()) {
    $list .= '<tr>';
    $list .= '<td>' . $log['id'] . '</td>';
    $list .= '<td>' . $log['text'] . '</td>';
    $list .= '<td class="text-center"><a href="' . $cfg['http'] . 'acp/users/id/' . $log['user'] . '">USER_' . $log['user'] . '</a></td>';
    $list .= '<td class="text-center">' . $log['money'] . '</td>';
    $list .= '<td class="text-center">' . date('d.m.Y - H:i:s', $log['date']) . '</td>';
    $list .= '</tr>';
}

$html->get('logs', 'sections/logs');

$html->set('list', $list);

$html->set('pages', isset($html->arr['pages']) ? $html->arr['pages'] : '');

$html->pack('main');

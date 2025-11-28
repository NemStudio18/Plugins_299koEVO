<?php

use Utils\Util;
use Core\Lang;
use Core\Core;
use Core\Auth\User;

/**
 * @copyright (C) 2022, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No pagesFileect script access allowed');

## Fonction d'installation

function contactInstall() {
    Util::writeJsonFile(DATA_PLUGIN . 'contact/emails.json', []);
    $data = Util::readJsonFile(DATA_PLUGIN . 'contact/config.json');
    Lang::loadLanguageFile(PLUGINS . 'contact/langs/');
    $data['acceptation'] = Lang::get('contact.default-acceptation');
    Util::writeJsonFile(DATA_PLUGIN . 'contact/config.json', $data);
}

## Hooks
## Code relatif au plugin

function contactSave($email) {
    $data = Util::readJsonFile(DATA_PLUGIN . 'contact/emails.json');
    $data[] = $email;
    Util::writeJsonFile(DATA_PLUGIN . 'contact/emails.json', array_unique($data));
}

function contactSend() {
    global $runPlugin;
    $core = Core::getInstance();
    $from = '299ko@' . $_SERVER['SERVER_NAME'];
    $reply = strip_tags(trim($_POST['email']));
    $name = strip_tags(trim($_POST['name']));
    $firstName = strip_tags(trim($_POST['firstname']));
    $msg = strip_tags(trim($_POST['message']));
    if (!Util::isEmail($reply) || $name == '' || $firstName == '' || $msg == '')
        return false;
    contactSave($reply);
    $to = User::findPK($runPlugin->getConfigVal('userMailId'))->email;
    $subject = 'Contact ' . $core->getConfigVal('siteName');
    $msg = $msg . "\n\n----------\n\n" . $name . " " . $firstName . " (" . $reply . ")";
    if (Util::isEmail($runPlugin->getConfigVal('copy')))
        Util::sendEmail($from, $reply, $runPlugin->getConfigVal('copy'), $subject, $msg);
    return Util::sendEmail($from, $reply, $to, $subject, $msg);
}
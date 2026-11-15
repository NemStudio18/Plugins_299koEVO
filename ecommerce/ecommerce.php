<?php

namespace Ecommerce {

use Core\Plugin\PluginsManager;

defined('ROOT') or exit('No direct script access allowed');

require_once PLUGINS . 'ecommerce/entities/Database.php';
require_once PLUGINS . 'ecommerce/entities/Category.php';
require_once PLUGINS . 'ecommerce/entities/CategoryManager.php';
require_once PLUGINS . 'ecommerce/entities/Product.php';
require_once PLUGINS . 'ecommerce/entities/ProductManager.php';
require_once PLUGINS . 'ecommerce/entities/Cart.php';
require_once PLUGINS . 'ecommerce/entities/Order.php';
require_once PLUGINS . 'ecommerce/entities/OrderItem.php';
require_once PLUGINS . 'ecommerce/entities/OrderManager.php';

class Hooks
{
    public static function install(): void
    {
        if (!is_dir(DATA_PLUGIN . 'ecommerce')) {
            @mkdir(DATA_PLUGIN . 'ecommerce', 0755, true);
        }
        // La création et la migration des tables sont gérées automatiquement par les entités JsonActiveRecord
        // lorsqu'elles sont utilisées pour la première fois (via FallbackStorage/SqlStorage/JsonStorage).
        \Core\Core::getInstance()->getLogger()->log(\Core\Logger::LEVEL_INFO, "[ecommerce::install] Entities will handle schema migration.");
    }

    public static function endFrontHead($param = ''): string
    {
        $plugin = PluginsManager::getInstance()->getPlugin('ecommerce');
        $output = (string)$param;

        if (!$plugin || !$plugin->getConfigVal('activate')) {
            return $output;
        }
        $output .= '<link rel="stylesheet" href="' . \Utils\Util::urlBuild(PLUGINS . 'ecommerce/template/public.css') . '" type="text/css"/>' . "\n";
        return $output;
    }
}

}

namespace {

    function ecommerceInstall(): void
    {
        \Ecommerce\Hooks::install();
    }

    function ecommerceEndFrontHead($param = ''): string
    {
        return \Ecommerce\Hooks::endFrontHead($param);
    }

}

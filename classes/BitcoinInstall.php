<?php
/**
 * 2013-2015 ZL Development
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to <prestashop@zakarialounes.fr> so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Bitcoin module to newer
 * versions in the future. If you wish to customize Bitcoin module for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @package    Bitcoin
 * @author     Zakaria Lounes <me@zakarialounes.fr>
 * @copyright  2013-2015 ZL Development
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @note       Property of ZL Development
 */

/**
 * Description of BitcoinInstall
 *
 * @author Zakaria Lounes <me@zakarialounes.fr>
 */
class BitcoinInstall {

    public static $psBitcoinSecret = 'BITCOIN_SECRET';
    public static $psBitcoinAddr = 'BITCOIN_ADDR';
    public static $psBitcoinCallLimit = 'BITCOIN_CALL_LIMIT';

    public function __construct() {
        // Check if BCMath module installed
        if (!function_exists('bcscale')) {
            throw new Blockchain_Error("BC Math module not installed.");
        }
        // Check if curl module installed
        if (!function_exists('curl_init')) {
            throw new Blockchain_Error("cURL module not installed.");
        }
    }

    /**
     * Create table
     */
    private function createTables() {
        if (!Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bitcoin_order` (
                `id_cart` int(11) NOT NULL,
                `addr` varchar(255) NOT NULL,
                `dest_addr` varchar(255) NOT NULL,
                `secret` varchar(255) NOT NULL,
                `transaction_hash` varchar(255) DEFAULT NULL,
                `input_transaction_hash` varchar(255) DEFAULT NULL,
                `input_addr` varchar(255) DEFAULT NULL,
                `total` decimal(16,8) NOT NULL,
                `total_paid` decimal(16,8) DEFAULT NULL,
                `payment_date` datetime DEFAULT NULL,
                UNIQUE KEY `id_cart` (`id_cart`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;')) {
            return false;
        }
    }

    /**
     * Set configuration table
     */
    private function updateConfiguration($secret, $addr, $callLimit) {
        Configuration::updateValue(self::$psBitcoinSecret, $secret);
        Configuration::updateValue(self::$psBitcoinAddr, $addr);
        Configuration::updateValue(self::$psBitcoinCallLimit, $callLimit);
    }

    /**
     * Delete Bitcoin configuration
     */
    private function deleteConfiguration() {
        Configuration::deleteByName(self::$psBitcoinSecret);
        Configuration::deleteByName(self::$psBitcoinAddr);
        Configuration::deleteByName(self::$psBitcoinCallLimit);
    }

    /**
     * Create a new order state
     */
    private function createOrderState() {
        if (Configuration::get('_PS_OS_BITCOINSTATUT_WAITING') ||
                Configuration::get('_PS_OS_BITCOINSTATUT_ACCEPTED')) {
            return;
        }

        $label = Array(
            Array(
                'statut' => 'WAITING',
                'label' => Array(
                    'Waiting for payment Bitcoin',
                    'En attente du paiement par Bitcoin'
                ),
                'color' => '#4169E1',
                'invoice' => false
            ),
            Array(
                'statut' => 'ACCEPTED',
                'label' => Array(
                    'Authorization accepted by Bitcoin',
                    'Autorisation acceptée par Bitcoin'
                ),
                'color' => '#32CD32',
                'invoice' => true,
                'model' => 'payment'
            )
        );

        for ($i = 0; $i < count($label); $i++) {
            foreach (Language::getLanguages() as $key => $language) {
                if ($key % 2 == 0) {
                    $orderState = new OrderState();
                    $orderState->name = array();
                    $orderState->template = array();
                }

                $lang = $language['id_lang'];
                $orderState->name[$lang] = $label[$i]['label'][$lang];

                if ($key % 2 == 0) {
                    $orderState->color = $label[$i]['color'];
                    $orderState->unremovable = false;
                    $orderState->hidden = false;
                    $orderState->delivery = false;
                    $orderState->logable = false;
                    $orderState->send_email = true;

                    if ($label[$i]['invoice']) {
                        $orderState->template[$lang] = $label[$i]['model'];
                        $orderState->invoice = true;
                    } else {
                        $orderState->invoice = false;
                    }

                    if ($orderState->add()) {
                        copy(dirname(__FILE__).'/../logo.gif', _PS_IMG_DIR_.'os/'.$orderState->id.'.gif');
                    }

                    Configuration::updateValue('_PS_OS_BITCOINSTATUT_'.$label[$i]['statut'], (int)$orderState->id);
                }
            }
        }
    }

    /**
     * Todo
     */
    private function getGenerateSecretKey() {
        return ("1K4N3BvpG8DwL4MirbtCHF3udrd5wmS");
    }

    /**
     * Install Bitcoin
     */
    public static function install($secret, $addr, $callLimit) {
        $install = new BitcoinInstall();
        $install->createTables();
        $install->updateConfiguration($secret, $addr, $callLimit);
        $install->createOrderState();
    }

    /**
     * Uninstall Bitcoin
     */
    public static function uninstall() {
        $uninstall = new BitcoinInstall();
        $uninstall->deleteConfiguration();
    }

}
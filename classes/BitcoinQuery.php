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
 * Description of BitcoinQuery
 *
 * @author Zakaria Lounes <me@zakarialounes.fr>
 */
class BitcoinQuery {

    private function setInitializeOrderPayment($data) {
        if ($data === false) {
            return false;
        }

        $query = "INSERT INTO `"._DB_PREFIX_."bitcoin_order` "
                ."(`id_cart`, `addr`, `dest_addr`, `secret`, `total`) "
                ."VALUES ("
                ."'".(int)$data['id_cart']."', "
                ."'".(string)$data['blockchain_data']->address."', "
                ."'".(string)$data['blockchain_data']->destination."', "
                ."'".(string)$data['secret']."', "
                ."'".(float)$data['total_btc']."');";
        $res = Db::getInstance()->Execute($query);

        if (!$res) {
            die(Db::getInstance()->getMsgError());
        }

        return true;
    }

    public static function setInitializeOrderPaymentStatic($data) {
        $q = new BitcoinQuery();

        return $q->setInitializeOrderPayment($data);
    }

    private function getTotal($orderId) {
        $query = "SELECT `total` FROM `" . _DB_PREFIX_ . "bitcoin_order` "
                . "WHERE `id_cart` = '" . (int)$orderId . "'";
        $res = Db::getInstance()->getValue($query);

        return $res;
    }

    public static function getTotalStatic($orderId) {
        $q = new BitcoinQuery();

        return $q->getTotal($orderId);
    }

    private function setOrderSet($blockchain_data) {
        $now = new DateTime('now');
        $query = "UPDATE `"._DB_PREFIX_."bitcoin_order` "
                ."SET `transaction_hash` = '".(string)$blockchain_data['transaction_hash']."', "
                ."`input_transaction_hash` = '".(string)$blockchain_data['input_transaction_hash']."', "
                ."`input_addr` = "."'".(string)$blockchain_data['input_addr']."', " // error
                ."`total_paid` = "."'".(float)($blockchain_data['value'] / 100000000)."', "
                ."`payment_date` =  "."'".(string)$now->format(DateTime::W3C)."' "
                ."WHERE `id_cart` = '".(int)$blockchain_data['id_cart']."';";

        return Db::getInstance()->Execute($query);
    }

    public static function setOrderSetStatic($orderId) {
        $q = new BitcoinQuery();

        return $q->setOrderSet($orderId);
    }

}
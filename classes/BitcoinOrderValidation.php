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

require_once(_PS_MODULE_DIR_.'bitcoin/classes/BitcoinInstall.php');
require_once(_PS_MODULE_DIR_.'bitcoin/classes/BitcoinQuery.php');

/**
 * Description of BitcoinrOrdeValidation
 *
 * @author Zakaria Lounes <prestashop@zakarialounes.fr>
 */
class BitcoinOrderValidation {

    private $conf;
    private $order;
    private $blockchain;
    private $blockchain_data;

    private function getConf() {
        return $this->conf;
    }

    private function setConf($arr) {
        $this->conf = $arr;

        return $this;
    }

    private function getOrder() {
        return $this->order;
    }

    private function setOrder(Order $order) {
        $this->order = $order;

        return $this;
    }

    private function getBlockchain() {
        return $this->blockchain;
    }

    private function setBlockchain(Blockchain $blockchain) {
        $this->blockchain = $blockchain;

        return $this;
    }

    private function getBlockchainData() {
        return $this->blockchain_data;
    }

    private function setBlockchainData($arr) {
        $this->blockchain_data = $arr;
        $this->setOrder(new Order(Order::getOrderByCartId($arr['id_cart'])));

        return $this;
    }

    private function updateOrder() {
        $accepted_state = (int)Configuration::get('_PS_OS_BITCOINSTATUT_ACCEPTED');

        try {
            $this->order->setCurrentState($accepted_state);
            BitcoinQuery::setOrderSetStatic($this->blockchain_data);
            echo '*ok*';
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    private function checkReceipts(Order $order) {
        $blockchain_btc = (float)($this->blockchain_data['value'] / 100000000);
        $total_btc = BitcoinQuery::getTotalStatic($order->id);

        if ($blockchain_btc < $total_btc) {
            return false;
        }

        return true;
    }

    private function checkSecretKey($my_secretKey, $blockchain_secretKey) {
        if (Tools::strlen($my_secretKey) <= 0 || $my_secretKey !== $blockchain_secretKey) {
            return false;
        }

        return true;
    }

    private function checkSecurity() {
        if (!$this->checkSecretKey($this->conf['secret'], $this->blockchain_data['secret'])) {
            return false;
        }
        if ($this->blockchain_data['test'] == true) {
            return false;
        }
        if ($this->blockchain_data['confirmations'] < 6) {
            return false;
        }

        $curr_state = (int)$this->order->getCurrentOrderState()->id;
        $accepted_state = (int)Configuration::get('_PS_OS_BITCOINSTATUT_ACCEPTED');

        if (!$this->order || ($accepted_state === $curr_state) || !$this->checkReceipts($this->order)) {
            return false;
        }

        return true;
    }

    private function blockchainCallback() {
        if (!$this->checkSecurity($this->conf, $this->blockchain_data)) {
            echo 'Waiting...';
            return;
        }

        $this->updateOrder();
    }

    public static function blockchainCallbackStatic(Blockchain $blockchain) {
        $BitcoinrOrdeValidation = new BitcoinOrderValidation($blockchain);
        $BitcoinrOrdeValidation->blockchainCallback();
    }

    public function __construct(Blockchain $blockchain) {
        header("Content-Type:text/plain");

        $this
                ->setConf(Array(
                    'addr' => (string)Configuration::get(BitcoinInstall::$psBitcoinAddr),
                    'secret' => (string)Configuration::get(BitcoinInstall::$psBitcoinSecret)))
                ->setBlockchain($blockchain)
                ->setBlockchainData(Array(
                    'id_cart' => (int)Tools::getValue('id_cart'),
                    'secret' => (string)Tools::getValue('secret'),
                    'addr' => (string)Tools::getValue('address'),
                    'dest' => (string)Tools::getValue('destination'),
                    'transaction_hash' => (string)Tools::getValue('transaction_hash'),
                    'input_transaction_hash' => (string)Tools::getValue('input_transaction_hash'),
                    'input_addr' => (string)Tools::getValue('input_addr'),
                    'value' => (string)Tools::getValue('value'),
                    'confirmations' => (int)Tools::getValue('confirmations'),
                    'test' => Tools::getValue('test')))
        ;
    }

}
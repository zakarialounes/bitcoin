<?php
/**
 * 2013-2015 ZL Development
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file <LICENSE.txt>.
 * It is also available through the world-wide-web at this URL:
 * <http://opensource.org/licenses/afl-3.0.php>
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to <prestashop@zakarialounes.fr> so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Bitcoin module to newer
 * versions in the future. If you wish to customize Bitcoin module for your
 * needs please refer to <http://www.prestashop.com> for more information.
 *
 *  @author    ZL Development <me@zakarialounes.fr>
 *  @copyright 2013-2015 ZL Development
 *  @license   <http://opensource.org/licenses/afl-3.0.php>  Academic Free License (AFL 3.0)
 *  Property of ZL Development
 */

/**
 * Description of BitcoinBlockchain
 *
 * @author Zakaria Lounes <me@zakarialounes.fr>
 */
class BitcoinBlockchain extends Blockchain {

    private $context;

    public function getContext() {
        return $this->context;
    }

    public function setContext($context) {
        $this->context = $context;

        return $this;
    }

    public function __construct($context) {
        parent::__construct();
        $this->setContext($context);
    }

    public function call($limit) {
        $callback = Tools::getShopDomainSsl(true, true).'/modules/bitcoin/express_checkout/payment.php';
        $callback_params = Array(
            'id_cart' => (int)$this->context->cart->id,
            'secret' => Configuration::get(BitcoinInstall::$psBitcoinSecret),
        );
        $callback_url = $callback.'?'.http_build_query($callback_params);
        $blockchain_call = false;
        $i = 0;
        while (!$blockchain_call && $i < $limit) {
            try {
                $blockchain_call = $this->Receive->generate(Configuration::get(BitcoinInstall::$psBitcoinAddr), $callback_url);
            } catch (Exception $exc) {
                die($exc->getMessage());
            }
            $i++;
        }
        return $blockchain_call;
    }

    public static function callStatic($limit, $context) {
        $blockchain = new BitcoinBlockchain($context);

        return $blockchain->call($limit);
    }

    public function cartToBTC() {
        return $this->Rates->toBTC($this->context->cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code);
    }

    public static function cartToBTCStatic($context) {
        $blockchain = new BitcoinBlockchain($context);

        return $blockchain->cartToBTC();
    }

    public function currencyToBTC() {
        return $this->Rates->toBTC(1, $this->context->currency->iso_code);
    }

    public static function currencyToBTCStatic($context) {
        $blockchain = new BitcoinBlockchain($context);

        return $blockchain->currencyToBTC();
    }

    public function serialize($blockchain_data) {
        $filepath = _PS_MODULE_DIR_.'bitcoin/pending_payment_do_not_delete/order_id-'.$this->context->cart->id.'.txt';

        if (file_exists($filepath) && is_readable($filepath)) {
            unlink($filepath);
        }

        $bitcoin_blockchain_serialized = serialize($blockchain_data);
        file_put_contents($filepath, $bitcoin_blockchain_serialized);
    }

    public static function serializeStatic($data, $context) {
        $blockchain = new BitcoinBlockchain($context);
        $blockchain->serialize($data);
    }

    public function unserialize() {
        $filepath = _PS_MODULE_DIR_.'bitcoin/pending_payment_do_not_delete/order_id-'.$this->context->cart->id.'.txt';
        
        if (!file_exists($filepath) || !is_readable($filepath)) {
            return false;
        }

        $data = unserialize(Tools::file_get_contents($filepath));
        unlink($filepath);

        return $data;
    }

    public static function unserializeStatic($context) {
        $blockchain = new BitcoinBlockchain($context);
        return $blockchain->unserialize();
    }

}
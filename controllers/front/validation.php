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

require_once(_PS_MODULE_DIR_.'bitcoin/vendors/Blockchain/Blockchain.php');
require_once(_PS_MODULE_DIR_.'bitcoin/classes/BitcoinBlockchain.php');
require_once(_PS_MODULE_DIR_.'bitcoin/classes/BitcoinQuery.php');

/**
 * @since 1.0.0
 */
class BitcoinValidationModuleFrontController extends ModuleFrontController {

    public $ssl = true;

    public function postProcess() {
        if ($this->context->cart->id_customer == 0 ||
                $this->context->cart->id_address_delivery == 0 ||
                $this->context->cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirectLink(__PS_BASE_URI__ . 'order.php?step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'bitcoin') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            die(Tools::displayError('This payment method is not available.'));
        }
        $customer = new Customer($this->context->cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirectLink(__PS_BASE_URI__ . 'order.php?step=1');
        }
        if (Tools::getValue('confirm')) {
            // add order into bitcoin tables
            $blockchain_data = BitcoinBlockchain::unserializeStatic($this->context);

            if (!BitcoinQuery::setInitializeOrderPaymentStatic($blockchain_data)) {
                die("Bitcoin order (file) not found");
            }

            // validate order
            $customer = new Customer((int) $this->context->cart->id_customer);
            $total = $this->context->cart->getOrderTotal(true, Cart::BOTH);
            $statut = Configuration::get('_PS_OS_BITCOINSTATUT_WAITING');
            $this->module->validateOrder((int) $this->context->cart->id, $statut, $total, $this->module->displayName, null, array(), null, false, $customer->secure_key);

            Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->module->id.'&id_order='.(int)$this->module->currentOrder);
        }
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {
        $this->display_column_left = false;

        parent::initContent();

        if ($this->context->cart->getOrderTotal() <= 0) {
            Tools::redirectLink(__PS_BASE_URI__ . 'order');
        }

        $bitcoin_blockchain = new BitcoinBlockchain($this->context);
        $blockchain_call = $bitcoin_blockchain->call(Configuration::get(BitcoinInstall::$psBitcoinCallLimit));

        $bitcoin_data = array(
            'id_cart' => $this->context->cart->id,
            'secret' => Configuration::get(BitcoinInstall::$psBitcoinSecret),
            'blockchain_data' => $blockchain_call,
            'total_btc' => $bitcoin_blockchain->cartToBTC()
        );
        $bitcoin_blockchain->serialize($bitcoin_data);

        $this->context->smarty->assign(array(
            'currency_to_btc' => $bitcoin_blockchain->currencyToBTC(),
            'total_btc' => $bitcoin_data['total_btc'],
            'addr' => $blockchain_call->address,
            'total' => $this->context->cart->getOrderTotal(true, Cart::BOTH),
            'this_path' => $this->module->getPathUri(), // keep for retro compat
            'this_path_cod' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . $this->module->getPathUri()
        ));

        $this->setTemplate('validation.tpl');
    }

}

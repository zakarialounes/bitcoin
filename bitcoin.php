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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_.'bitcoin/classes/BitcoinInstall.php');

/**
 * Description of Bitcoin
 *
 * @author Zakaria Lounes <me@zakarialounes.fr>
 */
class Bitcoin extends PaymentModule {

    public function __construct() {
        $this->name = 'bitcoin';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Zakaria Lounes';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.5');
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Bitcoin');
        $this->description = $this->l('Ce module vous permet d\'accepter des paiements par bitcoin.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install() {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() || !$this->registerHook('displayHeader') ||
                !$this->registerHook('displayPayment') || !$this->registerHook('displayPaymentReturn')) {
            return false;
        }

        BitcoinInstall::install('1K3BvpG8DwL[]/û4MirbtCHF3udrdwmS', '', 25);

        return true;
    }

    public function uninstall() {
        if (!parent::uninstall() ||
                !Configuration::deleteByName(BitcoinInstall::$psBitcoinAddr) ||
                !Configuration::deleteByName(BitcoinInstall::$psBitcoinSecret) ||
                !Configuration::deleteByName(BitcoinInstall::$psBitcoinCallLimit)) {
            return false;
        }

        BitcoinInstall::uninstall();

        return true;
    }

    public function getContent() {
        if (!$this->active) {
            return;
        }

        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $inputs = array(
                BitcoinInstall::$psBitcoinAddr,
                BitcoinInstall::$psBitcoinSecret,
                BitcoinInstall::$psBitcoinCallLimit
            );
            $errors_msg = null;

            foreach ($inputs as $input) {
                $setting = (string)Tools::getValue($input);
                if (!$setting || empty($setting) || !Validate::isGenericName($setting)) {
                    $errors_msg .= $this->l($input.': Invalid Configuration value').'<br />';
                } else {
                    Configuration::updateValue($input, $setting);
                }
            }

            if (empty($errors_msg)) {
                $output = $this->displayConfirmation($this->l('Bitcoin settings updated.'));
            } else {
                $output = $this->displayError($errors_msg);
            }
        }

        return $output . $this->displayForm();
    }

    public function displayForm() {
        if (!$this->active) {
            return;
        }

        // Get default Language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form = array(
            array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Settings'),
                    ),
                    'input' => array(
                        array(
                            'type' => 'text',
                            'label' => $this->l('Bitcoin Address'),
                            'name' => BitcoinInstall::$psBitcoinAddr,
                            'required' => true,
                            'placeholder' => $this->l('Your Receiving Bitcoin Address (Where you would like the payment to be sent)')
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('Secret Key'),
                            'name' => BitcoinInstall::$psBitcoinSecret,
                            'required' => true,
                            'placeholder' => $this->l('Your secret key')
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('Blockchain API call limit'),
                            'name' => BitcoinInstall::$psBitcoinCallLimit,
                            'required' => true,
                            'placeholder' => $this->l('Maximum API call')
                        )
                    ),
                    'submit' => array(
                        'title' => $this->l('Save'),
                        'class' => 'button'
                    )
                )
            )
        );
        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value[BitcoinInstall::$psBitcoinAddr] = Configuration::get(BitcoinInstall::$psBitcoinAddr);
        $helper->fields_value[BitcoinInstall::$psBitcoinSecret] = Configuration::get(BitcoinInstall::$psBitcoinSecret);
        $helper->fields_value[BitcoinInstall::$psBitcoinCallLimit] = Configuration::get(BitcoinInstall::$psBitcoinCallLimit);

        return $helper->generateForm($fields_form);
    }

    public function hookDisplayHeader() {
        if (!$this->active) {
            return;
        }

        if (get_class($this->context->controller) === "OrderController" && $this->context->controller->step === 3) {
            $this->context->controller->addCSS($this->_path . 'css/bitcoin.css', 'all');
        } else if (get_class($this->context->controller) === "BitcoinValidationModuleFrontController") {
            $this->context->controller->addCSS($this->_path . 'css/bitcoin.css', 'all');
        }
    }

    public function hookDisplayPayment() {
        if (!$this->active) {
            return;
        }

        $this->smarty->assign(array(
            'this_path' => $this->_path, // keep for retro compatatibility
            'this_path_cod' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));

        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookDisplayPaymentReturn($params) {
        if (!$this->active) {
            return;
        }

        return $this->display(__FILE__, 'confirmation.tpl');
    }

    public function validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $message = null, $transaction = array(), $currency_special = null, $dont_touch_amount = false, $secure_key = false, Shop $shop = null) {
        if (!$this->active) {
            return;
        }

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            parent::validateOrder((int)$id_cart, (int)$id_order_state, (float)$amount_paid, $payment_method, $message, $transaction, $currency_special, $dont_touch_amount, $secure_key);
        } else {
            parent::validateOrder((int)$id_cart, (int)$id_order_state, (float)$amount_paid, $payment_method, $message, $transaction, $currency_special, $dont_touch_amount, $secure_key, $shop);
        }
    }

}

{*
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
*}

<div class="row">
    <div class="col-xs-12 col-md-6">
        <p class="payment_module">
            <a class="bitcoin" href="{$link->getModuleLink('bitcoin', 'validation', [], true)|escape:'html'}" title="{l s='Payer par Bitcoin' mod='bitcoin'}" rel="nofollow">
                {l s='Payer par Bitcoin' mod='bitcoin'}
                <br style="clear:both;" />
            </a>
        </p>
    </div>
</div>
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

<div class="payment_carrier">
    {capture name=path}
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='bitcoin'}">
            {l s='Checkout' mod='bitcoin'}
        </a>
        <span class="navigation-pipe">{$navigationPipe}</span>
        {l s='Check payment' mod='bitcoin'}
    {/capture}
    
    {if $smarty.const._PS_VERSION_ < 1.6}
        {include file="$tpl_dir./breadcrumb.tpl"}
    {/if}
    
    <h1 class="page-heading">{l s='Order summary' mod='bitcoin'}</h1>

    {assign var='current_step' value='payment'}
    {include file="$tpl_dir./order-steps.tpl"}

    <h3>{l s='Bitcoin payment' mod='bitcoin'}</h3>
    <form action="{$link->getModuleLink('bitcoin', 'validation', [], true)|escape:'html'}" method="post">
        <input type="hidden" name="confirm" value="1" />

        <div class="row">
            <div class="col-lg-8">
                <div>
                    <p class="total_price_container">
                        <b>
                            {l s='The total amount of your order is' mod='bitcoin'}
                        </b>                        
                        <span id="amount_{$currencies.0.id_currency}" class="price">
                            {convertPrice price=$total}
                            {if $use_taxes == 1}
                                {l s='(tax incl.)' mod='bitcoin'}
                            {/if}
                        </span>
                    </p>

                    <p>
                        <span>1 {$currency->name} = <span>{$currency_to_btc}</span> BTC</span> |
                        <span>Mid-market rates: <span>{$smarty.now|date_format:"%Y-%m-%d %H:%M"}</span> </span>
                    </p>

                    <p>Send exactly:</p>

                    <p>
                        <input onclick="this.select();" readonly="" value="{$total_btc}">
                    </p>

                    <p>
                        <a href="bitcoin:{$addr}?amount={$total_btc}">Pay with desktop app</a>
                    </p>
                </div>

                <div>
                    <div>
                        Trouble launching desktop app? Please use the address below.<br>
                    </div>
                    <p>
                        <input onclick="this.select();" readonly="" value="{$addr}">
                    </p>
                </div>

                <p class="cart_navigation clearfix">
                    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='bitcoin'}</a>
                    <input type="submit" value="{l s='I confirm my order' mod='bitcoin'}" class="exclusive_large"/>
                    <b>{l s='Please confirm your order by clicking \'I confirm my order\'.' mod='bitcoin'}</b>
                </p>

            </div>
                
            <div class="col-lg-4">
                <div class="pull-right">
                    <p>Scan QR code to pay with a mobile app</p>
                    <img src="https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=bitcoin:{$addr}?amount={$total_btc}" />
                </div>
            </div>
                
        </div>
    </form>
</div>
{**
* 2007-2025 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License 3.0 (AFL-3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2025 PrestaShop SA
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
* International Registered Trademark & Property of PrestaShop SA
*}
{strip}
    <div id='account-pro-button'>
        <div class="tv-header-pro tv-pro-wrapper tvcms-header-pro">
            <button class="btn-unstyle tv-pro-btn tv-pro-btn-desktop" name="User Icon" aria-label="User Icon">
                <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#202464">
                    <path
                        d="M42-120v-98.67q0-33.66 16.33-58.33 16.34-24.67 45.67-39 53-26 115.33-43.33 62.34-17.34 140-17.34 77.67 0 140 17.34Q561.67-342 614.67-316q29.33 14.33 45.66 39 16.34 24.67 16.34 58.33V-120H42Zm66.67-66.67H610v-32q0-13.66-7.5-23Q595-251 584-256q-40.67-18.67-94.83-36.33Q435-310 359.33-310q-75.66 0-129.83 17.67-54.17 17.66-94.83 36.33-11 5-18.5 14.33-7.5 9.34-7.5 23v32ZM359.33-430q-66 0-109.66-44.33-43.67-44.34-43.67-109h-10q-8.33 0-14.17-5.84Q176-595 176-603.33q0-8.34 5.83-14.17 5.84-5.83 14.17-5.83h10q0-41.67 20.67-75 20.66-33.34 54-53.67v38.67q0 7 4.83 11.83t11.83 4.83q7.67 0 12.17-4.83t4.5-11.83V-766q8.33-2.33 21-3.83t25-1.5q12.33 0 25 1.5t21 3.83v52.67q0 7 4.5 11.83t12.17 4.83q7 0 11.83-4.83t4.83-11.83V-752q33.34 20.33 53.34 53.67 20 33.33 20 75h10q8.33 0 14.16 5.83 5.84 5.83 5.84 14.17 0 8.33-5.84 14.16-5.83 5.84-14.16 5.84h-10q0 64.66-43.67 109Q425.33-430 359.33-430Zm0-66.67q39 0 62.84-24.5Q446-545.67 446-583.33H272.67q0 37.66 23.83 62.16t62.83 24.5Zm301.34 136L657.33-390q-6.66-3.33-13.5-7.5-6.83-4.17-12.5-9.17L604.67-394l-21.34-33.33L608-446.67q-1.33-3.66-1.33-7v-14q0-3.33 1.33-7L583.33-494l21.34-33.33 26.66 12.66q6-4.66 12.67-9 6.67-4.33 13.33-7.66l3.34-29.34h40l3.33 29.34q6.67 3.33 13.33 7.66 6.67 4.34 12.67 9l26.67-12.66L778-494l-24.67 19.33q1.34 3.67 1.34 7v14q0 3.34-1.34 7L778-427.33 756.67-394 730-406.67q-5.67 5-12.5 9.17T704-390l-3.33 29.33h-40Zm20-64.66q14.66 0 25-10.34Q716-446 716-460.67q0-14.66-10.33-25-10.34-10.33-25-10.33-14.67 0-25 10.33-10.34 10.34-10.34 25 0 14.67 10.34 25 10.33 10.34 25 10.34ZM763.33-572l-8.66-37.33Q745-613 735.5-619.17q-9.5-6.16-16.17-13.5L676-617.33l-22.67-39.34L688-685.33q-2-5-3-10T684-706q0-5.67 1-10.67t3-10l-34.67-28.66L676-794.67l43.33 15.34q6.67-7.34 16.17-13.84 9.5-6.5 19.17-9.5l8.66-37.33h44l8.67 37.33q9.67 3 19.17 9.5 9.5 6.5 16.16 13.84l43.34-15.34 22.66 39.34-34.66 28.66q2 5 3 10t1 10.67q0 5.67-1 10.67t-3 10l34.66 28.66-22.66 39.34-43.34-15.34q-6.66 7.34-16.16 13.5-9.5 6.17-19.17 9.84L807.33-572h-44Zm22-78.67q23.67 0 39.5-15.83 15.84-15.83 15.84-39.5t-15.84-39.5q-15.83-15.83-39.5-15.83-23.66 0-39.5 15.83Q730-729.67 730-706t15.83 39.5q15.84 15.83 39.5 15.83Zm-426 464Z" />
                </svg>
                <span>Espace pro</span>
            </button>
            <ul class="dropdown-menu tv-pro-dropdown tv-dropdown">
                <li class="pro-icon">
                    <a href="{$urls.pages.register|cat:'?pro=1'}" class="tvaccount-pro">
                        <i class='material-icons'>&#xe7ff;</i>
                        S'inscrire
                    </a>
                </li>
            </ul>
        </div>
    </div>
{/strip}
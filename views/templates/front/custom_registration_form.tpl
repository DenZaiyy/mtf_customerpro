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
{if $isPro}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Make company field required (from default PrestaShop form)
            var companyInput = document.querySelector('input[name="company"]');
            var companyLabel = document.querySelector('label[for="field-company"]');
            if (companyInput) {
                companyInput.required = true;
                companyInput.setAttribute('required', 'required');
            }
            if (companyLabel) {
                companyLabel.textContent = "Raison Sociale"
                companyLabel.classList.add('required');
            }

            // Add form validation
            var form = document.querySelector('form#customer-form');
            if (form) {
                form.addEventListener('submit', function(event) {
                    // Check company field
                    if (!companyInput || !companyInput.value.trim()) {
                        event.preventDefault();
                        alert('Le champ Raison Sociale est obligatoire pour un compte professionnel.');
                        if (companyInput) companyInput.focus();
                        return;
                    }

                    // Check address
                    var addressInput = document.querySelector('input[name="address"]');
                    if (!addressInput || !addressInput.value.trim()) {
                        event.preventDefault();
                        alert('Le champ Adresse est obligatoire.');
                        if (addressInput) addressInput.focus();
                        return;
                    }

                    // Check zipcode
                    var zipcodeInput = document.querySelector('input[name="zipcode"]');
                    if (!zipcodeInput || !zipcodeInput.value.trim()) {
                        event.preventDefault();
                        alert('Le champ Code Postal est obligatoire.');
                        if (zipcodeInput) zipcodeInput.focus();
                        return;
                    }

                    // Check city
                    var cityInput = document.querySelector('input[name="city"]');
                    if (!cityInput || !cityInput.value.trim()) {
                        event.preventDefault();
                        alert('Le champ Ville est obligatoire.');
                        if (cityInput) cityInput.focus();
                        return;
                    }

                    // Check country
                    var countryInput = document.querySelector('select[name="country"]');
                    if (!countryInput || !countryInput.value) {
                        event.preventDefault();
                        alert('Le champ Pays est obligatoire.');
                        if (countryInput) countryInput.focus();
                        return;
                    }

                    // Check KBIS file
                    var kbisInput = document.querySelector('input[name="kbis_file"]');
                    if (!kbisInput || !kbisInput.files || !kbisInput.files.length) {
                        event.preventDefault();
                        alert('Le fichier KBIS est obligatoire.');
                        if (kbisInput) kbisInput.focus();
                        return;
                    }
                });
            }
        });
    </script>
    <div class="form-group row">
        <div class="col-md-6 js-input-column">
            <label class="form-control-label required" for="{$addressField.name}">{$addressField.label}</label>
            <input class="form-control" type="text" name="{$addressField.name}" id="{$addressField.name}" required />
        </div>
        <div class="col-md-6 js-input-column">
            <label class="form-control-label required" for="{$zipcodeField.name}">{$zipcodeField.label}</label>
            <input class="form-control" type="text" name="{$zipcodeField.name}" id="{$zipcodeField.name}" required />
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-6 js-input-column">
            <label class="form-control-label required" for="{$cityField.name}">{$cityField.label}</label>
            <input class="form-control" type="text" name="{$cityField.name}" id="{$cityField.name}" required />
        </div>
        <div class="col-md-6 js-input-column">
            <label class="form-control-label required" for="{$countryField.name}">{$countryField.label}</label>
            <select class="form-control" name="{$countryField.name}" id="{$countryField.name}">
                <option value="">Sélectionner un pays</option>
                {foreach from=$countryField.options item=country}
                    <option value="{$country.id_country}">{$country.name}</option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-12 js-input-column">
            <label class="form-control-label required" for="{$kbisField.name}">{$kbisField.label}</label>
            <input class="form-control" type="file" name="{$kbisField.name}" id="{$kbisField.name}" />
        </div>
    </div>
{/if}
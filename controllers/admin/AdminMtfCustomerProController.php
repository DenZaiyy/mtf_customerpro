<?php

/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @author    Your Name <your.email@domain.com>
 * @copyright 2007-2025 Your Company
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMtfCustomerProController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        $this->meta_title = $this->l('Customer Pro Settings');
    }

    /**
     * Initialize the content
     */
    public function initContent()
    {
        $this->content = $this->renderForm();

        parent::initContent();
    }

    /**
     * Render the configuration form
     */
    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Registration Fields Configuration'),
                    'icon' => 'icon-user'
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Company Number Field'),
                        'name' => 'MTF_CUSTOMERPRO_COMPANY_NAME_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                        'desc' => $this->l('Display the company number field in the registration form')
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Required Company Number'),
                        'name' => 'MTF_CUSTOMERPRO_COMPANY_NAME_REQUIRED',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                        'desc' => $this->l('Make the company number field required')
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable VAT Number Field'),
                        'name' => 'MTF_CUSTOMERPRO_SIRET_NUMBER_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                        'desc' => $this->l('Display the VAT number field in the registration form')
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Required VAT Number'),
                        'name' => 'MTF_CUSTOMERPRO_SIRET_NUMBER_REQUIRED',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                        'desc' => $this->l('Make the VAT number field required')
                    ],
                    // Add more custom fields here as needed
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMtfCustomerProConfig';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminMtfCustomerPro', false);
        $helper->token = Tools::getAdminTokenLite('AdminMtfCustomerPro');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * Get config form values
     */
    protected function getConfigFormValues()
    {
        return [
            'MTF_CUSTOMERPRO_COMPANY_NAME_ENABLED' => Configuration::get('MTF_CUSTOMERPRO_COMPANY_NAME_ENABLED', 1),
            'MTF_CUSTOMERPRO_COMPANY_NAME_REQUIRED' => Configuration::get('MTF_CUSTOMERPRO_COMPANY_NAME_REQUIRED', 0),
            'MTF_CUSTOMERPRO_SIRET_NUMBER_ENABLED' => Configuration::get('MTF_CUSTOMERPRO_SIRET_NUMBER_ENABLED', 1),
            'MTF_CUSTOMERPRO_SIRET_NUMBER_REQUIRED' => Configuration::get('MTF_CUSTOMERPRO_SIRET_NUMBER_REQUIRED', 0),
            // Add more fields as needed
        ];
    }

    /**
     * Process the form submission
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitMtfCustomerProConfig')) {
            // Save configuration values
            Configuration::updateValue('MTF_CUSTOMERPRO_COMPANY_NAME_ENABLED', (int)Tools::getValue('MTF_CUSTOMERPRO_COMPANY_NAME_ENABLED'));
            Configuration::updateValue('MTF_CUSTOMERPRO_COMPANY_NAME_REQUIRED', (int)Tools::getValue('MTF_CUSTOMERPRO_COMPANY_NAME_REQUIRED'));
            Configuration::updateValue('MTF_CUSTOMERPRO_SIRET_NUMBER_ENABLED', (int)Tools::getValue('MTF_CUSTOMERPRO_SIRET_NUMBER_ENABLED'));
            Configuration::updateValue('MTF_CUSTOMERPRO_SIRET_NUMBER_REQUIRED', (int)Tools::getValue('MTF_CUSTOMERPRO_SIRET_NUMBER_REQUIRED'));
            // Add more fields as needed

            $this->confirmations[] = $this->l('Settings updated successfully.');

            // Instead of using _clearCache, use a different approach
            // Option 1: Call the module's public cache clearance methods if available
            if (method_exists($this->module, 'clearCache')) {
                $this->module->clearCache();
            }

            // Option 2: Use Cache::clean if you know the pattern
            elseif (class_exists('Cache')) {
                Cache::clean('mtf_customerpro_*');
            }
        }

        parent::postProcess();
    }
}

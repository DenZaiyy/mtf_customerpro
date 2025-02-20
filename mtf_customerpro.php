<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Mtf_CustomerPro extends Module
{
    public function __construct()
    {
        $this->name = 'mtf_customerpro';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'MTFibertech';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_
        ];

        parent::__construct();

        $this->displayName = $this->trans('MTF Custom Pro Account', [], 'Modules.Mtfcustomerpro.Admin');
        $this->description = $this->trans("Ajoute les champs necessaires pour l'inscription d'un compte professionnel", [], 'Modules.Mtfcustomerpro.Admin');
        $this->js_path = $this->_path . 'views/js/';
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayCustomerAccountForm') &&
            $this->registerHook('actionCustomerAccountAdd') &&
            $this->registerHook('displayCustomerAccountProMenu') &&
            $this->registerHook('actionObjectCustomerUpdateBefore') &&
            $this->registerHook('actionObjectCustomerUpdateAfter') &&
            $this->registerHook('actionEmailSendBefore') &&
            Configuration::updateValue('MTF_PRO_GROUP_ID', $this->getProGroupId());

        // Copy email templates
        if (!$this->installEmailTemplates()) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    private function getProGroupId()
    {
        $groupId = (int)Db::getInstance()->getValue(
            '
            SELECT id_group FROM ' . _DB_PREFIX_ . 'group_lang 
            WHERE name = "Client PRO" AND id_lang = ' . (int)Configuration::get('PS_LANG_DEFAULT')
        );

        return $groupId ?: false;
    }

    public function hookDisplayCustomerAccountProMenu()
    {
        return $this->display(__FILE__, 'views/templates/front/menu.tpl');
    }

    public function hookDisplayCustomerAccountForm($params)
    {
        $isPro = Tools::getValue('pro') == 1; // Check if 'pro=1' is in the URL

        $countries = Country::getCountries($this->context->language->id); // Fetch countries list

        if ($isPro) {
            Media::addJsDef([
                'isPro' => true
            ]);
            $this->context->controller->addJS($this->_path . 'views/js/validation.js');
        }

        $this->context->smarty->assign([
            'isPro' => $isPro, // Pass this value to the template
            'companyField' => [
                'name' => 'company',
                'label' => $this->trans('Raison Sociale', [], 'Modules.CustomerPro.Shop'),
                "required" => $isPro
            ],
            'addressField' => [
                'name' => 'address',
                'label' => $this->trans('Adresse', [], 'Modules.CustomerPro.Shop')
            ],
            'zipcodeField' => [
                'name' => 'zipcode',
                'label' => $this->trans('Code Postal', [], 'Modules.CustomerPro.Shop')
            ],
            'cityField' => [
                'name' => 'city',
                'label' => $this->trans('Ville', [], 'Modules.CustomerPro.Shop')
            ],
            'countryField' => [
                'name' => 'country',
                'label' => $this->trans('Pays', [], 'Modules.CustomerPro.Shop'),
                'options' => $countries // Pass countries list
            ],
            'kbisField' => [
                'name' => 'kbis_file',
                'label' => $this->trans('Extrait Kbis (PDF, JPG, PNG)', [], 'Modules.CustomerPro.Shop')
            ]
        ]);

        return $this->display(__FILE__, 'views/templates/front/custom_registration_form.tpl');
    }

    public function hookActionCustomerAccountAdd($params)
    {
        try {
            $isPro = Tools::getValue('pro') == 1;

            if ($isPro) {
                // Validate required fields
                try {
                    $requiredFields = [
                        'company' => 'Raison Sociale',
                        'address' => 'Adresse',
                        'zipcode' => 'Code Postal',
                        'city' => 'Ville',
                        'country' => 'Pays'
                    ];

                    $errors = [];
                    foreach ($requiredFields as $field => $label) {
                        if (empty(Tools::getValue($field))) {
                            $errors[] = sprintf('Le champ %s est obligatoire.', $label);
                        }
                    }

                    // Validate KBIS file
                    if (!isset($_FILES['kbis_file']) || $_FILES['kbis_file']['error'] !== 0) {
                        $errors[] = 'Le fichier KBIS est obligatoire.';
                    }
                } catch (Exception $e) {
                    error_log('Error in field validation: ' . $e->getMessage());
                    throw $e;
                }

                // If we have any errors, delete the customer and throw exception
                if (!empty($errors)) {
                    $customer = $params['newCustomer'];
                    if ($customer->id) {
                        $customer->delete();
                    }
                    throw new PrestaShopException(implode("\n", $errors));
                }

                // Update customer group
                try {
                    $customer = $params['newCustomer'];
                    $groupId = (int)Configuration::get('PS_PRO_GROUP_ID');

                    // Set pro group and deactivate account
                    $customer->id_default_group = $groupId;
                    $customer->cleanGroups();
                    $customer->addGroups([$groupId]);
                    $customer->active = 0;

                    if (!$customer->update()) {
                        throw new PrestaShopException("Erreur lors de la mise à jour du compte client.");
                    }
                } catch (Exception $e) {
                    error_log('Error updating customer group: ' . $e->getMessage());
                    throw $e;
                }

                // Create address
                try {
                    $address = new Address();
                    $address->id_customer = (int)$customer->id;
                    $address->firstname = $customer->firstname;
                    $address->lastname = $customer->lastname;
                    $address->company = Tools::getValue('company');
                    $address->address1 = Tools::getValue('address');
                    $address->postcode = Tools::getValue('zipcode');
                    $address->city = Tools::getValue('city');
                    $address->id_country = (int)Tools::getValue('country');
                    $address->alias = 'Adresse professionnelle';

                    if (!$address->save()) {
                        throw new PrestaShopException('Erreur lors de l\'enregistrement de l\'adresse.');
                    }
                } catch (Exception $e) {
                    error_log('Error saving address: ' . $e->getMessage());
                    throw $e;
                }

                // Handle KBIS file
                try {
                    if (isset($_FILES['kbis_file']) && $_FILES['kbis_file']['error'] == 0) {
                        // Check file size
                        $maxFileSize = 5 * 1024 * 1024;
                        if ($_FILES['kbis_file']['size'] > $maxFileSize) {
                            throw new PrestaShopException('Le fichier KBIS est trop volumineux. Taille maximum : 5MB.');
                        }

                        // Validate extension
                        $fileName = $_FILES['kbis_file']['name'];
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

                        if (!in_array($fileExtension, $allowedExtensions)) {
                            throw new PrestaShopException('Format de fichier non autorisé. Formats acceptés : PDF, JPG, PNG');
                        }

                        // Validate MIME type
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->file($_FILES['kbis_file']['tmp_name']);
                        $allowedMimeTypes = [
                            'application/pdf',
                            'image/jpeg',
                            'image/png'
                        ];

                        if (!in_array($mimeType, $allowedMimeTypes)) {
                            throw new PrestaShopException('Type de fichier non autorisé.');
                        }

                        // Create directory and handle file
                        $uploadDir = _PS_UPLOAD_DIR_ . '/kbis/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $safeFileName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $fileName);
                        $filename = time() . '_' . $safeFileName;
                        $kbisFilePath = $uploadDir . $filename;

                        if (!is_uploaded_file($_FILES['kbis_file']['tmp_name'])) {
                            throw new PrestaShopException('Fichier invalide.');
                        }

                        if (!move_uploaded_file($_FILES['kbis_file']['tmp_name'], $kbisFilePath)) {
                            throw new PrestaShopException('Erreur lors du téléchargement du fichier KBIS.');
                        }

                        $customer->kbis_file = $filename;
                        $customer->update();
                    }
                } catch (Exception $e) {
                    error_log('Error handling KBIS file: ' . $e->getMessage());
                    throw $e;
                }

                // Send emails
                try {
                    $this->sendProUserEmailToAdmin($customer);
                    $this->sendProUserEmailToCustomer($customer);
                } catch (Exception $e) {
                    error_log('Error sending emails: ' . $e->getMessage());
                    // Don't throw here, continue process even if emails fail
                }

                // Add success notification and redirect
                try {
                    $this->context->controller->success[] = $this->l('Votre compte professionnel a été créé avec succès. Il est actuellement en attente de validation. Vous recevrez un email dès qu\'il sera activé.');
                    exit(Tools::redirect($this->context->link->getPageLink('index')));
                } catch (Exception $e) {
                    error_log('Error in redirect: ' . $e->getMessage());
                    throw $e;
                }
            }
        } catch (Exception $e) {
            error_log('Main error in hookActionCustomerAccountAdd: ' . $e->getMessage());

            // Delete customer if exists
            if (isset($customer) && $customer->id) {
                try {
                    $customer->delete();
                } catch (Exception $deleteError) {
                    error_log('Error deleting customer: ' . $deleteError->getMessage());
                }
            }

            $this->context->controller->errors[] = $e->getMessage();
            exit(Tools::redirect($this->context->link->getPageLink('index')));
        }
    }

    public function hookActionEmailSendBefore($params)
    {
        try {
            $isPro = Tools::getValue('pro') == 1;

            // Check if this is a customer account creation email
            if (
                $isPro && isset($params['template']) &&
                in_array($params['template'], ['account', 'account_creation'])
            ) {

                PrestaShopLogger::addLog('Blocking default account creation email for pro customer', 1);
                return false; // This prevents the email from being sent
            }

            return true;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Error in hookActionEmailSendBefore: ' . $e->getMessage(), 3);
            return true; // Let the email send in case of error
        }
    }

    public function hookActionObjectCustomerUpdateBefore($params)
    {
        try {
            if (isset($params['object']) && $params['object'] instanceof Customer) {
                // Get current status directly from database
                $oldStatus = (int)Db::getInstance()->getValue('
                    SELECT active 
                    FROM `'._DB_PREFIX_.'customer` 
                    WHERE id_customer = '.(int)$params['object']->id
                );
                // Store in context for later use
                Context::getContext()->oldActiveStatus = $oldStatus;

                //PrestaShopLogger::addLog('Stored old active status from DB: ' . $oldStatus, 1);
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Error in hookActionObjectUpdateBefore: ' . $e->getMessage(), 3);
            return false;
        }
    }

    public function hookActionObjectCustomerUpdateAfter($params)
    {
        try {
            //PrestaShopLogger::addLog('Starting hookActionObjectCustomerUpdateAfter', 1);
            if (isset($params['object']) && $params['object'] instanceof Customer) { 
                $customer = $params['object'];
                $groupId = (int)Configuration::get('PS_PRO_GROUP_ID');
                $newActiveStatus = (int)$customer->active;
                $oldActiveStatus = (int)Context::getContext()->oldActiveStatus;
                $loginUrl = Context::getContext()->link->getPageLink('authentication', true);

                // Check if this is a pro customer
                if (in_array($groupId, $customer->getGroups())) {
                    //PrestaShopLogger::addLog('Pro customer detected', 1);

                    // Check if customer is active
                    if ($oldActiveStatus === 0 && $newActiveStatus === 1) {
                        //PrestaShopLogger::addLog('Customer activated by admin, sending email', 1);

                        $templateVars = [
                            '{firstname}' => $customer->firstname,
                            '{lastname}' => $customer->lastname,
                            '{email}' => $customer->email,
                            '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
                            '{shop_url}' => $loginUrl,
                            '{shop_logo}' => _PS_IMG_DIR_ . Configuration::get('PS_LOGO')
                        ];

                        $mailResult = Mail::Send(
                            (int)Configuration::get('PS_LANG_DEFAULT'),
                            'pro_account_activated',
                            $this->l('Votre compte professionnel a été activé'),
                            $templateVars,
                            $customer->email,
                            $customer->firstname . ' ' . $customer->lastname,
                            null,
                            null,
                            null,
                            null,
                            _PS_MODULE_DIR_ . $this->name . '/mails'
                        );

                        PrestaShopLogger::addLog('Activation mail send result: ' . ($mailResult ? 'success' : 'failed'), 1);
                    }

                    unset(Context::getContext()->oldActiveStatus);
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Error in hookActionObjectUpdateAfter: ' . $e->getMessage(), 3);
            return false;
        }
        return true;
    }

    private function sendProUserEmailToAdmin($customer)
    {
        try {
            // Get the customer's address
            $addressId = Address::getFirstCustomerAddressId($customer->id);
            $address = new Address($addressId);
            $country = new Country((int)$address->id_country, (int)Configuration::get('PS_LANG_DEFAULT'));

            // Get shop's domain
            $shopDomain = Tools::getShopDomainSsl(true);
            $backofficeUrl = $shopDomain . '/admin_elecie/index.php?controller=AdminCustomers&id_customer=' . (int)$customer->id . '&viewcustomer';

            $templateVars = [
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{email}' => $customer->email,
                '{company}' => $address->company,
                '{address1}' => $address->address1,
                '{postcode}' => $address->postcode,
                '{city}' => $address->city,
                '{country}' => $country->name,
                '{backoffice_url}' => $backofficeUrl,
                '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
                '{shop_url}' => Context::getContext()->link->getBaseLink(),
                '{shop_logo}' => _PS_IMG_DIR_ . Configuration::get('PS_LOGO')
            ];

            // Get admin email
            $adminEmail = "k.grischko@mtfibertech.fr";

            // Prepare KBIS file attachment
            $fileAttachment = null;
            if (!empty($customer->kbis_file)) {
                $kbisPath = _PS_UPLOAD_DIR_ . '/kbis/' . $customer->kbis_file;
                PrestaShopLogger::addLog('Looking for KBIS file at: ' . $kbisPath, 1);

                if (file_exists($kbisPath)) {
                    $fileAttachment = [
                        'content' => file_get_contents($kbisPath),
                        'name' => basename($customer->kbis_file),
                        'mime' => mime_content_type($kbisPath)
                    ];
                    PrestaShopLogger::addLog('KBIS file found and attached', 1);
                } else {
                    PrestaShopLogger::addLog('KBIS file not found at path: ' . $kbisPath, 3);
                }
            }

            return Mail::Send(
                (int)Configuration::get('PS_LANG_DEFAULT'),
                'new_pro_user_admin',
                $this->l('Nouveau compte professionnel'),
                $templateVars,
                $adminEmail,
                null,
                null,
                null,
                $fileAttachment,
                null,
                _PS_MODULE_DIR_ . $this->name . '/mails'
            );
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Error in sendProUserEmailToAdmin: ' . $e->getMessage(), 3);
            return false;
        }
    }

    private function sendProUserEmailToCustomer($customer)
    {
        try {
            $templateVars = [
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{email}' => $customer->email,
                '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
                '{shop_url}' => Context::getContext()->link->getBaseLink(),
                '{shop_logo}' => _PS_IMG_DIR_ . Configuration::get('PS_LOGO')
            ];

            return Mail::Send(
                (int)Configuration::get('PS_LANG_DEFAULT'),
                'new_pro_user_customer',
                $this->l('Confirmation de votre inscription'),
                $templateVars,
                $customer->email,
                $customer->firstname . ' ' . $customer->lastname,
                null,
                null,
                null,
                null,
                _PS_MODULE_DIR_ . $this->name . '/mails'
            );
        } catch (Exception $e) {
            error_log('Error sending customer notification: ' . $e->getMessage());
            return false;
        }
    }

    private function installEmailTemplates()
    {
        // Get module path
        $modulePath = _PS_MODULE_DIR_ . $this->name;

        // Create mails directory if it doesn't exist
        if (!is_dir($modulePath . '/mails')) {
            mkdir($modulePath . '/mails', 0777, true);
        }

        // Copy email templates for each language
        foreach (Language::getLanguages(true, Context::getContext()->shop->id) as $lang) {
            $isoCode = strtolower($lang['iso_code']);

            // Create language directory if it doesn't exist
            if (!is_dir($modulePath . '/mails/' . $isoCode)) {
                mkdir($modulePath . '/mails/' . $isoCode, 0777, true);
            }

            // Copy admin template files
            if (!file_exists($modulePath . '/mails/' . $isoCode . '/new_pro_user_admin.html')) {
                copy(
                    $modulePath . '/mails/fr/new_pro_user_admin.html',
                    $modulePath . '/mails/' . $isoCode . '/new_pro_user_admin.html'
                );
            }
            if (!file_exists($modulePath . '/mails/' . $isoCode . '/new_pro_user_admin.txt')) {
                copy(
                    $modulePath . '/mails/fr/new_pro_user_admin.txt',
                    $modulePath . '/mails/' . $isoCode . '/new_pro_user_admin.txt'
                );
            }

            // Copy customer template files
            if (!file_exists($modulePath . '/mails/' . $isoCode . '/new_pro_user_customer.html')) {
                copy(
                    $modulePath . '/mails/fr/new_pro_user_customer.html',
                    $modulePath . '/mails/' . $isoCode . '/new_pro_user_customer.html'
                );
            }
            if (!file_exists($modulePath . '/mails/' . $isoCode . '/new_pro_user_customer.txt')) {
                copy(
                    $modulePath . '/mails/fr/new_pro_user_customer.txt',
                    $modulePath . '/mails/' . $isoCode . '/new_pro_user_customer.txt'
                );
            }

            // Copy activation email template
            if (!file_exists($modulePath . '/mails/' . $isoCode . '/pro_account_activated.html')) {
                copy(
                    $modulePath . '/mails/fr/pro_account_activated.html',
                    $modulePath . '/mails/' . $isoCode . '/pro_account_activated.html'
                );
            }
            if (!file_exists($modulePath . '/mails/' . $isoCode . '/pro_account_activated.txt')) {
                copy(
                    $modulePath . '/mails/fr/pro_account_activated.txt',
                    $modulePath . '/mails/' . $isoCode . '/pro_account_activated.txt'
                );
            }
        }

        return true;
    }
}

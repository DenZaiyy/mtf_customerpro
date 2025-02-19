# PrestaShop Module – MTF CustomerPro  

This is a PrestaShop module (created for v8.2) that adds custom fields to the registration form when the user is a professional.  

## Directory Structure  

```
mtf_customerpro/
├── LICENSE
├── mails/ -> Email templates
│   └── fr/
│       ├── new_pro_user_admin.html
│       ├── new_pro_user_admin.txt
│       ├── new_pro_user_customer.html
│       ├── new_pro_user_customer.txt
│       ├── pro_account_activated.html
│       └── pro_account_activated.txt
├── mtf_customerpro.php -> Main module file
└── views/ -> Front-end assets
    ├── js/
    │   └── validation.js -> Form validation script
    └── templates/
        └── front/
            ├── custom_registration_form.tpl -> Custom fields template
            └── menu.tpl -> Menu to display the professional account registration link
```

## Features  

- Allows users to register as either a **basic customer** or a **professional**.  
- Professional accounts are **inactive by default** upon registration.  
- The administrator receives an email notification when a professional registers.  
- The user must upload a **K-bis document** (business registration proof) during registration.  
- The admin can review the document and **activate the account manually** from the back office.  
- Once activated, the user receives a confirmation email notifying them that they can now log in and access the store.  

## Installation  

1. Upload the module to your PrestaShop installation.  
2. Install and activate it from the **Modules Manager**.  
3. Configure the module settings if needed.  

## Notes  

- The K-bis file is sent to the admin via email upon registration.  
- Professional accounts do not have access to special promotions until activated.  


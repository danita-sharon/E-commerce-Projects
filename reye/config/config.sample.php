<?php
// =========================================================
// REYE - Private settings
// This file is listed in .gitignore, so it NEVER goes to GitHub.
// =========================================================

// ---- Database ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'reye_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP on Mac: empty by default

// ---- Site ----
define('APP_ENV', 'local');     // change to 'production' on the Ashesi server
define('BASE_URL', '/E-commerce-Projects/reye');
define('WHATSAPP_NUMBER', '233536137600');   // 0536137600 in international format

// ---- Paystack (test keys from dashboard.paystack.com -> Settings -> API Keys) ----
define('PAYSTACK_PUBLIC_KEY', 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('PAYSTACK_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
<?php
// File: includes/inc-functions.php

/**
 * Escapes HTML special characters in a string.
 *
 * @param string $string The string to escape.
 * @return string The escaped string.
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generates a CSRF token and stores it in the session.
 *
 * @return string The generated CSRF token.
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generates the sitemap.xml and robots.txt files for the website.
 * 
 * @param string $baseUrl       The base URL of the site (e.g., https://example.com/). 
 *                              Defaults to the full URL from the current server environment if not provided.
 * @param string $documentRoot  The document root of the site (e.g., /var/www/html/). 
 *                              Defaults to the server's document root if not provided.
 */
function generateSitemapAndRobots($baseUrl = '', $documentRoot = '') {
    if (empty($baseUrl)) {
        $baseUrl = fullUrl();
    }
    if (empty($documentRoot)) {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
    }
    if (substr($documentRoot, -1) !== '/') {
        $documentRoot .= '/';
    }
    if (!is_writable($documentRoot)) {
        throw new Exception("The directory $documentRoot is not writable. Please check permissions.");
    }

    $phpFiles = glob($documentRoot . '*.php');
    $skipPages = [
        'index','login','logout','signup','reset-password',
        'forgot-password','verify-email','dashboard','settings',
        'reports','users'
    ];

    $sitemap  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
    // Homepage
    $sitemap .= "    <url>\n"
             . "        <loc>" . rtrim($baseUrl, '/') . "/</loc>\n"
             . "        <changefreq>daily</changefreq>\n"
             . "        <priority>1.0</priority>\n"
             . "    </url>\n";

    foreach ($phpFiles as $file) {
        $filePath  = str_replace($documentRoot, '', $file);
        $cleanPath = preg_replace('/\.php$/', '', $filePath);
        if (in_array($cleanPath, $skipPages)) continue;
        if (preg_match('/^(admin-|ajax-|save-|inc-)/', $cleanPath)) continue;

        $sitemap .= "    <url>\n"
                 . "        <loc>" . rtrim($baseUrl, '/') . "/" . ltrim($cleanPath, '/') . "/</loc>\n"
                 . "        <changefreq>daily</changefreq>\n"
                 . "        <priority>0.8</priority>\n"
                 . "    </url>\n";
    }
    $sitemap .= "</urlset>\n";

    $sitemapFile = $documentRoot . 'sitemap.xml';
    if (file_put_contents($sitemapFile, $sitemap) === false) {
        throw new Exception("Failed to write sitemap.xml to $sitemapFile.");
    }

    $robots  = "User-agent: *\n";
    $robots .= "Sitemap: " . rtrim($baseUrl, '/') . "/sitemap.xml\n";
    $robotsFile = $documentRoot . 'robots.txt';
    if (file_put_contents($robotsFile, $robots) === false) {
        throw new Exception("Failed to write robots.txt to $robotsFile.");
    }
}

/**
 * Generates the full base URL of the site.
 *
 * @return string The base URL (e.g., "https://example.com/").
 */
function fullUrl() {
    try {
        $protocol = 'http';
        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        ) {
            $protocol = 'https';
        }
        $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        return sprintf("%s://%s%s", $protocol, $host, $scriptDir);
    } catch (Exception $e) {
        error_log('Error generating full URL: ' . $e->getMessage());
        return '/';
    }
}

/**
 * Retrieves the currently logged-in user's data.
 *
 * @return array|null An associative array of user data or null if not logged in.
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        $db   = DB::getInstance();
        $user = $db->selectOne(
            "SELECT * FROM users WHERE id = :id",
            [':id' => $_SESSION['user_id']]
        );
        return $user;
    }
    return null;
}

/**
 * Retrieves metadata for the specified page.
 *
 * @param string $page The current page's URI (e.g., "/index.php").
 * @return array An associative array containing 'title' and 'description'.
 */
function getPageMetaData($page) {
    $settings            = getSiteSettings();
    $site_name           = $settings['site_name'] ?? 'Your Site Name';
    $baseTitle           = $site_name . " | ";
    $home_meta_title     = $settings['home_meta_title'] ?? '';
    $home_meta_desc      = $settings['home_meta_description'] ?? '';
    $mapping = [
        "/about.php"            => ["title"=>"About Us","description"=>"Learn more about what we do at {$site_name}."],
        "/contact.php"          => ["title"=>"Contact Us","description"=>"Get in touch with the team at {$site_name}."],
        "/dashboard.php"        => ["title"=>"Dashboard","description"=>"Manage your account and access exclusive features on {$site_name}."],
        "/features.php"         => ["title"=>"Features","description"=>"Explore the features and benefits of using {$site_name}."],
        "/forgot-password.php"  => ["title"=>"Forgot Password","description"=>"Recover access to your {$site_name} account."],
        "/index.php"            => ["title"=>(!empty($home_meta_title)?$home_meta_title:"Welcome to {$site_name}"),"description"=>(!empty($home_meta_desc)?$home_meta_desc:"Find the best services with {$site_name}.")],
        "/login.php"            => ["title"=>"Login","description"=>"Access your {$site_name} account."],
        "/privacy-policy.php"   => ["title"=>"Privacy Policy","description"=>"Learn about the privacy policy for {$site_name}."],
        "/signup.php"           => ["title"=>"Sign Up","description"=>"Create your {$site_name} account."],
        "/terms-of-service.php" => ["title"=>"Terms of Service","description"=>"Read the terms of using {$site_name} and your responsibilities."]
    ];
    $default = ["title"=>"Home","description"=>"Welcome to {$site_name} – your go-to solution for all your needs."];
    $meta    = $mapping[$page] ?? $default;
    return [
        "title"       => $baseTitle . $meta['title'],
        "description" => $meta['description']
    ];
}

/**
 * Fetches site settings from the database or returns default settings.
 *
 * @return array An associative array of site settings.
 */
function getSiteSettings() {
    $db       = DB::getInstance();
    $settings = $db->selectOne("SELECT * FROM settings WHERE id = 1") ?? [];
    $defaults = [
        'site_name'        => 'Your Site Name',
        'site_description' => 'Your site description.',
        'contact_email'    => 'no-reply@example.com',
    ];
    return array_merge($defaults, $settings);
}

/**
 * Determines if a navigation link is active.
 *
 * @param string $page The target page filename (e.g., "about.php").
 * @return string The CSS classes to apply.
 */
function isActive($page) {
    $current = basename($_SERVER['PHP_SELF']);
    if ($current === $page) {
        // active: indigo box with white text
        return 'bg-indigo-600 text-white px-3 py-2 text-sm font-medium uppercase rounded-md';
    }
    // inactive: gray text, light hover background
    return 'text-gray-700 hover:bg-gray-100 px-3 py-2 text-sm font-medium uppercase rounded-md transition';
}

/**
 * Checks if the currently logged-in user is an admin.
 *
 * @return bool True if the user is an admin, false otherwise.
 */
function isAdmin() {
    if (isLoggedIn()) {
        $u = getCurrentUser();
        return isset($u['role']) && $u['role'] === 'admin';
    }
    return false;
}

/**
 * Checks if a user is logged in.
 *
 * @return bool True if logged in, false otherwise.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirects to a specified URL.
 *
 * @param string $url The URL to redirect to.
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    }
    error_log("Cannot redirect to $url; headers already sent.");
}

/**
 * Sends a contact form email.
 *
 * @param string $to   The recipient email address.
 * @param array  $data The validated contact form data.
 * @param string $from The sender email address.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function sendContactEmail($to, $data, $from) {
    $headers   = [
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=UTF-8',
        'From: ' . $from,
        'Reply-To: ' . $data['email'],
        'X-Mailer: PHP/' . phpversion()
    ];
    $body = "You have received a new message:\n\n"
          . "Name: {$data['name']}\n"
          . "Email: {$data['email']}\n"
          . "Subject: {$data['subject']}\n\n"
          . "Message:\n{$data['message']}";
    return mail($to, 'Contact Form Submission: ' . $data['subject'], $body, implode("\r\n", $headers));
}

/**
 * Sends an email using the PHP mail() function.
 *
 * @param string $to      The recipient's email address.
 * @param string $subject The subject of the email.
 * @param string $body    The HTML body of the email.
 * @param array  $headers Optional. An associative array of additional headers.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function sendEmail($to, $subject, $body, $headers = []) {
    $db       = DB::getInstance();
    $settings = $db->selectOne("SELECT contact_email FROM settings LIMIT 1");
    if (empty($settings['contact_email'])) {
        throw new Exception('Contact email not set in settings.');
    }
    $from         = $settings['contact_email'];
    $defaultHeads = [
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=UTF-8',
        'From'         => $from,
    ];
    $all          = array_merge($defaultHeads, $headers);
    $strHeads     = '';
    foreach ($all as $k => $v) {
        $strHeads .= "$k: $v\r\n";
    }
    return mail($to, $subject, $body, $strHeads);
}

/**
 * Sends a password reset email to the user.
 *
 * @param string $email The recipient's email address.
 * @param string $token The unique password reset token.
 */
function sendPasswordResetEmail($email, $token) {
    $settings  = getSiteSettings();
    $siteName  = $settings['site_name'] ?? 'Your Site Name';
    $siteUrl   = fullUrl();
    $fromEmail = $settings['contact_email'] ?? 'noreply@example.com';
    $resetLink = $siteUrl . 'reset-password.php?token=' . urlencode($token);

    $subject = "Password Reset Request for " . $siteName;
    $body    = "
    <html>
    <head>
      <title>Password Reset</title>
      <style>
        body { font-family:Arial,sans-serif; background:#f2f2f2; margin:0; padding:0; }
        .container { max-width:600px; margin:0 auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
        .header h1 { color:#333; font-size:24px; margin:0; text-align:center; padding:10px 0; }
        .content { padding:20px; text-align:center; color:#333; font-size:16px; }
        .btn { background:#4F46E5; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block; margin-top:20px; }
        .footer { text-align:center; font-size:12px; color:#888; margin-top:20px; padding-bottom:20px; }
      </style>
    </head>
    <body>
      <div class='container'>
        <div class='header'><h1>Password Reset</h1></div>
        <div class='content'>
          <p>Hi,</p>
          <p>You requested to reset your password for your account on <a href='".htmlspecialchars($siteUrl,ENT_QUOTES)."'>{$siteName}</a>. Click the button below:</p>
          <a href='".htmlspecialchars($resetLink,ENT_QUOTES)."' class='btn'>Reset Your Password</a>
          <p>If you did not request this, ignore this email.</p>
        </div>
        <div class='footer'>
          <p>&copy; ".date('Y')." <a href='".htmlspecialchars($siteUrl,ENT_QUOTES)."'>{$siteName}</a>. All rights reserved.</p>
        </div>
      </div>
    </body>
    </html>
    ";

    $headers = [
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=UTF-8',
        'From'         => $fromEmail,
        'Return-Path'  => $fromEmail
    ];
    sendEmail($email, $subject, $body, $headers);
}

/**
 * Sends a verification email to the user.
 *
 * @param string $email The recipient's email address.
 * @param string $token The unique verification token.
 */
function sendVerificationEmail($email, $token) {
    $settings             = getSiteSettings();
    $siteName             = $settings['site_name'] ?? 'My SaaS Application';
    $siteUrl              = fullUrl();
    $fromEmail            = $settings['contact_email'] ?? 'noreply@example.com';
    $verificationLink     = $siteUrl . 'verify-email.php?token=' . urlencode($token);

    $subject = "Verify your email for " . $siteName;
    $body    = "
    <html>
    <head>
      <title>Verify Your Email</title>
      <style>
        body { font-family:Arial,sans-serif; background:#f2f2f2; margin:0; padding:0; }
        .container { max-width:600px; margin:0 auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
        .header h1 { color:#333; font-size:24px; margin:0; text-align:center; padding:10px 0; }
        .content { padding:20px; text-align:center; color:#333; font-size:16px; }
        .btn { background:#4F46E5; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block; margin-top:20px; }
        .footer { text-align:center; font-size:12px; color:#888; margin-top:20px; padding-bottom:20px; }
      </style>
    </head>
    <body>
      <div class='container'>
        <div class='header'><h1>Verify Your Email</h1></div>
        <div class='content'>
          <p>Hello,</p>
          <p>Please verify your email for <a href='".htmlspecialchars($siteUrl,ENT_QUOTES)."'>{$siteName}</a>:</p>
          <a href='".htmlspecialchars($verificationLink,ENT_QUOTES)."' class='btn'>Verify Email</a>
          <p>If you didn't sign up, ignore this email.</p>
        </div>
        <div class='footer'>
          <p>&copy; ".date('Y')." <a href='".htmlspecialchars($siteUrl,ENT_QUOTES)."'>{$siteName}</a>. All rights reserved.</p>
        </div>
      </div>
    </body>
    </html>
    ";

    $headers = [
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=UTF-8',
        'From'         => $fromEmail,
        'Return-Path'  => $fromEmail
    ];
    sendEmail($email, $subject, $body, $headers);
}

/**
 * Validates the contact form inputs.
 *
 * @param array $post               The $_POST array from the form submission.
 * @param string $recaptcha_secret_key The secret key for Google reCAPTCHA.
 * @return array Returns an array with 'errors' and 'data' keys.
 */
function validateContactForm($post, $recaptcha_secret_key) {
    $errors = [];
    $data   = [];

    // Name
    if (empty(trim($post['name']))) {
        $errors['name_err'] = "Please enter your name.";
    } else {
        $data['name'] = htmlspecialchars(trim($post['name']), ENT_QUOTES, 'UTF-8');
    }

    // Email
    if (empty(trim($post['email']))) {
        $errors['email_err'] = "Please enter your email.";
    } elseif (!filter_var(trim($post['email']), FILTER_VALIDATE_EMAIL)) {
        $errors['email_err'] = "Please enter a valid email.";
    } else {
        $data['email'] = htmlspecialchars(trim($post['email']), ENT_QUOTES, 'UTF-8');
    }

    // Subject
    if (empty(trim($post['subject']))) {
        $errors['subject_err'] = "Please enter a subject.";
    } else {
        $data['subject'] = htmlspecialchars(trim($post['subject']), ENT_QUOTES, 'UTF-8');
    }

    // Message
    if (empty(trim($post['message']))) {
        $errors['message_err'] = "Please enter a message.";
    } else {
        $data['message'] = htmlspecialchars(trim($post['message']), ENT_QUOTES, 'UTF-8');
    }

    // reCAPTCHA
    if (empty($post['g-recaptcha-response'])) {
        $errors['recaptcha_err'] = "Please complete the reCAPTCHA verification.";
    } else {
        $url    = 'https://www.google.com/recaptcha/api/siteverify';
        $fields = [
            'secret'   => $recaptcha_secret_key,
            'response' => $post['g-recaptcha-response'],
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        $opts   = ['http'=>[
            'header'=>"Content-type: application/x-www-form-urlencoded\r\n",
            'method'=>"POST",
            'content'=>http_build_query($fields),
            'timeout'=>10
        ]];
        $ctx    = stream_context_create($opts);
        $resp   = @file_get_contents($url, false, $ctx);
        $json   = json_decode($resp);
        if (!$json || !$json->success) {
            $errors['recaptcha_err'] = "reCAPTCHA verification failed. Please try again.";
        }
    }

    return ['errors'=>$errors, 'data'=>$data];
}

/**
 * Validates the CSRF token from the form against the session.
 *
 * @param string $token The CSRF token from the form.
 * @return bool True if valid, false otherwise.
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
<?php
/**
 * Utility Functions for your SaaS application
 */

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
 * Redirects to a specified URL.
 *
 * @param string $url The URL to redirect to.
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        error_log("Cannot redirect to $url; headers already sent.");
    }
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
 * Retrieves the currently logged-in user's data.
 *
 * @return array|null An associative array of user data or null if not logged in.
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        $db = DB::getInstance();
        $user = $db->selectOne(
            "SELECT * FROM users WHERE id = :id",
            [
                ':id' => $_SESSION['user_id']
            ]
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
    $site_settings = getSiteSettings() ?? [];
    $site_name = isset($site_settings['site_name']) && is_string($site_settings['site_name']) ? $site_settings['site_name'] : 'Your Site Name';
    $baseTitle = $site_name . " | ";

	$metadata = [
		"/about.php" => [
			"title" => "About Us",
			"description" => "Learn more about what we do at {$site_name}."
		],
		"/contact.php" => [
			"title" => "Contact Us",
			"description" => "Get in touch with the team at {$site_name}."
		],
		"/dashboard.php" => [
			"title" => "Dashboard",
			"description" => "Manage your account and access exclusive features on {$site_name}."
		],
		"/features.php" => [
			"title" => "Features",
			"description" => "Explore the features and benefits of using {$site_name}."
		],
		"/forgot-password.php" => [
			"title" => "Forgot Password",
			"description" => "Reset your {$site_name} password."
		],
		"/index.php" => [
			"title" => "Welcome to {$site_name}",
			"description" => "Find the best services with {$site_name}."
		],
		"/login.php" => [
			"title" => "Login",
			"description" => "Access your {$site_name} account."
		],
		"/privacy-policy.php" => [
			"title" => "Privacy Policy",
			"description" => "Learn about the privacy policy for {$site_name}."
		],
		"/reset-password.php" => [
			"title" => "Reset Password",
			"description" => "Set a new password for your {$site_name} account."
		],
		"/settings.php" => [
			"title" => "Site Settings",
			"description" => "Adjust the settings and configurations for {$site_name}."
		],
		"/signup.php" => [
			"title" => "Sign Up",
			"description" => "Create your {$site_name} account."
		],
		"/users.php" => [
			"title" => "User Management",
			"description" => "Manage users and permissions for {$site_name}."
		],
		"/verify-email.php" => [
			"title" => "Verify Your Email",
			"description" => "Verify your email address to activate your {$site_name} account."
		]
	];

    // Default title and description
    $defaultTitle = "Home";
    $defaultDescription = "Welcome to " . $site_name . " – your go-to solution for all your needs.";

    $pageTitle = $baseTitle . $defaultTitle;
    $pageDescription = $defaultDescription;

    if (array_key_exists($page, $metadata) && is_array($metadata[$page])) {
        if (isset($metadata[$page]['title']) && is_string($metadata[$page]['title']) && !empty($metadata[$page]['title'])) {
            $pageTitle = $baseTitle . $metadata[$page]['title'];
        }

        if (isset($metadata[$page]['description']) && is_string($metadata[$page]['description']) && !empty($metadata[$page]['description'])) {
            $pageDescription = $metadata[$page]['description'];
        }
    }

    return [
        "title" => $pageTitle,
        "description" => $pageDescription
    ];
}

/**
 * Fetches site settings from the database or returns default settings.
 *
 * @return array An associative array of site settings.
 */
function getSiteSettings() {
    $db = DB::getInstance();
    $settings = $db->selectOne("SELECT * FROM settings WHERE id = 1") ?? [];

    // Provide default settings if none are found
    $defaultSettings = [
        'site_name' => 'Your Site Name',
        'site_description' => 'Your site description.',
        'contact_email' => 'no-reply@example.com',
        // Add other default settings as needed
    ];

    return array_merge($defaultSettings, $settings);
}

/**
 * Generates the full base URL of the site.
 *
 * @return string The base URL (e.g., "https://example.com/").
 */
function fullUrl() {
    try {
        $protocol = 'http';
        if (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        ) {
            $protocol = 'https';
        }

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Get the script's directory path
        $scriptDir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

        return sprintf("%s://%s%s", $protocol, $host, $scriptDir);
    } catch(Exception $e) {
        // Log the error message and return a default value
        error_log('Error generating full URL: ' . $e->getMessage());
        // As a safer fallback, return a relative URL
        return '/';
    }
}

/**
 * Determines if a navigation link is active.
 *
 * @param string $page The target page filename (e.g., "about.php").
 * @return string The CSS classes to apply.
 */
function isActive($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page === $page) {
        return 'text-indigo-600 px-3 py-2 text-sm font-medium uppercase border-b-2 border-indigo-600';
    } else {
        return 'text-gray-700 px-3 py-2 text-sm font-medium uppercase border-b-2 border-transparent hover:border-indigo-600 transition duration-300';
    }
}

/**
 * Checks if the currently logged-in user is an admin.
 *
 * @return bool True if the user is an admin, false otherwise.
 */
function isAdmin() {
    if (isLoggedIn()) {
        $currentUser = getCurrentUser();
        if ($currentUser && isset($currentUser['role']) && $currentUser['role'] === 'admin') {
            return true;
        }
    }
    return false;
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
 * Validates the CSRF token from the form against the session.
 *
 * @param string $token The CSRF token from the form.
 * @return bool True if valid, false otherwise.
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sends an email using the PHP mail() function.
 *
 * @param string $to The recipient's email address.
 * @param string $subject The subject of the email.
 * @param string $body The HTML body of the email.
 * @param array $headers Optional. An associative array of additional headers.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function sendEmail($to, $subject, $body, $headers = []) {
    // Get the contact email from the settings table
    $db = DB::getInstance();
    $settings = $db->selectOne("SELECT contact_email FROM settings LIMIT 1");

    // Ensure we have a contact email
    if (empty($settings['contact_email'])) {
        throw new Exception('Contact email not set in settings.');
    }

    $contactEmail = $settings['contact_email'];

    // Default headers
    $defaultHeaders = [
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=UTF-8',
        'From' => $contactEmail,
    ];

    // Merge default headers with any additional headers
    $allHeaders = array_merge($defaultHeaders, $headers);

    // Build the headers string
    $headersString = '';
    foreach ($allHeaders as $key => $value) {
        $headersString .= $key . ': ' . $value . "\r\n";
    }

    // Send the email
    return mail($to, $subject, $body, $headersString);
}

<?php
/**
 * Sends a verification email to the user.
 *
 * @param string $email The recipient's email address.
 * @param string $token The unique verification token.
 */
function sendVerificationEmail($email, $token) {
    $siteSettings = getSiteSettings();  // Fetch site settings
    $siteName = $siteSettings['site_name'] ?? 'My SaaS Application';
    $siteIcon = $siteSettings['site_icon'] ?? 'fas fa-globe'; // Default icon
    $siteUrl = fullUrl();

    // Verification link
    $verificationLink = $siteUrl . 'verify-email.php?token=' . urlencode($token);

    // Email subject and body
    $subject = "Verify your email for " . $siteName;
    $body = "
    <html>
    <head>
        <title>Verify Your Email</title>
    </head>
    <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
        <!-- Logo with Font Awesome Icon -->
        <div style='text-align: center; margin-bottom: 20px;'>
            <div style='font-size: 24px; font-weight: bold; color: #4F46E5; display: inline-flex; align-items: center;'>
                <a href='" . htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8') . "' style='text-decoration: none; color: #4F46E5;'>
                    <i class='" . htmlspecialchars($siteIcon, ENT_QUOTES, 'UTF-8') . "' style='margin-right: 8px;'></i>
                    " . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . "
                </a>
            </div>
        </div>

        <!-- Main email content inside the white box -->
        <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto;'>
            <p>Hello,</p>
            <p>Please click the link below to verify your email address:</p>
            <p style='text-align: center;'>
                <a href='" . htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') . "' style='display: inline-block; padding: 10px 20px; background-color: #4F46E5; color: #ffffff; text-decoration: none; border-radius: 5px;'>Verify Email</a>
            </p>
            <p>If you didn't sign up for this account, please ignore this email.</p>
            <p>Thanks,<br>" . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . "</p>
        </div>
    </body>
    </html>
    ";

    sendEmail($email, $subject, $body);
}

/**
 * Sends a password reset email to the user.
 *
 * @param string $email The recipient's email address.
 * @param string $token The unique password reset token.
 */
function sendPasswordResetEmail($email, $token) {
    $siteSettings = getSiteSettings();  // Fetch site settings
    $siteName = $siteSettings['site_name'] ?? 'Your Site Name';
    $siteIcon = $siteSettings['site_icon'] ?? 'fas fa-globe'; // Default icon
    $siteUrl = fullUrl();

    // Password reset link
    $resetLink = $siteUrl . 'reset-password.php?token=' . urlencode($token);

    // Email subject and body with a clean, boxed layout
    $subject = "Password Reset Request for " . $siteName;
    $body = "
    <html>
    <head>
        <title>Password Reset</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f2f2f2;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .header {
                text-align: center;
                padding: 10px 0;
            }
            .header h1 {
                color: #333333;
                font-size: 24px;
                margin: 0;
            }
            .content {
                padding: 20px;
                text-align: center;
                color: #333333;
                font-size: 16px;
            }
            .btn {
                background-color: #4F46E5;
                color: #ffffff;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                display: inline-block;
                margin-top: 20px;
            }
            .footer {
                text-align: center;
                font-size: 12px;
                color: #888888;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div style='text-align: center; margin-bottom: 20px;'>
            <!-- Logo with Font Awesome Icon -->
            <div style='font-size: 24px; font-weight: bold; color: #4F46E5; display: inline-flex; align-items: center;'>
                <a href='" . htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8') . "' style='text-decoration: none; color: #4F46E5;'>
                    <i class='" . htmlspecialchars($siteIcon, ENT_QUOTES, 'UTF-8') . "' style='margin-right: 8px;'></i>
                    " . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . "
                </a>
            </div>
        </div>
        <div class='container'>
            <div class='header'>
                <h1>Password Reset</h1>
            </div>
            <div class='content'>
                <p>Hi,</p>
                <p>You requested to reset your password for your account on $siteName. Click the button below to reset it:</p>
                <a href='" . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "' class='btn'>Reset Your Password</a>
                <p>If you did not request this password reset, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " $siteName. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    sendEmail($email, $subject, $body);
}

?>
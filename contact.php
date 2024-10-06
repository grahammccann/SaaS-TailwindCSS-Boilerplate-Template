<?php
// File: contact.php

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

// Fetch current user data (if needed)
$currentUser = getCurrentUser();

// Initialize variables
$name = $email = $subject = $message = "";
$name_err = $email_err = $subject_err = $message_err = $recaptcha_err = $general_err = $success_msg = "";

// Fetch site settings, including reCAPTCHA keys and contact email
$siteSettings = getSiteSettings();
$recaptcha_site_key = $siteSettings['recaptcha_site_key'] ?? '';
$recaptcha_secret_key = $siteSettings['recaptcha_secret_key'] ?? '';
$contact_email = $siteSettings['contact_email'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $general_err = "Invalid CSRF token.";
    } else {
        // Validate Name
        if (empty(trim($_POST["name"]))) {
            $name_err = "Please enter your name.";
        } else {
            $name = htmlspecialchars(trim($_POST["name"]), ENT_QUOTES, 'UTF-8');
        }

        // Validate Email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter your email.";
        } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email.";
        } else {
            $email = htmlspecialchars(trim($_POST["email"]), ENT_QUOTES, 'UTF-8');
        }

        // Validate Subject
        if (empty(trim($_POST["subject"]))) {
            $subject_err = "Please enter a subject.";
        } else {
            $subject = htmlspecialchars(trim($_POST["subject"]), ENT_QUOTES, 'UTF-8');
        }

        // Validate Message
        if (empty(trim($_POST["message"]))) {
            $message_err = "Please enter a message.";
        } else {
            $message = htmlspecialchars(trim($_POST["message"]), ENT_QUOTES, 'UTF-8');
        }

        // Validate reCAPTCHA
        if (empty($_POST['g-recaptcha-response'])) {
            $recaptcha_err = "Please complete the reCAPTCHA verification.";
        } else {
            $recaptcha_response = $_POST['g-recaptcha-response'];

            // Make a POST request to verify reCAPTCHA
            $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => $recaptcha_secret_key,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                    'timeout' => 10
                ]
            ];
            $context  = stream_context_create($options);
            $verify = @file_get_contents($verify_url, false, $context);
            $captcha_success = json_decode($verify);

            if ($captcha_success === null || !$captcha_success->success) {
                $recaptcha_err = "reCAPTCHA verification failed. Please try again.";
            }
        }

        // Check for errors before sending email
        if (empty($name_err) && empty($email_err) && empty($subject_err) && empty($message_err) && empty($recaptcha_err) && empty($general_err)) {
            // Ensure contact email is configured
            if (empty($contact_email)) {
                $general_err = "Contact email is not configured. Please contact the administrator.";
            } else {
                // Prepare email headers securely
                $email_headers = [];
                $email_headers[] = 'MIME-Version: 1.0';
                $email_headers[] = 'Content-type: text/plain; charset=UTF-8';
                $email_headers[] = 'From: ' . $contact_email;
                $email_headers[] = 'Reply-To: ' . $email;
                $email_headers[] = 'X-Mailer: PHP/' . phpversion();

                $headers_string = implode("\r\n", $email_headers);

                // Prepare email subject and body
                $email_subject = 'Contact Form Submission: ' . $subject;
                $email_body = "You have received a new message from your website contact form.\n\n" .
                              "Here are the details:\n" .
                              "Name: $name\n" .
                              "Email: $email\n" .
                              "Subject: $subject\n\n" .
                              "Message:\n$message";

                // Send the email
                if (mail($contact_email, $email_subject, $email_body, $headers_string)) {
                    $success_msg = "Your message has been sent successfully! We will get back to you shortly.";
                    // Clear the form values
                    $name = $email = $subject = $message = "";
                } else {
                    $general_err = "Something went wrong. Please try again later.";
                }
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCsrfToken();

// Include the header (handles metadata and navigation)
include(__DIR__ . "/includes/inc-header.php");
?>

<!-- Contact Page Introduction -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">Get in Touch with Us</h1>
        <p class="text-lg md:text-xl text-gray-600">
            Have questions, feedback, or need support? We're here to help! Fill out the form below, and our team will get back to you as soon as possible.
        </p>
    </div>
</section>

<!-- Contact Form Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto bg-white p-10 rounded-lg shadow-lg">
            <?php 
            if (!empty($success_msg)) {
                echo '<div class="bg-green-100 text-green-700 p-4 rounded mb-6 flex items-center"><i class="fas fa-check-circle mr-2"></i>' . htmlspecialchars($success_msg) . '</div>';
            } 
            if (!empty($general_err)) {
                echo '<div class="bg-red-100 text-red-700 p-4 rounded mb-6 flex items-center"><i class="fas fa-exclamation-circle mr-2"></i>' . htmlspecialchars($general_err) . '</div>';
            }
            ?>
            <form action="" method="POST" class="space-y-6">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-gray-700 font-semibold mb-2">Name<span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name); ?>" required 
                        class="w-full px-4 py-2 border <?= !empty($name_err) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php 
                    if (!empty($name_err)) {
                        echo '<p class="text-red-500 text-sm mt-1">' . htmlspecialchars($name_err) . '</p>';
                    }
                    ?>
                </div>
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-gray-700 font-semibold mb-2">Email<span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email); ?>" required 
                        class="w-full px-4 py-2 border <?= !empty($email_err) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php 
                    if (!empty($email_err)) {
                        echo '<p class="text-red-500 text-sm mt-1">' . htmlspecialchars($email_err) . '</p>';
                    }
                    ?>
                </div>
                <!-- Subject Field -->
                <div>
                    <label for="subject" class="block text-gray-700 font-semibold mb-2">Subject<span class="text-red-500">*</span></label>
                    <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($subject); ?>" required 
                        class="w-full px-4 py-2 border <?= !empty($subject_err) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php 
                    if (!empty($subject_err)) {
                        echo '<p class="text-red-500 text-sm mt-1">' . htmlspecialchars($subject_err) . '</p>';
                    }
                    ?>
                </div>
                <!-- Message Field -->
                <div>
                    <label for="message" class="block text-gray-700 font-semibold mb-2">Message<span class="text-red-500">*</span></label>
                    <textarea id="message" name="message" rows="6" required 
                        class="w-full px-4 py-2 border <?= !empty($message_err) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($message); ?></textarea>
                    <?php 
                    if (!empty($message_err)) {
                        echo '<p class="text-red-500 text-sm mt-1">' . htmlspecialchars($message_err) . '</p>';
                    }
                    ?>
                </div>
                <!-- reCAPTCHA Widget -->
                <?php if (!empty($recaptcha_site_key)) { ?>
                    <div class="flex justify-center">
                        <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptcha_site_key, ENT_QUOTES, 'UTF-8'); ?>"></div>
                    </div>
                    <?php 
                    if (!empty($recaptcha_err)) {
                        echo '<p class="text-red-500 text-sm mt-2 text-center">' . htmlspecialchars($recaptcha_err) . '</p>';
                    }
                    ?>
                <?php } else { ?>
                    <p class="text-red-500 text-sm mt-2 text-center">reCAPTCHA site key is not configured. Please contact the administrator.</p>
                <?php } ?>
                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-md shadow-md transition duration-300">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Additional Information Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 text-center mb-8">Why Contact Us?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Reason 1 -->
            <div class="text-center">
                <i class="fas fa-headset text-indigo-600 text-4xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Customer Support</h3>
                <p class="text-gray-600">
                    Need help using our services? Our dedicated support team is here to assist you with any questions or issues.
                </p>
            </div>
            <!-- Reason 2 -->
            <div class="text-center">
                <i class="fas fa-comments text-indigo-600 text-4xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Feedback</h3>
                <p class="text-gray-600">
                    We value your feedback! Let us know how we can improve our services to better serve your needs.
                </p>
            </div>
            <!-- Reason 3 -->
            <div class="text-center">
                <i class="fas fa-info-circle text-indigo-600 text-4xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">General Inquiries</h3>
                <p class="text-gray-600">
                    Have general questions about our services or partnership opportunities? Feel free to reach out.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-20 bg-gradient-to-r from-indigo-600 to-indigo-800 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">We're Here to Help!</h2>
        <p class="text-lg md:text-xl mb-8">
            Your satisfaction is our top priority. Don't hesitate to contact us with any questions or feedback.
        </p>
        <a href="<?= htmlspecialchars(fullUrl() . 'signup.php', ENT_QUOTES, 'UTF-8'); ?>" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition duration-300">
            Get Started
        </a>
    </div>
</section>

<?php
    include(__DIR__ . "/includes/inc-footer.php");
?>
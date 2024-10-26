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

// Initialize variables for error and success messages
$errors = [];
$general_err = $success_msg = "";

// Fetch site settings, including reCAPTCHA keys and contact email
$siteSettings = getSiteSettings();
$recaptcha_site_key = $siteSettings['recaptcha_site_key'] ?? '';
$recaptcha_secret_key = $siteSettings['recaptcha_secret_key'] ?? '';
$contact_email = $siteSettings['contact_email'] ?? '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate the form
    $validation = validateContactForm($_POST, $recaptcha_secret_key);
    $errors = $validation['errors'];
    $data = $validation['data'];

    // Check if there are no validation errors and proceed to send email
    if (empty($errors)) {
        // Ensure contact email is configured
        if (empty($contact_email)) {
            $general_err = "Contact email is not configured. Please contact the administrator.";
        } else {
            // Send the contact email
            if (sendContactEmail($contact_email, $data, $contact_email)) {
                $success_msg = "Your message has been sent successfully! We will get back to you shortly.";
                // Clear the form values
                $_POST = [];
            } else {
                $general_err = "Something went wrong. Please try again later.";
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
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>" required 
                        class="w-full px-4 py-2 border <?= !empty($errors['name_err']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php 
                    if (!empty($errors['name_err'])) {
                        echo '<p class="text-red-500 text-sm mt-1">' . htmlspecialchars($errors['name_err']) . '</p>';
                    }
                    ?>
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-gray-700 font-semibold mb-2">Email<span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required 
                        class="w-full px-4 py-2 border <?= !empty($errors['email_err']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php 
                    if (!empty($errors['email_err'])) {
                        echo '<p class="text-red-500 text-sm mt-1">' . htmlspecialchars($errors['email_err']) . '</p>';
                    }
                    ?>
                </div>

                <!-- Subject Field -->
                <div>
                    <label for="subject" class="block text-gray-700 font-semibold mb-2">Subject<span class="text-red-500">*</span></label>
                    <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? ''); ?>" required 
                        class="w-full px-4 py-2 border <?= !empty($errors['subject_err']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php 
                    if (!empty($errors['subject_err'])) {
                        echo '<p class="text-red-500 text-sm mt-1">' . htmlspecialchars($errors['subject_err']) . '</p>';
                    }
                    ?>
                </div>

                <!-- Message Field -->
                <div>
                    <label for="message" class="block text-gray-700 font-semibold mb-2">Message<span class="text-red-500">*</span></label>
                    <textarea id="message" name="message" rows="6" required 
                        class="w-full px-4 py-2 border <?= !empty($errors['message_err']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    <?php 
                    if (!empty($errors['message_err'])) {
                        echo '<p class="text-red-500 text-sm mt-1">' . htmlspecialchars($errors['message_err']) . '</p>';
                    }
                    ?>
                </div>

                <!-- reCAPTCHA Widget -->
                <?php if (!empty($recaptcha_site_key)) { ?>
                    <div class="flex justify-center">
                        <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptcha_site_key, ENT_QUOTES, 'UTF-8'); ?>"></div>
                    </div>
                    <?php 
                    if (!empty($errors['recaptcha_err'])) {
                        echo '<p class="text-red-500 text-sm mt-2 text-center">' . htmlspecialchars($errors['recaptcha_err']) . '</p>';
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

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>
<?php

// File: contact.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

$currentUser = getCurrentUser();
$errors = [];
$general_err = $success_msg = "";

$siteSettings = getSiteSettings();
$recaptcha_site_key = $siteSettings['recaptcha_site_key'] ?? '';
$recaptcha_secret_key = $siteSettings['recaptcha_secret_key'] ?? '';
$contact_email = $siteSettings['contact_email'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $validation = validateContactForm($_POST, $recaptcha_secret_key);
    $errors = $validation['errors'];
    $data = $validation['data'];

    if (empty($errors)) {
        if (empty($contact_email)) {
            $general_err = "Contact email is not configured. Please contact the administrator.";
        } else {
            if (sendContactEmail($contact_email, $data, $contact_email)) {
                $success_msg = "Your message has been sent successfully! We will get back to you shortly.";
                $_POST = [];
            } else {
                $general_err = "Something went wrong. Please try again later.";
            }
        }
    }
}

$csrf_token = generateCsrfToken();

include(__DIR__ . "/includes/inc-header.php");
?>

<!-- Main Contact Page Content -->
<main>
    <!-- Intro -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">Get in Touch with Us</h1>
            <p class="text-lg md:text-xl text-gray-600">
                Have questions, feedback, or need support? We're here to help! Fill out the form below, and our team will get back to you as soon as possible.
            </p>
        </div>
    </section>

    <!-- Contact Form -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto bg-white p-10 rounded-lg shadow-lg">
                <?php if ($success_msg): ?>
                    <div class="bg-green-100 text-green-700 p-4 rounded mb-6 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success_msg) ?>
                    </div>
                <?php endif; ?>
                <?php if ($general_err): ?>
                    <div class="bg-red-100 text-red-700 p-4 rounded mb-6 flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($general_err) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-gray-700 font-semibold mb-2">Name<span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required 
                            class="w-full px-4 py-2 border <?= isset($errors['name_err']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php if (isset($errors['name_err'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['name_err']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-gray-700 font-semibold mb-2">Email<span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required 
                            class="w-full px-4 py-2 border <?= isset($errors['email_err']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php if (isset($errors['email_err'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['email_err']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-gray-700 font-semibold mb-2">Subject<span class="text-red-500">*</span></label>
                        <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required 
                            class="w-full px-4 py-2 border <?= isset($errors['subject_err']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php if (isset($errors['subject_err'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['subject_err']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="message" class="block text-gray-700 font-semibold mb-2">Message<span class="text-red-500">*</span></label>
                        <textarea id="message" name="message" rows="6" required 
                            class="w-full px-4 py-2 border <?= isset($errors['message_err']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        <?php if (isset($errors['message_err'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['message_err']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- reCAPTCHA -->
                    <?php if ($recaptcha_site_key): ?>
                        <div class="flex justify-center">
                            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptcha_site_key) ?>"></div>
                        </div>
                        <?php if (isset($errors['recaptcha_err'])): ?>
                            <p class="text-red-500 text-sm mt-2 text-center"><?= htmlspecialchars($errors['recaptcha_err']) ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-red-500 text-sm text-center">reCAPTCHA site key not configured.</p>
                    <?php endif; ?>

                    <!-- Submit -->
                    <div>
						<button type="submit" 
							class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-md shadow-md transition duration-300 flex items-center justify-center gap-2">
							<i class="fas fa-paper-plane"></i>
							Send Message
						</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Additional Info -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 text-center mb-8">Why Contact Us?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div>
                    <i class="fas fa-headset text-indigo-600 text-4xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Customer Support</h3>
                    <p class="text-gray-600">Need help using our services? We're here to assist you with any questions or issues.</p>
                </div>
                <div>
                    <i class="fas fa-comments text-indigo-600 text-4xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Feedback</h3>
                    <p class="text-gray-600">We value your feedback! Let us know how we can improve.</p>
                </div>
                <div>
                    <i class="fas fa-info-circle text-indigo-600 text-4xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">General Enquiries</h3>
                    <p class="text-gray-600">Have general questions or partnership ideas? Feel free to reach out.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 bg-gradient-to-r from-indigo-600 to-indigo-800 text-white text-center">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">We're Here to Help!</h2>
            <p class="text-lg md:text-xl mb-8">Your satisfaction is our top priority. Reach out any time.</p>
            <a href="<?= fullUrl() ?>signup/" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition duration-300">
                Get Started
            </a>
        </div>
    </section>
</main>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>
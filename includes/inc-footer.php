<?php
// File: inc-footer.php
?>

<footer class="bg-gray-900 text-white mt-auto">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="text-gray-400">
                &copy; <?= date('Y'); ?> <a href="<?= htmlspecialchars(fullUrl(), ENT_QUOTES, 'UTF-8'); ?>" class="hover:text-white"><?= htmlspecialchars($siteSettings['site_name'] ?? 'My SaaS Application', ENT_QUOTES, 'UTF-8'); ?></a>. All rights reserved.
            </div>
            <div class="flex space-x-4 mt-4 md:mt-0">
                <a href="<?= htmlspecialchars(fullUrl() . 'privacy-policy.php', ENT_QUOTES, 'UTF-8'); ?>" class="text-gray-400 hover:text-white transition duration-300">Privacy Policy</a>
                <a href="<?= htmlspecialchars(fullUrl() . 'terms-of-service.php', ENT_QUOTES, 'UTF-8'); ?>" class="text-gray-400 hover:text-white transition duration-300">Terms of Service</a>
                <a href="<?= htmlspecialchars(fullUrl() . 'contact.php', ENT_QUOTES, 'UTF-8'); ?>" class="text-gray-400 hover:text-white transition duration-300">Contact Us</a>
            </div>
            <div class="flex space-x-4 mt-4 md:mt-0">
                <!-- Show icons only if the corresponding link exists -->
                <?php if (!empty($siteSettings['facebook_link'])): ?>
                    <a href="<?= htmlspecialchars($siteSettings['facebook_link'], ENT_QUOTES, 'UTF-8'); ?>" class="text-gray-400 hover:text-white transition duration-300">
                        <i class="fab fa-facebook fa-lg"></i>
                    </a>
                <?php endif; ?>
                <?php if (!empty($siteSettings['twitter_link'])): ?>
                    <a href="<?= htmlspecialchars($siteSettings['twitter_link'], ENT_QUOTES, 'UTF-8'); ?>" class="text-gray-400 hover:text-white transition duration-300">
                        <i class="fab fa-twitter fa-lg"></i>
                    </a>
                <?php endif; ?>
                <?php if (!empty($siteSettings['instagram_link'])): ?>
                    <a href="<?= htmlspecialchars($siteSettings['instagram_link'], ENT_QUOTES, 'UTF-8'); ?>" class="text-gray-400 hover:text-white transition duration-300">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<!-- Include Google reCAPTCHA JavaScript API -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

</body>
</html>
<?php
// File: includes/inc-footer.php
?>

<footer class="mt-auto bg-gray-800 text-gray-200 border-t border-gray-700">
  <div class="container mx-auto px-6 py-8 flex flex-col md:flex-row justify-between items-center">
    <!-- Company Info -->
    <div class="mb-6 md:mb-0 text-center md:text-left">
      <h4 class="text-sm font-semibold uppercase tracking-wide mb-2">Company Info</h4>
      <p class="text-xs">
        &copy; <?= date('Y'); ?> &mdash;
        <a href="<?= htmlspecialchars(fullUrl(), ENT_QUOTES, 'UTF-8'); ?>"
           class="text-indigo-400 hover:text-indigo-300 transition">
          <?= htmlspecialchars($siteSettings['site_name'] ?? 'My SaaS Application', ENT_QUOTES, 'UTF-8'); ?>
        </a>
        &mdash; All rights reserved.
      </p>
    </div>

	<!-- Quick Links -->
	<div class="mb-6 md:mb-0 text-center">
	  <h4 class="text-sm font-semibold uppercase tracking-wide mb-2">Quick Links</h4>
	  <ul class="flex flex-col md:flex-row items-center text-xs">
		<li>
		  <a href="<?= fullUrl() . 'privacy-policy/'; ?>"
			 class="hover:text-white transition">
			Privacy Policy
		  </a>
		</li>
		<li class="hidden md:inline mx-2">|</li>
		<li>
		  <a href="<?= fullUrl() . 'terms-of-service/'; ?>"
			 class="hover:text-white transition">
			Terms of Service
		  </a>
		</li>
		<li class="hidden md:inline mx-2">|</li>
		<li>
		  <a href="<?= fullUrl() . 'contact/'; ?>"
			 class="hover:text-white transition">
			Contact
		  </a>
		</li>
	  </ul>
	</div>

    <!-- Social Media -->
    <div class="text-center md:text-right">
      <h4 class="text-sm font-semibold uppercase tracking-wide mb-2">Follow Us</h4>
      <div class="flex justify-center md:justify-end space-x-4 text-lg">
        <?php if (!empty($siteSettings['facebook_link'])): ?>
          <a href="<?= htmlspecialchars($siteSettings['facebook_link'], ENT_QUOTES, 'UTF-8'); ?>"
             class="hover:text-indigo-400 transition" target="_blank" rel="noopener">
            <i class="fab fa-facebook-f"></i>
          </a>
        <?php endif; ?>
        <?php if (!empty($siteSettings['x_link'])): ?>
          <a href="<?= htmlspecialchars($siteSettings['x_link'], ENT_QUOTES, 'UTF-8'); ?>"
             class="hover:text-indigo-400 transition" target="_blank" rel="noopener">
            <i class="fab fa-x-twitter"></i>
          </a>
        <?php endif; ?>
        <?php if (!empty($siteSettings['instagram_link'])): ?>
          <a href="<?= htmlspecialchars($siteSettings['instagram_link'], ENT_QUOTES, 'UTF-8'); ?>"
             class="hover:text-indigo-400 transition" target="_blank" rel="noopener">
            <i class="fab fa-instagram"></i>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</footer>

<!-- Include Google reCAPTCHA JavaScript API -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<!-- Include JQuery BEFORE saas.js -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include Custom JavaScript AFTER jQuery -->
<script src="<?= fullUrl(); ?>js/saas.js" async defer></script>

</body>
</html>
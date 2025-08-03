<?php
// File: users.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect non-admin users
if (!isAdmin()) {
    header("Location: " . fullUrl() . "login/");
    exit();
}

$db       = DB::getInstance();
$error    = '';
$success  = '';

// Handle POST (edit or delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        // Edit
        if (isset($_POST['edit_user'])) {
            $user_id   = intval($_POST['user_id']);
            $username  = trim($_POST['username']);
            $email     = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
            $role      = trim($_POST['role']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($username)) {
                $error = "Username cannot be empty.";
            } elseif (!$email) {
                $error = "Please enter a valid email address.";
            } elseif (!in_array($role, ['admin','user'])) {
                $error = "Invalid role.";
            } else {
                $updated = $db->update("users","id",$user_id,[
                    'username'   => $username,
                    'email'      => $email,
                    'role'       => $role,
                    'is_active'  => $is_active,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $success = $updated ? "User updated successfully." : "Failed to update user.";
            }
        }
        // Delete
        if (isset($_POST['delete_user'])) {
            $user_id = intval($_POST['user_id']);
            if ($user_id === $_SESSION['user_id']) {
                $error = "You cannot delete your own account.";
            } else {
                $deleted = $db->delete("users","id",$user_id);
                $success = $deleted ? "User deleted successfully." : "Failed to delete user.";
            }
        }
    }
}

$csrf_token = generateCsrfToken();
$users      = $db->select("SELECT id, username, email, role, is_active, created_at FROM users ORDER BY created_at DESC");
$userCount  = count($users);

include(__DIR__ . "/includes/inc-header.php");
?>

<main class="bg-gray-100 min-h-screen py-12 px-4">
  <div class="max-w-5xl mx-auto">

    <!-- Header with badge -->
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-3xl font-semibold text-gray-800 flex items-center">
        <i class="fas fa-users text-indigo-600 mr-2"></i> Users
      </h1>
      <span class="inline-flex items-center bg-indigo-100 text-indigo-800 text-sm font-semibold px-3 py-1 rounded-full">
        Users: <?= $userCount ?>
      </span>
    </div>
    <hr class="border-t-2 border-gray-200 mb-8">

    <!-- Alerts -->
    <?php if ($success): ?>
      <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-6 flex items-center">
        <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($success) ?>
      </div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-6 flex items-center">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Desktop Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden hidden md:block">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Role</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($users as $u): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-900"><?= $u['id'] ?></td>
                <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($u['username']) ?></td>
                <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($u['email']) ?></td>
                <td class="px-6 py-4 text-center text-sm text-gray-900"><?= ucfirst($u['role']) ?></td>
                <td class="px-6 py-4 text-center text-sm">
                  <?php if ($u['is_active']): ?>
                    <span class="inline-block px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>
                  <?php else: ?>
                    <span class="inline-block px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Inactive</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= date('j M Y, H:i', strtotime($u['created_at'])) ?></td>
                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                  <button
                    class="edit-button text-indigo-600 hover:text-indigo-800"
                    data-id="<?= $u['id'] ?>"
                    data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>"
                    data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
                    data-role="<?= $u['role'] ?>"
                    data-active="<?= $u['is_active'] ?>"
                    title="Edit"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <form method="POST" class="inline" onsubmit="return confirm('Delete this user?');">
                      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <button name="delete_user" class="text-red-600 hover:text-red-800" title="Delete">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile List -->
    <div class="md:hidden space-y-4">
      <?php foreach ($users as $u): ?>
        <div class="bg-white p-6 rounded-xl shadow divide-y divide-gray-200">
          <div class="pb-4">
            <div class="flex justify-between mb-1">
              <span class="text-sm text-gray-600">Username</span>
              <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($u['username']) ?></span>
            </div>
            <div class="flex justify-between mb-1">
              <span class="text-sm text-gray-600">Email</span>
              <span class="text-sm text-gray-900"><?= htmlspecialchars($u['email']) ?></span>
            </div>
            <div class="flex justify-between mb-1">
              <span class="text-sm text-gray-600">Role</span>
              <span class="text-sm text-gray-900"><?= ucfirst($u['role']) ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Status</span>
              <?php if ($u['is_active']): ?>
                <span class="inline-block px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>
              <?php else: ?>
                <span class="inline-block px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Inactive</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="pt-4 flex space-x-3">
            <button
              class="edit-button flex-1 inline-flex justify-center items-center border border-indigo-600 text-indigo-600 text-sm font-medium px-3 py-2 rounded-md hover:bg-indigo-50 transition"
              data-id="<?= $u['id'] ?>"
              data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>"
              data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
              data-role="<?= $u['role'] ?>"
              data-active="<?= $u['is_active'] ?>"
            >
              <i class="fas fa-edit mr-1"></i> Edit
            </button>
            <form method="POST" class="flex-1" onsubmit="return confirm('Delete this user?');">
              <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button
                name="delete_user"
                class="w-full inline-flex justify-center items-center border border-red-600 text-red-600 text-sm font-medium px-3 py-2 rounded-md hover:bg-red-50 transition"
              >
                <i class="fas fa-trash-alt mr-1"></i> Delete
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
      <div class="flex justify-between items-center p-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Edit User</h3>
        <button id="closeModal" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form id="editForm" method="POST" class="p-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="user_id" id="modal_user_id">

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-1">Username</label>
          <input type="text" name="username" id="modal_username" required
            class="w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-1">Email</label>
          <input type="email" name="email" id="modal_email" required
            class="w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-1">Role</label>
          <select name="role" id="modal_role" required
            class="w-full bg-white border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="flex items-center">
          <input type="checkbox" name="is_active" id="modal_active"
            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
          <label for="modal_active" class="ml-2 text-gray-700 text-sm">Active</label>
        </div>

        <div class="flex justify-end space-x-2">
          <button type="button" id="cancelModal" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
            Cancel
          </button>
          <button type="submit" name="edit_user" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
            <i class="fas fa-save mr-1"></i> Save
          </button>
        </div>
      </form>
    </div>
  </div>
</main>

<script>
  // Open modal and populate fields
  document.querySelectorAll('.edit-button').forEach(btn => {
    btn.addEventListener('click', () => {
      const id       = btn.dataset.id;
      const username = btn.dataset.username;
      const email    = btn.dataset.email;
      const role     = btn.dataset.role;
      const active   = btn.dataset.active === '1';

      document.getElementById('modal_user_id').value   = id;
      document.getElementById('modal_username').value  = username;
      document.getElementById('modal_email').value     = email;
      document.getElementById('modal_role').value      = role;
      document.getElementById('modal_active').checked  = active;

      document.getElementById('editModal').classList.remove('hidden');
    });
  });

  // Close modal
  document.getElementById('closeModal').onclick =
  document.getElementById('cancelModal').onclick = () => {
    document.getElementById('editModal').classList.add('hidden');
  };

  // Close modal on outside click
  document.getElementById('editModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) {
      e.currentTarget.classList.add('hidden');
    }
  });
</script>

<?php include(__DIR__ . "/includes/inc-footer.php"); ?>
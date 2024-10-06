<?php
// File: users.php

require_once(__DIR__ . "/includes/inc-db-connection.php");
require_once(__DIR__ . "/includes/inc-functions.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is admin
if (!isAdmin()) {
    header("Location: " . fullUrl() . "login.php");
    exit();
}

$db = DB::getInstance();

// Initialize variables
$error = '';
$success = '';
$editingUser = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        // Handle Edit User
        if (isset($_POST['edit_user'])) {
            $user_id = intval($_POST['user_id']);
            $username = trim($_POST['username']);
            $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
            $role = trim($_POST['role']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            // Validate inputs
            if (empty($username)) {
                $error = "Username cannot be empty.";
            } elseif (!$email) {
                $error = "Please enter a valid email address.";
            } elseif (!in_array($role, ['admin', 'user'])) {
                $error = "Invalid role selected.";
            } else {
                // Update user in the database
                $updated = $db->update("users", "id", $user_id, [
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'is_active' => $is_active,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if ($updated) {
                    $success = "User updated successfully.";
                } else {
                    $error = "Failed to update user.";
                }
            }
        }

        // Handle Delete User
        if (isset($_POST['delete_user'])) {
            $user_id = intval($_POST['user_id']);

            // Prevent deleting oneself
            if ($user_id === $_SESSION['user_id']) {
                $error = "You cannot delete your own account.";
            } else {
                // Delete user from database
                $deleted = $db->delete("users", "id", $user_id);

                if ($deleted) {
                    $success = "User deleted successfully.";
                } else {
                    $error = "Failed to delete user.";
                }
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCsrfToken();

// Fetch users from database
$users = $db->select("SELECT id, username, email, role, is_active FROM users");

// Include the header
include(__DIR__ . "/includes/inc-header.php");
?>

<main class="container mx-auto my-12 px-4">
    <div class="bg-white p-8 shadow-lg rounded-lg">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">User Management</h1>

        <!-- Success and Error Messages Inside the Container -->
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg">
                <thead>
                    <tr>
                        <th class="py-3 px-4 border-b text-left">ID</th>
                        <th class="py-3 px-4 border-b text-left">Username</th>
                        <th class="py-3 px-4 border-b text-left">Email</th>
                        <th class="py-3 px-4 border-b text-left">Role</th>
                        <th class="py-3 px-4 border-b text-left">Status</th>
                        <th class="py-3 px-4 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="py-3 px-4 border-b"><?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-3 px-4 border-b"><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-3 px-4 border-b"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-3 px-4 border-b"><?= htmlspecialchars(ucfirst($user['role']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-3 px-4 border-b"><?= $user['is_active'] ? 'Active' : 'Inactive' ?></td>
                            <td class="py-3 px-4 border-b flex space-x-2">
                                <!-- Edit Button -->
                                <button onclick="toggleEditForm(<?= $user['id'] ?>)" class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-edit"></i> Edit
                                </button>

                                <!-- Delete Button -->
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <!-- CSRF Token -->
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" name="delete_user" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Edit Form Row (Hidden by Default) -->
                        <tr id="edit-form-<?= $user['id'] ?>" class="hidden">
                            <td colspan="6" class="py-3 px-4 border-b bg-gray-50">
                                <form action="" method="POST" class="space-y-4">
                                    <!-- CSRF Token -->
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="username-<?= $user['id'] ?>" class="block text-gray-700 font-bold mb-1">Username:</label>
                                            <input
                                                type="text"
                                                id="username-<?= $user['id'] ?>"
                                                name="username"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                required
                                                value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                        </div>
                                        <div>
                                            <label for="email-<?= $user['id'] ?>" class="block text-gray-700 font-bold mb-1">Email:</label>
                                            <input
                                                type="email"
                                                id="email-<?= $user['id'] ?>"
                                                name="email"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                required
                                                value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="role-<?= $user['id'] ?>" class="block text-gray-700 font-bold mb-1">Role:</label>
                                            <select
                                                id="role-<?= $user['id'] ?>"
                                                name="role"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                required
                                            >
                                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </div>
                                        <div class="flex items-center mt-6">
                                            <input
                                                type="checkbox"
                                                id="is_active-<?= $user['id'] ?>"
                                                name="is_active"
                                                class="form-checkbox h-5 w-5 text-indigo-600"
                                                <?= $user['is_active'] ? 'checked' : '' ?>
                                            >
                                            <label for="is_active-<?= $user['id'] ?>" class="ml-2 text-gray-700">Active</label>
                                        </div>
                                    </div>
                                    <div class="flex justify-end space-x-2">
                                        <button type="button" onclick="toggleEditForm(<?= $user['id'] ?>)" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                            Cancel
                                        </button>
                                        <button type="submit" name="edit_user" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                            <i class="fas fa-save"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- JavaScript to Toggle Edit Forms -->
<script>
    function toggleEditForm(userId) {
        var editFormRow = document.getElementById('edit-form-' + userId);
        if (editFormRow.classList.contains('hidden')) {
            editFormRow.classList.remove('hidden');
        } else {
            editFormRow.classList.add('hidden');
        }
    }
</script>

<?php
    include(__DIR__ . "/includes/inc-footer.php");
?>

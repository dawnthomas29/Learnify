


<?php
// ViewUsers.php - Complete user management with delete, edit, and block/unblock functionality

// Database connection (replace with your credentials)
$conn = new mysqli("localhost", "root", "", "learnify");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'] ?? '';
    
    if (!empty($userId)) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting user']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
    }
    exit;
}

// Handle user update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $oldUserId = $_POST['old_user_id'] ?? '';
    $newUserId = $_POST['new_user_id'] ?? '';
    $roleId = $_POST['role_id'] ?? '';
    
    if (!empty($oldUserId) && !empty($newUserId) && !empty($roleId)) {
        // First check if the new user ID already exists (if it's being changed)
        if ($oldUserId !== $newUserId) {
            $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
            $checkStmt->bind_param("s", $newUserId);
            $checkStmt->execute();
            $checkStmt->store_result();
            
            if ($checkStmt->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'User ID already exists']);
                $checkStmt->close();
                exit;
            }
            $checkStmt->close();
        }
        
        $stmt = $conn->prepare("UPDATE users SET user_id = ?, role_id = ? WHERE user_id = ?");
        $stmt->bind_param("sis", $newUserId, $roleId, $oldUserId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating user']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
    }
    exit;
}

// Handle block/unblock user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_block'])) {
    $userId = $_POST['user_id'] ?? '';
    
    if (!empty($userId)) {
        // Toggle the is_blocked status
        $stmt = $conn->prepare("UPDATE users SET is_blocked = NOT is_blocked WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        
        if ($stmt->execute()) {
            // Get the new status to return it
            $statusStmt = $conn->prepare("SELECT is_blocked FROM users WHERE user_id = ?");
            $statusStmt->bind_param("s", $userId);
            $statusStmt->execute();
            $statusStmt->bind_result($isBlocked);
            $statusStmt->fetch();
            $statusStmt->close();
            
            echo json_encode([
                'success' => true, 
                'message' => 'User status updated',
                'is_blocked' => $isBlocked
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating user status']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
    }
    exit;
}

// Fetch users with their roles and block status
$query = "SELECT u.user_id, u.name, u.email, r.role_name, r.role_id, u.is_blocked 
          FROM users u 
          JOIN roles r ON u.role_id = r.role_id
          ORDER BY u.user_id";
$result = $conn->query($query);

// Fetch all roles for the edit modal
$rolesQuery = "SELECT role_id, role_name FROM roles";
$rolesResult = $conn->query($rolesQuery);
$roles = [];
while ($role = $rolesResult->fetch_assoc()) {
    $roles[] = $role;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learnify - User Management</title>
   <style>
        /* Dashboard-style gradient background */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            
            color: #333;
        }

        /* Container styling */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 0px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        /* Header styling */
       .header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
   
    padding: 20px 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    display: flex
;
    justify-content: space-between;
    align-items: center;
}

        h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        /* Search input */
        .search-input {
            padding: 12px 15px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            background: white;
            font-size: 14px;
            width: 300px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Table styling */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 0px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        /* Action buttons */
        .action-btns {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-edit {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        /* No users message */
        .no-users {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
        }

        /* Modal styling */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 400px;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.active .modal-content {
            transform: translateY(0);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            position: relative;
        }
        
        .modal-title {
            margin: 0;
            color: #2c3e50;
            font-size: 1.25rem;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .modal-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-cancel {
            background: #f1f1f1;
            color: #333;
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 4px solid rgba(0,0,0,0.1);
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-input {
                width: 100%;
            }
            
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<!-- Toast Notification -->


<body>
  <?php include 'sidebar.php'; ?>   

    <div id="toast" style="
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #2ecc71;
    color: white;
    padding: 14px 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    font-weight: 500;
    display: none;
    z-index: 9999;
    transition: all 0.3s ease;
"></div>
<!-- Block/Unblock Confirmation Modal -->
<div class="modal-overlay" id="blockModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="blockModalTitle">Confirm Action</h3>
        </div>
        <div class="modal-body">
            <p id="blockModalText">Are you sure you want to block this user?</p>
            <div class="loading-spinner" id="blockLoadingSpinner">
                <div class="spinner"></div>
                <p>Updating status...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn btn-cancel" id="blockCancelBtn">Cancel</button>
            <button class="modal-btn btn-confirm" id="blockConfirmBtn">Yes, Continue</button>
        </div>
    </div>
</div>
    <div class="container">
        <div class="header">
            <h1>User Management</h1>
            <input type="text" class="search-input" placeholder="Search users..." id="searchInput">
        </div>
        
        <div class="table-container">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['user_id']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['role_name']) ?></td>
                                <td class="<?= $row['is_blocked'] ? 'status-blocked' : 'status-active' ?>">
                                    <?= $row['is_blocked'] ? 'Blocked' : 'Active' ?>
                                </td>
                                <td class="action-btns">
                                    <button class="btn btn-edit" onclick="showEditModal(
                                        '<?= $row['user_id'] ?>',
                                        '<?= $row['role_id'] ?>'
                                    )">Edit</button>
                                    

                                    <button class="toggle-btn" onclick="showBlockModal('<?= $row['user_id'] ?>', <?= $row['is_blocked'] ?>)">
  <?= $row['is_blocked'] ? 'Unblock' : 'Block' ?>
</button>


                                    <button class="btn btn-delete" onclick="showDeleteModal('<?= $row['user_id'] ?>')">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-users">No users found in the database</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Deletion</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner"></div>
                    <p>Deleting user...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn btn-cancel" id="cancelBtn">Cancel</button>
                <button class="modal-btn btn-confirm" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit User</h3>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <div class="edit-form-group">
                        <label for="oldUserId">Current User ID</label>
                        <input type="text" id="oldUserId" name="old_user_id" readonly>
                    </div>
                    <div class="edit-form-group">
                        <label for="newUserId">New User ID</label>
                        <input type="text" id="newUserId" name="new_user_id" required>
                    </div>
                    <div class="edit-form-group">
                        <label for="editRole">Role</label>
                        <select id="editRole" name="role_id" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <div class="loading-spinner" id="editLoadingSpinner">
                    <div class="spinner"></div>
                    <p>Updating user...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn btn-cancel" id="editCancelBtn">Cancel</button>
                <button class="modal-btn btn-confirm" id="confirmEditBtn">Save Changes</button>
            </div>
        </div>
    </div>

    <script>
    let currentUserId = '';

    function showDeleteModal(userId) {
        currentUserId = userId;
        document.getElementById('deleteModal').classList.add('active');
    }

    function showEditModal(userId, roleId) {
        currentUserId = userId;
        document.getElementById('oldUserId').value = userId;
        document.getElementById('newUserId').value = userId;
        document.getElementById('editRole').value = roleId;
        document.getElementById('editModal').classList.add('active');
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }

    function hideEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    function showToast(message, isError = false) {
        const toast = document.getElementById('toast');
        toast.innerText = message;
        toast.style.background = isError ? '#e74c3c' : '#2ecc71';
        toast.style.display = 'block';

        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }

    function deleteUser() {
        const loadingSpinner = document.getElementById('loadingSpinner');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        loadingSpinner.style.display = 'block';
        confirmBtn.disabled = true;
        cancelBtn.disabled = true;

        fetch('ViewUsers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'delete_user=true&user_id=' + encodeURIComponent(currentUserId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideDeleteModal();
                showToast('User deleted successfully!');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast('Error: ' + (data.message || 'Failed to delete user'), true);
            }
        })
        .catch(error => {
            showToast('Error: ' + error.message, true);
        })
        .finally(() => {
            loadingSpinner.style.display = 'none';
            confirmBtn.disabled = false;
            cancelBtn.disabled = false;
        });
    }

    function updateUser() {
        const loadingSpinner = document.getElementById('editLoadingSpinner');
        const confirmBtn = document.getElementById('confirmEditBtn');
        const cancelBtn = document.getElementById('editCancelBtn');
        const oldUserId = document.getElementById('oldUserId').value;
        const newUserId = document.getElementById('newUserId').value;
        const roleId = document.getElementById('editRole').value;

        loadingSpinner.style.display = 'block';
        confirmBtn.disabled = true;
        cancelBtn.disabled = true;

        fetch('ViewUsers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'update_user=true&old_user_id=' + encodeURIComponent(oldUserId) + 
                  '&new_user_id=' + encodeURIComponent(newUserId) + 
                  '&role_id=' + encodeURIComponent(roleId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideEditModal();
                showToast('User updated successfully!');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast('Error: ' + (data.message || 'Failed to update user'), true);
            }
        })
        .catch(error => {
            showToast('Error: ' + error.message, true);
        })
        .finally(() => {
            loadingSpinner.style.display = 'none';
            confirmBtn.disabled = false;
            cancelBtn.disabled = false;
        });
    }

    function toggleBlock(userId, isBlocked) {
        if (!confirm(`Are you sure you want to ${isBlocked ? 'unblock' : 'block'} this user?`)) return;

        fetch('ViewUsers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'toggle_block=true&user_id=' + encodeURIComponent(userId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`User ${data.is_blocked ? 'blocked' : 'unblocked'} successfully!`);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast('Error: ' + (data.message || 'Failed to update user status'), true);
            }
        })
        .catch(error => {
            showToast('Error: ' + error.message, true);
        });
    }

    document.getElementById('cancelBtn').addEventListener('click', hideDeleteModal);
    document.getElementById('confirmDeleteBtn').addEventListener('click', deleteUser);
    document.getElementById('editCancelBtn').addEventListener('click', hideEditModal);
    document.getElementById('confirmEditBtn').addEventListener('click', updateUser);

    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) hideDeleteModal();
    });

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) hideEditModal();
    });

    document.getElementById('searchInput').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let found = false;

            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchValue)) {
                    found = true;
                }
            });

            row.style.display = found ? '' : 'none';
        });
    });

  // ======= Toast Message Function =======
  function showToast(message, isError = false) {
    const toast = document.getElementById('toast');
    toast.innerText = message;
    toast.style.backgroundColor = isError ? '#e74c3c' : '#3f0553ff';
    toast.style.display = 'block';
    setTimeout(() => {
      toast.style.display = 'none';
    }, 3000);
  }

  // ======= Block Modal Logic =======
  let selectedUserId = '';
  let selectedBlockStatus = false;

  function showBlockModal(userId, isBlocked) {
    selectedUserId = userId;
    selectedBlockStatus = isBlocked;

    document.getElementById('blockModalTitle').innerText = isBlocked ? 'Unblock User' : 'Block User';
    document.getElementById('blockModalText').innerText = `Are you sure you want to ${isBlocked ? 'unblock' : 'block'} this user?`;

    document.getElementById('blockModal').classList.add('active');
  }

  function hideBlockModal() {
    document.getElementById('blockModal').classList.remove('active');
  }

  function confirmBlockAction() {
    const spinner = document.getElementById('blockLoadingSpinner');
    const confirmBtn = document.getElementById('blockConfirmBtn');
    const cancelBtn = document.getElementById('blockCancelBtn');

    spinner.style.display = 'block';
    confirmBtn.disabled = true;
    cancelBtn.disabled = true;

    fetch('ViewUsers.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'toggle_block=true&user_id=' + encodeURIComponent(selectedUserId)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast(`User ${data.is_blocked ? 'blocked' : 'unblocked'} successfully`);
        hideBlockModal();
        setTimeout(() => window.location.reload(), 1500);
      } else {
        showToast('Failed to update user status', true);
        hideBlockModal();
      }
    })
    .catch(error => {
      showToast('Error: ' + error.message, true);
      hideBlockModal();
    })
    .finally(() => {
      spinner.style.display = 'none';
      confirmBtn.disabled = false;
      cancelBtn.disabled = false;
    });
  }

  // ======= Event Binding =======
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('blockConfirmBtn').addEventListener('click', confirmBlockAction);
    document.getElementById('blockCancelBtn').addEventListener('click', hideBlockModal);
    document.getElementById('blockModal').addEventListener('click', (e) => {
      if (e.target.id === 'blockModal') hideBlockModal();
    });
  });
</script>

</body>
</html>
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

// ==========================
// 🔐 SECURITY
// ==========================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("⛔ Access denied");
}

$branch_id = $_SESSION['branch_id'] ?? 1;

// ==========================
// 👥 FETCH USERS (BRANCH FILTER)
// ==========================
$users = $conn->query("
    SELECT id, name, username, email, role, branch_id 
    FROM users 
    WHERE branch_id = $branch_id
    ORDER BY id DESC
");
?>

<h2>👥 User Management</h2>

<button onclick="openAdd()">➕ Add User</button>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Username</th>
<th>Email</th>
<th>Role</th>
<th>Action</th>
</tr>

<?php while($u = $users->fetch_assoc()): ?>
<tr id="row<?= $u['id'] ?>">
<td><?= $u['id'] ?></td>
<td><?= htmlspecialchars($u['name'] ?? '') ?></td>
<td><?= htmlspecialchars($u['username'] ?? '') ?></td>
<td><?= htmlspecialchars($u['email'] ?? '') ?></td>
<td><?= htmlspecialchars($u['role']) ?></td>

<td>
<button onclick="editUser(<?= $u['id'] ?>)">✏</button>
<button onclick="deleteUser(<?= $u['id'] ?>)">🗑</button>
</td>
</tr>
<?php endwhile; ?>
</table>

<!-- ==========================
➕ ADD MODAL
========================== -->
<div id="addModal" class="modal">
<div class="modal-content">
<h3>Add User</h3>

<input id="name" placeholder="Full Name">
<input id="username" placeholder="Username">
<input id="email" placeholder="Email">
<input id="password" type="password" placeholder="Password">

<select id="role">
<option value="admin">Admin</option>
<option value="manager">Manager</option>
<option value="cashier">Cashier</option>
</select>

<button onclick="addUser()">Save</button>
<button onclick="closeModal()">Close</button>
</div>
</div>

<!-- ==========================
✏ EDIT MODAL
========================== -->
<div id="editModal" class="modal">
<div class="modal-content">
<h3>Edit User</h3>

<input id="edit_id" type="hidden">
<input id="edit_name" placeholder="Name">
<input id="edit_username" placeholder="Username">
<input id="edit_email" placeholder="Email">

<select id="edit_role">
<option value="admin">Admin</option>
<option value="manager">Manager</option>
<option value="cashier">Cashier</option>
</select>

<button onclick="updateUser()">Update</button>
<button onclick="closeModal()">Close</button>
</div>
</div>

<style>
.modal {
display:none;
position:fixed;
top:0; left:0;
width:100%; height:100%;
background:rgba(0,0,0,0.5);
}

.modal-content {
background:white;
padding:20px;
width:320px;
margin:10% auto;
border-radius:8px;
}

table {
width:100%;
border-collapse:collapse;
margin-top:15px;
background:white;
}

td, th {
padding:10px;
border:1px solid #ddd;
}

button {
cursor:pointer;
}
</style>

<script>

// ======================
// MODALS
// ======================
function openAdd(){
    document.getElementById('addModal').style.display='block';
}

function closeModal(){
    document.querySelectorAll('.modal').forEach(m=>m.style.display='none');
}

// ======================
// ADD USER
// ======================
function addUser(){

    let name = document.getElementById('name').value.trim();
    let username = document.getElementById('username').value.trim();
    let email = document.getElementById('email').value.trim();
    let password = document.getElementById('password').value;
    let role = document.getElementById('role').value;

    if(!name || !username || !password){
        alert("⚠️ Name, username and password required");
        return;
    }

    if(password.length < 4){
        alert("⚠️ Password must be at least 4 characters");
        return;
    }

    fetch('/infinity/admin/users/user_api.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({
            action:'add',
            name, username, email, password, role
        })
    })
    .then(r=>r.json())
    .then(d=>{
        alert(d.msg || 'User added');
        location.reload();
    })
    .catch(()=>alert("❌ Failed to add user"));
}

// ======================
// EDIT USER
// ======================
function editUser(id){
    fetch('/infinity/admin/users/user_api.php?action=get&id='+id)
    .then(r=>r.json())
    .then(u=>{

        if(!u.id){
            alert("User not found");
            return;
        }

        document.getElementById('edit_id').value = u.id;
        document.getElementById('edit_name').value = u.name || '';
        document.getElementById('edit_username').value = u.username || '';
        document.getElementById('edit_email').value = u.email || '';
        document.getElementById('edit_role').value = u.role;

        document.getElementById('editModal').style.display='block';
    });
}

// ======================
// UPDATE USER
// ======================
function updateUser(){

    fetch('/infinity/admin/users/user_api.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({
            action:'update',
            id:document.getElementById('edit_id').value,
            name:document.getElementById('edit_name').value,
            username:document.getElementById('edit_username').value,
            email:document.getElementById('edit_email').value,
            role:document.getElementById('edit_role').value
        })
    })
    .then(r=>r.json())
    .then(d=>{
        alert(d.msg || 'Updated');
        location.reload();
    })
    .catch(()=>alert("❌ Update failed"));
}

// ======================
// DELETE USER
// ======================
function deleteUser(id){

    if(!confirm('Delete this user?')) return;

    fetch('/infinity/admin/users/user_api.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({
            action:'delete',
            id:id
        })
    })
    .then(r=>r.json())
    .then(d=>{
        if(d.success){
            document.getElementById('row'+id).remove();
        } else {
            alert(d.error || "Delete failed");
        }
    });
}

</script>
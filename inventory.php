<?php


session_start();

// --- Database Connection ---
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// --- Admin Check by User Type ---
$admin = isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'admin';

// --- Alerts ---
$alert = '';
$req_alert = '';

// --- Handle Add Item ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $item_name = mysqli_real_escape_string($conn, trim($_POST['item_name']));
    $quantity = (int)$_POST['quantity'];
    if ($item_name && $quantity >= 0) {
        $dup_check = mysqli_query($conn, "SELECT id FROM inventory WHERE item_name='$item_name' LIMIT 1");
        if (mysqli_num_rows($dup_check) > 0) {
            $alert = '<div class="alert alert-warning">Item with this name already exists!</div>';
        } else {
            $insert = mysqli_query($conn, "INSERT INTO inventory (item_name, quantity) VALUES ('$item_name', $quantity)");
            if ($insert) {
                $alert = '<div class="alert alert-success">Item added successfully!</div>';
            } else {
                $alert = '<div class="alert alert-danger">Failed to add item.</div>';
            }
        }
    } else {
        $alert = '<div class="alert alert-warning">Please enter valid item name and quantity.</div>';
    }
}

// --- Handle Delete Item ---
if (isset($_GET['delete_item'])) {
    $del_id = (int)$_GET['delete_item'];
    mysqli_query($conn, "DELETE FROM inventory WHERE id=$del_id");
    $alert = '<div class="alert alert-success">Item deleted successfully.</div>';
}

// --- Handle Edit Item ---
if (isset($_POST['edit_item'])) {
    $edit_id = (int)$_POST['edit_id'];
    $edit_name = mysqli_real_escape_string($conn, trim($_POST['edit_name']));
    $edit_qty = (int)$_POST['edit_quantity'];
    $dup_check = mysqli_query($conn, "SELECT id FROM inventory WHERE item_name='$edit_name' AND id!=$edit_id LIMIT 1");
    if (mysqli_num_rows($dup_check) > 0) {
        $alert = '<div class="alert alert-warning">Another item with this name already exists!</div>';
    } else {
        mysqli_query($conn, "UPDATE inventory SET item_name='$edit_name', quantity=$edit_qty WHERE id=$edit_id");
        $alert = '<div class="alert alert-success">Item updated successfully!</div>';
    }
}

// --- Handle Approve/Reject Requisition (Admin Only) ---
if ($admin && isset($_GET['req_action']) && isset($_GET['req_id'])) {
    $req_id = (int)$_GET['req_id'];
    $action = $_GET['req_action'];
    $req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM requisitions WHERE id=$req_id LIMIT 1"));
    if ($req && $req['status'] === 'Pending') {
        if ($action === 'approve') {
            // Check if enough inventory
            $item_id = (int)$req['item_id'];
            $qty_needed = (int)$req['quantity'];
            $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT quantity FROM inventory WHERE id=$item_id LIMIT 1"));
            if ($item && $item['quantity'] >= $qty_needed) {
                // Deduct from inventory
                mysqli_query($conn, "UPDATE inventory SET quantity=quantity-$qty_needed WHERE id=$item_id");
                // Update requisition status
                mysqli_query($conn, "UPDATE requisitions SET status='Approved' WHERE id=$req_id");
                $req_alert = '<div class="alert alert-success">Requisition approved and inventory updated.</div>';
            } else {
                $req_alert = '<div class="alert alert-danger">Not enough inventory to approve this requisition.</div>';
            }
        } elseif ($action === 'reject') {
            mysqli_query($conn, "UPDATE requisitions SET status='Rejected' WHERE id=$req_id");
            $req_alert = '<div class="alert alert-warning">Requisition rejected.</div>';
        }
    }
}

// --- Fetch Inventory Items ---
$inventory = [];
$inv_query = mysqli_query($conn, "SELECT * FROM inventory ORDER BY item_name ASC");
while ($row = mysqli_fetch_assoc($inv_query)) {
    $inventory[] = $row;
}

// --- Fetch All Requisitions for Admin ---
$all_requisitions = [];
if ($admin) {
    $all_query = mysqli_query($conn, "SELECT * FROM requisitions ORDER BY requested_at DESC");
    while ($row = mysqli_fetch_assoc($all_query)) {
        $all_requisitions[] = $row;
    }
}

// For edit modal
$edit_item = null;
if (isset($_GET['edit_item'])) {
    $edit_id = (int)$_GET['edit_item'];
    $edit_item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM inventory WHERE id=$edit_id LIMIT 1"));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; margin: 0; }
        .dashboard-flex {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100vw;
            min-height: 100vh;
            background: #f7f7fa;
        }
        .admin-sidebar {
            position: relative;
            top: 0;
            left: 0;
            height: 100vh;
            min-width: 200px;
            max-width: 240px;
            background: #0e7490;
            color: #fff;
            border-radius: 0 10px 10px 0;
            box-shadow: 2px 0 8px #e0e0e0;
            z-index: 10;
            padding-top: 30px;
            padding-bottom: 30px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: transform 0.3s;
        }
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }
        .admin-sidebar li {
            width: 100%;
        }
        .admin-sidebar a {
            display: flex;
            align-items: center;
            color: #fff;
            padding: 12px 24px;
            text-decoration: none;
            transition: background 0.2s;
            width: 100%;
            font-size: 1em;
        }
        .admin-sidebar a i {
            margin-right: 10px;
        }
        .admin-sidebar a:hover, .admin-sidebar .active > a {
            background: #155e75;
            color: #fff;
        }
        .sidebar-header {
            font-size: 1.2em;
            font-weight: bold;
            padding: 0 24px 18px 24px;
            margin-bottom: 10px;
            width: 100%;
            border-bottom: 1px solid #38bdf8;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .main-container {
            max-width: 750px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ddd;
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        header.header {
            width: 100%;
            background: #0e7490;
            box-shadow: 0 2px 8px #e0e0e0;
            padding: 18px 30px 18px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 20;
        }
        .dashboard-title {
            font-size: 1.4em;
            font-weight: bold;
            color: #fff;
            margin-left: 10px;
        }
        .logout {
            margin-left: auto;
        }
        .table th, .table td { vertical-align: middle !important; }
        .action-btns a, .action-btns form { display: inline-block; margin-right: 8px; }
        .alert { margin-bottom: 20px; }
        @media (max-width: 900px) {
            .dashboard-flex { flex-direction: column; }
            .admin-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                transform: translateX(-100%);
                width: 220px;
                max-width: 90vw;
                box-shadow: 2px 0 8px #e0e0e0;
                background: #0e7490;
                z-index: 100;
            }
            .admin-sidebar.show {
                transform: translateX(0);
            }
            .main-container { margin: 20px auto 0 auto; }
        }
        @media (max-width: 800px) {
            .main-container { padding: 10px; }
            .table-responsive { font-size: 13px; }
        }
        @media (max-width: 600px) {
            .admin-sidebar {
                width: 90vw;
                min-width: unset;
                max-width: unset;
                padding-top: 10px;
                padding-bottom: 10px;
            }
            .sidebar-header {
                font-size: 1em;
                padding: 0 12px 10px 12px;
            }
            .admin-sidebar a {
                padding: 10px 12px;
                font-size: 0.98em;
            }
            .main-container { padding: 10px 0; }
        }
    </style>
    <script>
        function toggleRequests() {
            var sec = document.getElementById('requests-section');
            sec.style.display = (sec.style.display === 'none' || sec.style.display === '') ? 'block' : 'none';
        }
        function toggleAllStatus() {
            var sec = document.getElementById('status-section');
            sec.style.display = (sec.style.display === 'none' || sec.style.display === '') ? 'block' : 'none';
        }
        function showEditForm(id, name, qty) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_quantity').value = qty;
        }
        function hideEditForm() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</head>
<body>
<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
       <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-archive"></i>
        <span style="margin-left: 8px;">Inventory</span>
    </span>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>
<div class="dashboard-flex">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i> Admin Panel
        </div>
        <ul>
            <li class="active"><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
        </ul>
    </nav>
    <div class="main-container">
        <?php if ($alert) echo $alert; ?>
        <?php if ($req_alert) echo $req_alert; ?>

        <!-- Add New Item Form -->
        <form method="post" class="well" style="margin-bottom:30px;">
            <h4><i class="fa fa-plus-circle"></i> Add New Item</h4>
            <div class="form-group">
                <label for="item_name">Item Name</label>
                <input type="text" name="item_name" id="item_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" min="0" required>
            </div>
            <button type="submit" name="add_item" class="btn btn-primary"><i class="fa fa-plus"></i> Add Item</button>
        </form>

        <!-- Edit Item Modal -->
        <div id="editModal" style="display:<?php echo $edit_item ? 'block' : 'none'; ?>; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999;">
            <div style="background:#fff; max-width:400px; margin:60px auto; padding:30px; border-radius:8px; position:relative;">
                <form method="post">
                    <input type="hidden" name="edit_id" id="edit_id" value="<?php echo $edit_item ? $edit_item['id'] : ''; ?>">
                    <div class="form-group">
                        <label for="edit_name">Item Name</label>
                        <input type="text" name="edit_name" id="edit_name" class="form-control" value="<?php echo $edit_item ? htmlspecialchars($edit_item['item_name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_quantity">Quantity</label>
                        <input type="number" name="edit_quantity" id="edit_quantity" class="form-control" min="0" value="<?php echo $edit_item ? (int)$edit_item['quantity'] : ''; ?>" required>
                    </div>
                    <button type="submit" name="edit_item" class="btn btn-success"><i class="fa fa-save"></i> Save Changes</button>
                    <button type="button" class="btn btn-default" onclick="hideEditForm()">Cancel</button>
                </form>
            </div>
        </div>

        <!-- View Requests Button (Admin Only) -->
        <?php if ($admin): ?>
            <button class="btn btn-info" style="margin-bottom:10px;" onclick="toggleRequests()">
                <i class="fa fa-user-shield"></i> View Pending Requests
            </button>
            <button class="btn btn-default" style="margin-bottom:20px;" onclick="toggleAllStatus()">
                <i class="fa fa-tasks"></i> View All Requisition Status
            </button>
        <?php endif; ?>

        <!-- Pending Requisitions (Admin Only) -->
        <div id="requests-section" style="display:none;">
        <?php
        $pending_requisitions = array_filter($all_requisitions, function($r) { return $r['status'] === 'Pending'; });
        ?>
        <?php if ($admin && count($pending_requisitions) > 0): ?>
            <h4><i class="fa fa-user-shield"></i> Pending Requisitions (Approve or Reject)</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Reason</th>
                            <th>Requested By</th>
                            <th>Requested At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pending_requisitions as $req): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['item_name']); ?></td>
                            <td><?php echo (int)$req['quantity']; ?></td>
                            <td><?php echo htmlspecialchars($req['reason']); ?></td>
                            <td><?php echo htmlspecialchars($req['requested_by']); ?></td>
                            <td><?php echo htmlspecialchars($req['requested_at']); ?></td>
                            <td>
                                <a href="inventory.php?req_action=approve&req_id=<?php echo $req['id']; ?>" class="btn btn-xs btn-success" onclick="return confirm('Approve this requisition?');">
                                    <i class="fa fa-check"></i> Approve
                                </a>
                                <a href="inventory.php?req_action=reject&req_id=<?php echo $req['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Reject this requisition?');">
                                    <i class="fa fa-times"></i> Reject
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <hr>
        <?php elseif ($admin): ?>
            <div class="alert alert-info">No pending requests at the moment.</div>
        <?php endif; ?>
        </div>

        <!-- All Requisitions Status (Admin Only) -->
        <div id="status-section" style="display:none;">
        <?php if ($admin && count($all_requisitions) > 0): ?>
            <h4><i class="fa fa-tasks"></i> All Requisitions Status</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th>Requested At</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($all_requisitions as $req): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['item_name']); ?></td>
                            <td><?php echo (int)$req['quantity']; ?></td>
                            <td><?php echo htmlspecialchars($req['reason']); ?></td>
                            <td>
                                <?php
                                    if ($req['status'] === 'Pending') echo '<span class="status-pending">Pending</span>';
                                    elseif ($req['status'] === 'Approved') echo '<span class="status-approved">Approved</span>';
                                    elseif ($req['status'] === 'Rejected') echo '<span class="status-rejected">Rejected</span>';
                                    else echo htmlspecialchars($req['status']);
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($req['requested_by']); ?></td>
                            <td><?php echo htmlspecialchars($req['requested_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <hr>
        <?php elseif ($admin): ?>
            <div class="alert alert-info">No requisitions found.</div>
        <?php endif; ?>
        </div>

        <!-- Inventory List -->
        <h4><i class="fa fa-list"></i> Inventory List</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo (int)$item['quantity']; ?></td>
                        <td class="action-btns">
                            <a href="#" class="btn btn-xs btn-warning" onclick="showEditForm('<?php echo $item['id']; ?>', '<?php echo htmlspecialchars($item['item_name'], ENT_QUOTES); ?>', '<?php echo (int)$item['quantity']; ?>'); return false;">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <a href="inventory.php?delete_item=<?php echo $item['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this item?');">
                                <i class="fa fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sidebarToggle = document.getElementById('sidebarToggle');
    var adminSidebar = document.getElementById('adminSidebar');
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            adminSidebar.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 900 &&
                !adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                adminSidebar.classList.remove('show');
            }
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 900) {
                adminSidebar.classList.remove('show');
            }
        });
    }
});
</script>
</body>
</html>
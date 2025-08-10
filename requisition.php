<?php


session_start();
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Fetch inventory items
$inventory = [];
$inv_query = mysqli_query($conn, "SELECT id, item_name, quantity FROM inventory ORDER BY item_name ASC");
while ($row = mysqli_fetch_assoc($inv_query)) {
    $inventory[] = $row;
}

// Handle delete request
$alert = '';
if (isset($_GET['delete_req'])) {
    $delete_id = intval($_GET['delete_req']);
    $user = isset($_SESSION['username']) ? mysqli_real_escape_string($conn, $_SESSION['username']) : '';
    // Only allow delete if request is still pending and belongs to the user
    $check = mysqli_query($conn, "SELECT * FROM requisitions WHERE id=$delete_id AND requested_by='$user' AND status='Pending'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM requisitions WHERE id=$delete_id");
        $alert = '<div class="alert alert-success">Request deleted successfully.</div>';
    } else {
        $alert = '<div class="alert alert-danger">Cannot delete this request.</div>';
    }
}

// Handle requisition submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['requisition'])) {
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    // Use username from session as requested_by
    $requested_by = isset($_SESSION['username']) ? mysqli_real_escape_string($conn, $_SESSION['username']) : 'Unknown';

    // Check available quantity
    $item_check = mysqli_query($conn, "SELECT item_name, quantity FROM inventory WHERE id=$item_id LIMIT 1");
    $item = mysqli_fetch_assoc($item_check);
    if (!$item) {
        $alert = '<div class="alert alert-danger">Invalid item selected.</div>';
    } elseif ($quantity < 1) {
        $alert = '<div class="alert alert-warning">Quantity must be at least 1.</div>';
    } elseif ($quantity > $item['quantity']) {
        $alert = '<div class="alert alert-warning">Requested quantity exceeds available stock.</div>';
    } else {
        // Insert requisition (pending approval)
        $insert = mysqli_query($conn, "INSERT INTO requisitions (item_id, item_name, quantity, reason, requested_by, status, requested_at) VALUES (
            $item_id,
            '" . mysqli_real_escape_string($conn, $item['item_name']) . "',
            $quantity,
            '$reason',
            '$requested_by',
            'Pending',
            NOW()
        )");
        if ($insert) {
            $alert = '<div class="alert alert-success">Requisition sent for approval!</div>';
        } else {
            $alert = '<div class="alert alert-danger">Failed to send requisition.</div>';
        }
    }
}

// Fetch user's previous requisitions (if logged in)
$user_requisitions = [];
if (isset($_SESSION['username'])) {
    $user = mysqli_real_escape_string($conn, $_SESSION['username']);
    $req_query = mysqli_query($conn, "SELECT * FROM requisitions WHERE requested_by='$user' ORDER BY requested_at DESC LIMIT 10");
    while ($row = mysqli_fetch_assoc($req_query)) {
        $user_requisitions[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory Requisition</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ddd; padding: 30px; }
        .header { background: #007bff; color: #fff; padding: 18px 30px; border-radius: 8px 8px 0 0; margin-bottom: 30px; }
        .header h2 { margin: 0; font-size: 2em; }
        .form-group label { font-weight: 600; }
        .table th, .table td { vertical-align: middle !important; }
        .status-pending { color: #ff9800; font-weight: bold; }
        .status-approved { color: #28a745; font-weight: bold; }
        .status-rejected { color: #dc3545; font-weight: bold; }
        .delete-btn { color: #dc3545; }
        @media (max-width: 700px) {
            .main-container { padding: 10px; }
        }
    </style>
</head>
<body>
<div class="header text-center">
    <h2><i class="fa fa-clipboard-list"></i> Inventory Requisition</h2>
</div>
<div class="main-container">
    <?php if ($alert) echo $alert; ?>
    <form method="post" class="well" style="margin-bottom:30px;">
        <h4><i class="fa fa-box"></i> Request Item</h4>
        <div class="form-group">
            <label for="item_id">Select Item</label>
            <select name="item_id" id="item_id" class="form-control" required>
                <option value="">-- Select Item --</option>
                <?php foreach ($inventory as $item): ?>
                    <option value="<?php echo $item['id']; ?>">
                        <?php echo htmlspecialchars($item['item_name']); ?> (Available: <?php echo $item['quantity']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity Needed</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
        </div>
        <div class="form-group">
            <label for="reason">Reason / Purpose</label>
            <textarea name="reason" id="reason" class="form-control" rows="2" required></textarea>
        </div>
        <div class="form-group">
            <label for="requested_by">Requested By</label>
            <input type="text" class="form-control" value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>" readonly>
        </div>
        <button type="submit" name="requisition" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Send Request</button>
    </form>

    <h4><i class="fa fa-history"></i> My Recent Requisitions</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Requested At</th>
                    <th>Requested By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($user_requisitions) > 0): ?>
                    <?php foreach ($user_requisitions as $req): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['item_name']); ?></td>
                            <td><?php echo (int)$req['quantity']; ?></td>
                            <td><?php echo htmlspecialchars($req['reason']); ?></td>
                            <td>
                                <?php
                                if ($req['status'] == 'Pending') echo '<span class="status-pending">Pending</span>';
                                elseif ($req['status'] == 'Approved') echo '<span class="status-approved">Approved</span>';
                                else echo '<span class="status-rejected">Rejected</span>';
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($req['requested_at']); ?></td>
                            <td><?php echo htmlspecialchars($req['requested_by']); ?></td>
                            <td>
                                <?php if ($req['status'] == 'Pending'): ?>
                                    <a href="requisition.php?delete_req=<?php echo $req['id']; ?>" class="btn btn-xs btn-danger delete-btn" onclick="return confirm('Delete this request?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">No requisitions found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top:20px;">
        <small class="text-muted"><i class="fa fa-info-circle"></i> All requests must be approved by the administrator before collection.</small>
    </div>
</div>
</body>
</html>
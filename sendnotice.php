<?php


session_start();
$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Handle form submission
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notice'])) {
    $recipient_type = $_POST['recipient_type'];
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $date_sent = date('Y-m-d H:i:s');

    $sql = "INSERT INTO announcements (recipient_type, subject, message, date_sent) VALUES ('$recipient_type', '$subject', '$message', '$date_sent')";
    if (mysqli_query($conn, $sql)) {
        $alert = '<div class="alert alert-success">Announcement sent successfully!</div>';
    } else {
        $alert = '<div class="alert alert-danger">Failed to send announcement.</div>';
    }
}

// Handle delete announcement
if (isset($_GET['delete_announcement'])) {
    $del_id = (int)$_GET['delete_announcement'];
    mysqli_query($conn, "DELETE FROM announcements WHERE id=$del_id");
    // Optionally delete reads
    mysqli_query($conn, "DELETE FROM announcement_reads WHERE announcement_id=$del_id");
    $alert = '<div class="alert alert-success">Announcement deleted.</div>';
}

// Handle edit announcement
if (isset($_POST['edit_announcement'])) {
    $edit_id = (int)$_POST['edit_id'];
    $edit_subject = mysqli_real_escape_string($conn, $_POST['edit_subject']);
    $edit_message = mysqli_real_escape_string($conn, $_POST['edit_message']);
    mysqli_query($conn, "UPDATE announcements SET subject='$edit_subject', message='$edit_message' WHERE id=$edit_id");
    $alert = '<div class="alert alert-success">Announcement updated.</div>';
}

// Fetch all announcements for modal
$all_announcements = [];
$res = mysqli_query($conn, "SELECT * FROM announcements ORDER BY date_sent DESC");
while ($row = mysqli_fetch_assoc($res)) {
    $all_announcements[] = $row;
}

// Fetch users who have read a specific announcement (AJAX)
if (isset($_GET['readers']) && isset($_GET['announcement_id'])) {
    $aid = (int)$_GET['announcement_id'];
    $readers = [];
    $rres = mysqli_query($conn, "SELECT t.name, r.read_at FROM announcement_reads r LEFT JOIN stafftb t ON r.teacher_id = t.staffid WHERE r.announcement_id = $aid");
    while ($r = mysqli_fetch_assoc($rres)) {
        $readers[] = $r;
    }
    header('Content-Type: application/json');
    echo json_encode($readers);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Send Announcement</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #f7f7fa; }
        .main-flex-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100vw;
            min-height: 100vh;
        }
        .main-container {
            max-width: 500px;
            width: 100%;
            margin: 60px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px #e0e0e0;
            padding: 30px 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2 { color: #0e7490; font-weight: 700; margin-bottom: 24px; }
        .form-group label { font-weight: 600; }
        .btn-primary { background: #0e7490; border: none; }
        .btn-primary:hover { background: #155e75; }
        .alert { margin-top: 18px; }
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100vw; height: 100vh; overflow: auto; background: rgba(0,0,0,0.3);}
        .modal-content { background: #fff; margin: 60px auto; padding: 24px 20px; border-radius: 8px; max-width: 600px; position: relative; }
        .close { position: absolute; right: 18px; top: 10px; font-size: 28px; font-weight: bold; color: #888; cursor: pointer; }
        .close:hover { color: #e11d48; }
        .btn-group-custom { margin-top: 18px; }
        .btn-group-custom .btn { margin-right: 10px; }
        .edit-form { margin-bottom: 0; }
        .edit-form input, .edit-form textarea { margin-bottom: 10px; }
        .edit-form .btn { margin-right: 8px; }
        @media (max-width: 900px) {
            .main-flex-container {
                flex-direction: column;
                align-items: stretch;
            }
            .main-container {
                margin: 20px auto;
                padding: 10px 2vw;
            }
            .admin-sidebar {
                position: static;
                width: 100vw;
                max-width: 100vw;
                height: auto;
                border-radius: 0;
                box-shadow: none;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
<link rel="stylesheet" href="admin-panel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<header class="header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle admin panel">
        <i class="fa fa-bars"></i>
    </button>
    <span class="dashboard-title"><i class="fa fa-gauge"></i> Administrator Dashboard</span>
    <div class="logout">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>
<div class="main-flex-container">
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i> Admin Panel
        </div>
        <ul>
            <li class="active"><a href="adminhome.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="viewnotice.php"><i class="fa fa-bullhorn"></i> View Notices</a></li>
        </ul>
    </nav>
    <div class="main-container">
        <h2><i class="fa fa-bullhorn"></i> Send Announcement</h2>
        <?php if ($alert) echo $alert; ?>
        <form method="post">
            <div class="form-group">
                <label for="recipient_type">Send To:</label>
                <select name="recipient_type" id="recipient_type" class="form-control" required>
                    <option value="">Select Recipient</option>
                    <option value="teachers">Teachers</option>
                    <option value="parents">Parents</option>
                    <option value="all">All (Teachers & Parents)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" name="subject" id="subject" class="form-control" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea name="message" id="message" class="form-control" rows="5" required maxlength="1000"></textarea>
            </div>
            <button type="submit" name="send_notice" class="btn btn-primary">
                <i class="fa fa-paper-plane"></i> Send Announcement
            </button>
        </form>
        <div class="btn-group-custom">
            <button class="btn btn-info" onclick="showAllAnnouncements()">
                <i class="fa fa-list"></i> View All Sent Announcements
            </button>
        </div>
    </div>
</div>

<!-- Modal for All Announcements -->
<div id="allAnnouncementsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAllAnnouncements()">&times;</span>
        <h4><i class="fa fa-list"></i> All Sent Announcements</h4>
        <div id="allAnnouncementsContent">
            <?php if (count($all_announcements) > 0): ?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Recipient</th>
                            <th>Date Sent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($all_announcements as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['subject']); ?></td>
                            <td>
                                <?php
                                    if ($a['recipient_type'] == 'teachers') echo 'Teachers';
                                    elseif ($a['recipient_type'] == 'parents') echo 'Parents';
                                    else echo 'All';
                                ?>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($a['date_sent'])); ?></td>
                            <td>
                                <button class="btn btn-xs btn-success" onclick="showReaders(<?php echo $a['id']; ?>)">
                                    <i class="fa fa-eye"></i> Readers
                                </button>
                                <button class="btn btn-xs btn-warning" onclick="showEditForm(<?php echo $a['id']; ?>, '<?php echo htmlspecialchars(addslashes($a['subject'])); ?>', '<?php echo htmlspecialchars(addslashes($a['message'])); ?>')">
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                                <a href="?delete_announcement=<?php echo $a['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this announcement?');">
                                    <i class="fa fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No announcements found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for Readers -->
<div id="readersModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeReaders()">&times;</span>
        <h4><i class="fa fa-eye"></i> Users Who Have Read This Announcement</h4>
        <div id="readersContent" style="min-height:60px;">
            <div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
        </div>
    </div>
</div>

<!-- Modal for Edit -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditForm()">&times;</span>
        <h4><i class="fa fa-edit"></i> Edit Announcement</h4>
        <form method="post" class="edit-form">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-group">
                <label for="edit_subject">Subject:</label>
                <input type="text" name="edit_subject" id="edit_subject" class="form-control" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="edit_message">Message:</label>
                <textarea name="edit_message" id="edit_message" class="form-control" rows="4" required maxlength="1000"></textarea>
            </div>
            <button type="submit" name="edit_announcement" class="btn btn-success"><i class="fa fa-save"></i> Save Changes</button>
            <button type="button" class="btn btn-default" onclick="closeEditForm()">Cancel</button>
        </form>
    </div>
</div>

<script>
function showAllAnnouncements() {
    document.getElementById('allAnnouncementsModal').style.display = 'block';
}
function closeAllAnnouncements() {
    document.getElementById('allAnnouncementsModal').style.display = 'none';
}
function showReaders(aid) {
    document.getElementById('readersModal').style.display = 'block';
    var readersContent = document.getElementById('readersContent');
    readersContent.innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
    fetch('sendnotice.php?readers=1&announcement_id=' + aid)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                let html = '<ul class="list-group">';
                data.forEach(function(r) {
                    html += '<li class="list-group-item">' + (r.name ? r.name : 'Unknown') + ' <span class="text-muted" style="font-size:12px;">(' + r.read_at + ')</span></li>';
                });
                html += '</ul>';
                readersContent.innerHTML = html;
            } else {
                readersContent.innerHTML = '<div class="alert alert-info">No users have read this announcement yet.</div>';
            }
        })
        .catch(() => {
            readersContent.innerHTML = '<div class="alert alert-danger">Failed to load readers.</div>';
        });
}
function closeReaders() {
    document.getElementById('readersModal').style.display = 'none';
}
function showEditForm(id, subject, message) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_subject').value = subject.replace(/\\'/g, "'");
    document.getElementById('edit_message').value = message.replace(/\\'/g, "'");
    document.getElementById('editModal').style.display = 'block';
}
function closeEditForm() {
    document.getElementById('editModal').style.display = 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    var sidebarToggle = document.getElementById('sidebarToggle');
    var adminSidebar = document.getElementById('adminSidebar');
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            adminSidebar.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1000 &&
                !adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                adminSidebar.classList.remove('show');
            }
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1000) {
                adminSidebar.classList.remove('show');
            }
        });
    }
    // Modal close on click outside
    document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.style.display = 'none';
        });
    });
});
</script>
</body>
</html>
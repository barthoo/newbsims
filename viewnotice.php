<?php


$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Simulate teacher login (replace with your session logic)
$teacher_id = isset($_SESSION['teacher_id']) ? $_SESSION['teacher_id'] : 1; // Example: teacher_id=1

// Fetch all announcements, latest first
$announcements = [];
$res = mysqli_query($conn, "SELECT * FROM announcements ORDER BY date_sent DESC");
while ($row = mysqli_fetch_assoc($res)) {
    $announcements[] = $row;
}

// Fetch unread announcements for this teacher
$unread_count = 0;
$unread_ids = [];
if ($teacher_id) {
    // Announcements for teachers or all
    $unread_sql = "
        SELECT a.id
        FROM announcements a
        LEFT JOIN announcement_reads r
            ON a.id = r.announcement_id AND r.teacher_id = $teacher_id
        WHERE (a.recipient_type = 'teachers' OR a.recipient_type = 'all')
          AND r.id IS NULL
    ";
    $unread_res = mysqli_query($conn, $unread_sql);
    while ($row = mysqli_fetch_assoc($unread_res)) {
        $unread_ids[] = $row['id'];
    }
    $unread_count = count($unread_ids);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Announcements</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #f7f7fa; }
        .main-container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px #e0e0e0;
            padding: 30px 24px;
        }
        h2 { color: #0e7490; font-weight: 700; margin-bottom: 24px; }
        .notice {
            border-left: 5px solid #0e7490;
            background: #f1f5f9;
            margin-bottom: 24px;
            padding: 18px 18px 10px 18px;
            border-radius: 6px;
            box-shadow: 0 1px 4px #e0e0e0;
        }
        .notice .meta {
            font-size: 13px;
            color: #555;
            margin-bottom: 8px;
        }
        .notice .subject {
            font-size: 1.1em;
            font-weight: bold;
            color: #0e7490;
        }
        .notice .message {
            margin-top: 8px;
            font-size: 1em;
            color: #222;
        }
        .notice .recipient {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        .bell-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        .fa-bell {
            font-size: 2em;
            color: #f59e42;
            transition: color 0.2s;
        }
        .fa-bell.shake {
            animation: shake-bell 0.7s cubic-bezier(.36,.07,.19,.97) both infinite;
        }
        @keyframes shake-bell {
            10%, 90% { transform: translateX(-2px); }
            20%, 80% { transform: translateX(4px); }
            30%, 50%, 70% { transform: translateX(-8px); }
            40%, 60% { transform: translateX(8px); }
        }
        .bell-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e11d48;
            color: #fff;
            font-size: 13px;
            font-weight: bold;
            border-radius: 50%;
            padding: 2px 7px;
            min-width: 24px;
            text-align: center;
            border: 2px solid #fff;
            box-shadow: 0 1px 4px #e0e0e0;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="bell-container" title="Unread Announcements">
        <i class="fa fa-bell<?php echo $unread_count > 0 ? ' shake' : ''; ?>"></i>
        <?php if ($unread_count > 0): ?>
            <span class="bell-badge"><?php echo $unread_count; ?></span>
        <?php endif; ?>
    </div>
    <h2><i class="fa fa-bullhorn"></i> Announcements</h2>
    <?php if (count($announcements) > 0): ?>
        <?php foreach ($announcements as $a): ?>
            <div class="notice<?php echo (in_array($a['id'], $unread_ids)) ? ' bg-warning' : ''; ?>">
                <div class="meta">
                    <span class="subject"><?php echo htmlspecialchars($a['subject']); ?></span>
                    <span style="float:right;">
                        <?php echo date('M d, Y H:i', strtotime($a['date_sent'])); ?>
                    </span>
                </div>
                <div class="message"><?php echo nl2br(htmlspecialchars($a['message'])); ?></div>
                <div class="recipient">
                    For: 
                    <?php
                        if ($a['recipient_type'] == 'teachers') echo 'Teachers';
                        elseif ($a['recipient_type'] == 'parents') echo 'Parents';
                        else echo 'All (Teachers & Parents)';
                    ?>
                    <?php if (in_array($a['id'], $unread_ids)): ?>
                        <span class="label label-warning" style="margin-left:10px;">Unread</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No announcements found.</div>
    <?php endif; ?>
</div>
</body>
</html>
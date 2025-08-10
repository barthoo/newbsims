<?php


session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");

// Handle book recommendation submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recommend_book'])) {
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $book_title = mysqli_real_escape_string($conn, $_POST['book_title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    mysqli_query($conn, "INSERT INTO recommended_books (class, book_title, author, description) VALUES ('$class', '$book_title', '$author', '$description')");
    $msg = "Book recommended successfully!";
}

// Fetch all classes
$classes = [];
$class_result = mysqli_query($conn, "SELECT DISTINCT class FROM students ORDER BY class ASC");
while ($row = mysqli_fetch_assoc($class_result)) {
    $classes[] = $row['class'];
}


$selected_class = isset($_GET['class']) ? mysqli_real_escape_string($conn, $_GET['class']) : '';
$where = $selected_class ? "WHERE class='$selected_class'" : '';
$books = [];
$books_result = mysqli_query($conn, "SELECT * FROM recommended_books $where ORDER BY class, book_title ASC");
while ($row = mysqli_fetch_assoc($books_result)) {
    $books[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recommended Books for Students</title>
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
        .admin-sidebar {
            background: #0e7490;
            color: #fff;
            border-radius: 0 10px 10px 0;
            box-shadow: 2px 0 8px #e0e0e0;
            margin: 0;
            padding-top: 30px;
            padding-bottom: 30px;
            min-width: 200px;
            max-width: 240px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
            z-index: 10;
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
            max-width: 1000px;
            width: 100%;
            margin: 40px auto 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ddd;
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2 { color: #0e7490; }
        .msg { color: #16a34a; margin-bottom: 10px; }
        .section { margin-bottom: 40px; }
        th { background: #0e7490; color: #fff; }
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
        @media (max-width: 700px) {
            .main-container { padding: 10px; }
            table, thead, tbody, th, td, tr { display: block; }
            th, td { padding: 8px 0; }
            thead { display: none; }
            tr { margin-bottom: 15px; border-bottom: 1px solid #eee; }
            td:before {
                content: attr(data-label);
                font-weight: bold;
                display: block;
                color: #0e7490;
                margin-bottom: 2px;
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
       <span class="dashboard-title" style="margin: 0 auto; display: flex; align-items: center; justify-content: center; flex: 1; text-align: center;">
        <i class="fa fa-book"></i>
        <span style="margin-left: 8px;">Recommended Books</span>
    </span>>
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
            <li><a href="view_students_assessments.php"><i class="fa fa-book"></i> Student Reports</a></li>
        </ul>
    </nav>
    <div class="main-container">
        <h2>Recommended Books for Students</h2>

        <!-- Book Recommendation Form -->
        <div class="section">
            <h4>Recommend a Book</h4>
            <?php if ($msg): ?>
                <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>
            <form method="post" class="form-inline">
                <label>Class:
                    <select name="class" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Book Title:
                    <input type="text" name="book_title" class="form-control" required>
                </label>
                <label>Author:
                    <input type="text" name="author" class="form-control" required>
                </label>
                <label>Description:
                    <input type="text" name="description" class="form-control" placeholder="Optional">
                </label>
                <button type="submit" name="recommend_book" class="btn btn-primary">Recommend</button>
            </form>
        </div>

        <!-- Filter by Class -->
        <div class="section">
            <form method="get" class="form-inline" style="margin-bottom:15px;">
                <label>Filter by Class:
                    <select name="class" class="form-control" onchange="this.form.submit()">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>" <?php if ($selected_class == $class) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($class); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($books) > 0): ?>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td data-label="Class"><?php echo htmlspecialchars($book['class']); ?></td>
                            <td data-label="Book Title"><?php echo htmlspecialchars($book['book_title']); ?></td>
                            <td data-label="Author"><?php echo htmlspecialchars($book['author']); ?></td>
                            <td data-label="Description"><?php echo htmlspecialchars($book['description']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No recommended books found.</td>
                    </tr>
                <?php endif; ?>
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
});
</script>
</body>
</html>
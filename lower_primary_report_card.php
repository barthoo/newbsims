<?php


session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
} elseif ($_SESSION['username'] == 'student') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "bsmsdb");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lower Primary Terminal Report</title>
  <link rel="stylesheet" href="report_card.css">
</head>
<body>
  <div class="report">
    <header>
      <header>
      <img src="logo.png" alt="School Logo" class="logo">
      <h1>GHANA EDUCATION SERVICE</h1>
      <h2>BASIC SCHOOL INFORMATION MANAGEMENT SYSTEM</h2>
      <p>THE INVINCIBLE TEAM</p>
      <p>TEL: 0530770774 WHATSAPP: 0246049420</p>
      <div class="title-bar">LOWER PRIMARY TERMINAL REPORT</div>
    </header>

    <section class="details">
      <p><strong>NAME :</strong> .......................................................... <strong>YEAR:</strong> 2025</p>
      <p><strong>CLASS:</strong> ................................... <strong>TERM: THREE(3)</strong> .....................................</p>
      <p><strong>NO. ON ROLL:</strong> ..................... <strong>POSITION:</strong> ..................................................</p>
      <p><strong>DATE:</strong> 24<sup>th</sup> July, 2025 ............ <strong>NEXT TERM BEGINS:</strong> 9<sup>th</sup> September, 2025</p>
    </section>

    <table>
      <thead>
        <tr>
          <th>SUBJECT</th>
          <th>CLASS SCORE<br>50%</th>
          <th>EXAMS SCORE<br>50%</th>
          <th>TOTAL SCORE<br>100%</th>
          <th>GRADE IN SUBJECT</th>
          <th>REMARKS & AREAS OF STRENGTHS<br>AND WEAKNESS</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>ENGLISH LANGUAGE</td><td></td><td></td><td></td><td>GR:</td><td>90-100 Grade 1 = Highest</td></tr>
        <tr><td>MATHEMATICS</td><td></td><td></td><td></td><td>GR:</td><td>80-89 Grade 2 = Higher</td></tr>
        <tr><td>SCIENCE</td><td></td><td></td><td></td><td>GR:</td><td>70-79 Grade 3 = High</td></tr>
        <tr><td>RME</td><td></td><td></td><td></td><td>GR:</td><td>60-69 Grade 4 = High Average</td></tr>
        <tr><td>CREATIVE ARTS</td><td></td><td></td><td></td><td>GR:</td><td>55-59 Grade 5 = Average</td></tr>
        <tr><td>HISTORY</td><td></td><td></td><td></td><td>GR:</td><td>50-54 Grade 6 = Low Average</td></tr>
        <tr><td>ASANTE TWI</td><td></td><td></td><td></td><td>GR:</td><td>40-49 Grade 7 = Low</td></tr>
        <tr><td>COMPUTING</td><td></td><td></td><td></td><td>GR:</td><td>35-39 Grade 8 = Lower</td></tr>
        <tr><td>PHYSICAL EDU.</td><td></td><td></td><td></td><td>GR:</td><td>0-39 Grade 9 = Lowest</td></tr>
      </tbody>
    </table>

    <section class="footer-section">
      <p>Attendance .................................................... Out Of .................... Out Of .................... Promoted To ....................</p>
      <p>Interest: ................................................................................................. Repeated in ....................</p>
      <p>Conduct: ................................................................................................................</p>
      <p>Attitude: ...............................................................................

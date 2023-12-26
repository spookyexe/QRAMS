<?php
session_start();
include 'db_conn.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
    // Function to get students from database by gender
    function getStudentsByGender($connect, $gradeLevel, $section, $gender)
    {
        $stmt = $connect->prepare("SELECT * FROM registeredStudents WHERE grade_level=? AND section=? AND gender=? ORDER BY full_name ASC");
        $stmt->execute([$gradeLevel, $section, $gender]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Function to generate the greeting
    function getGreeting()
    {
        date_default_timezone_set("Asia/Manila");
        $today = date("F j, Y | g:i A");
        $hour = date('H');
        if ($hour >= 17 && $hour < 5) {
            $dayTerm = "Evening";
        } elseif ($hour >= 12) {
            $dayTerm = "Afternoon";
        } else {
            $dayTerm = "Morning";
        }
        $name = $_SESSION['user_full_name'];

        return "<h1>Good $dayTerm, $name</h1><p>$today</p>";
    }

    // Function to render a student table
    function renderStudentTable($students, $connect)
    {
        $html = '<table>';
        $html .= '<tr><th>Name</th><th>Morning In</th><th>Morning Out</th><th>Afternoon In</th><th>Afternoon Out</th></tr>';
        foreach ($students as $student) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($student['full_name']) . '</td>';

            $id = $student['id'];

            $stmt2 = $connect->prepare("SELECT * FROM attendance WHERE id=?");
            $stmt2->execute([$id]);
            $attendances = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($attendances)) {
                foreach ($attendances as $attendance) {
                    $html .= '<td class="attendanceData' . (strtotime($attendance['morning_in']) >= strtotime('07:15:00') ? " late" : "") . '">' . htmlspecialchars($attendance['morning_in']) . '</td>';
                    $html .= '<td class="attendanceData">' . htmlspecialchars($attendance['morning_out']) . '</td>';
                    $html .= '<td class="attendanceData' . (strtotime($attendance['afternoon_in']) >= strtotime('01:15:00') ? " late" : "") . '">' . htmlspecialchars($attendance['afternoon_in']) . '</td>';
                    $html .= '<td class="attendanceData">' . htmlspecialchars($attendance['afternoon_out']) . '</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }

    // Generate the greeting
    $greeting = getGreeting();

    // Get male and female students
    $maleStudents = getStudentsByGender($connect, $_SESSION['user_gradeLevel'], $_SESSION['user_section'], 'male');
    $femaleStudents = getStudentsByGender($connect, $_SESSION['user_gradeLevel'], $_SESSION['user_section'], 'female');
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>QRAMS | <?= $_SESSION['user_gradeLevel'] ?> <?= strtoupper($_SESSION['user_section']) ?></title>

        <link rel="icon" type="image/x-icon" href="public/assets/icon.png">

        <link rel="stylesheet" href="public/stylesheets/index.css">
        <link rel="stylesheet" href="public/stylesheets/footer.css">
        <link rel="stylesheet" href="public/stylesheets/nav.css">
        <link rel="stylesheet" href="public/stylesheets/glass.css">
        <link rel="stylesheet" href="public/stylesheets/template.css">

    </head>

    <body>
        <nav class="navbar">
            <div class="navbar-left">
                <h1><?= $_SESSION['user_gradeLevel'] ?> - <?= strtoupper($_SESSION['user_section']) ?> ATTENDANCE</h1>
            </div>
            <div class="navbar-right">
                <ul>
                    <li><a href="logout.php">LOGOUT</a></li>
                </ul>
            </div>
        </nav>
        <div class="main-container glass">
            <div class="container1">
                <div class="greetings">
                    <?= $greeting ?>
                </div>
                <div class="buttons">
                    <ul>
                        <li><button onclick="">SF2</button></li>
                        <li><button onclick="refreshPage()"><img src="public/assets/refresh.svg" alt=""></button></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="container2">
                <div class="male-table">
                    <h2>Male Students</h2>
                    <?= renderStudentTable($maleStudents, $connect) ?>
                </div>
                <div class="female-table">
                    <h2>Female Students</h2>
                    <?= renderStudentTable($femaleStudents, $connect) ?>
                </div>
            </div>
        </div>
        <div class="footer">
            <img src="public/assets/gg.png" alt="gian.gg logo">
            <hr id="vertical-hr">
            <p>© GIAN EPANTO, 2023</p>

        </div>

        <p id="disclaimer">In Partial fulfillment Of the requirements for the Strand Science, Technology, Engineering, Mathematics. <a href="about.php">Learn more</a></p>
        <script>
            function refreshPage() {
                location.reload();
            }
        </script>
    </body>

    </html>
<?php
} else {
    header("Location: login.php");
}
?>
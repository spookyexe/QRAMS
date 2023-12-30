<?php
session_start();
include '../config/db_conn.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {

    function arhiveAttendance($conn)
    {
        // Get the current date and time in the format 'YYYY-MM-DD HH:MM:SS'
        $currentDateTime = date('Y-m-d H:i:s');

        // Build the INSERT INTO ... SELECT query with the additional column for the date
        $copySql = "INSERT INTO archive (id, gradeLevel, section, morning_in, morning_out, afternoon_in, afternoon_out, archive_date)
                    SELECT id, gradeLevel, section, morning_in, morning_out, afternoon_in, afternoon_out, :archive_date
                    FROM attendance";

        // Prepare the statement
        $stmt = $conn->prepare($copySql);

        // Bind the parameter
        $stmt->bindParam(':archive_date', $currentDateTime, PDO::PARAM_STR);

        try {
            $conn->beginTransaction();

            // Execute the statement
            $stmt->execute();

            // Check if the copy was successful
            if ($stmt->rowCount() > 0) {
                // Delete data from the attendance table
                $conn->exec("DELETE FROM attendance");

                $conn->commit();
                echo "Successfully Archived";
            } else {
                echo "No records to archive.";
                $conn->rollBack();
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }

    function searchAttendance($conn)
    {
        // Check if search input is set
        if (isset($_POST["searchInput"])) {
            // Sanitize input to prevent SQL injection
            $searchInput = '%' . $_POST["searchInput"] . '%';

            // Define the SQL query using JOIN with aliases and selecting specific columns
            $sql = "SELECT rs.id AS student_id, rs.grade_level, rs.full_name, rs.gender, rs.section,
                a.morning_in, a.morning_out, a.afternoon_in, a.afternoon_out
                FROM registeredStudents rs
                LEFT JOIN attendance a ON rs.id = a.id
                WHERE rs.id LIKE :searchInput 
                OR rs.grade_level LIKE :searchInput 
                OR rs.full_name LIKE :searchInput 
                OR rs.gender LIKE :searchInput 
                OR rs.section LIKE :searchInput";

            // Prepare the statement
            $stmt = $conn->prepare($sql);

            // Bind the parameter
            $stmt->bindParam(':searchInput', $searchInput, PDO::PARAM_STR);

            // Execute the statement
            $stmt->execute();

            // Fetch the results
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check if there are any results
            if ($results) {
                // Display the results
                foreach ($results as $row) {
                    // Output the columns and values for the initial search result
                    foreach ($row as $column => $value) {
                        echo "$column: $value<br>";
                    }
                    echo "<hr>";
                    echo "<br>";
                }
            } else {
                echo "No results found.";
            }
        }
    }

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>QRAMS | ADMIN</title>

        <link rel="icon" type="image/x-icon" href="../assets/images/icon.png">

        <link rel="stylesheet" href="../assets/css/admin.css">
        <link rel="stylesheet" href="../assets/css/footer.css">
        <link rel="stylesheet" href="../assets/css/nav.css">
        <link rel="stylesheet" href="../assets/css/glass.css">
        <link rel="stylesheet" href="../assets/css/template.css">
    </head>

    <body>
        <nav class="navbar">
            <div class="navbar-left">
                <h1>ADMIN INTERFACE</h1>
            </div>
            <div class="navbar-right">
                <ul>
                    <li><a href="../scripts/logout.php">LOGOUT</a></li>
                </ul>
            </div>
        </nav>
        <div class="main-container glass">

            <div class="container1">

                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="submit" name="archiveButton" value="ARCHIVE" />
                </form>

                <?php
                if (isset($_POST['archiveButton'])) {
                    arhiveAttendance($connect);
                }
                ?>
            </div>

            <div class="container2">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="text" name="searchInput" id="searchInput" placeholder="Search...">
                    <input type="submit" name="searchButton" value="Search" />
                </form>



                <div class="searchResults glass">

                    <h3>RESULTS</h3>
                    <hr>

                    <div class="results">
                        <?php
                        if (isset($_POST['searchButton'])) {
                            searchAttendance($connect);
                        }
                        ?>
                    </div>

                </div>
            </div>
        </div>

        <div class="footer">
            <img src="../assets/images/gg.png" alt="gian.gg logo">
            <hr id="vertical-hr">
            <p>© GIAN EPANTO, 2023</p>

        </div>

        <p id="disclaimer">In Partial fulfillment Of the requirements for the Strand Science, Technology, Engineering, Mathematics. <a href="about.php">Learn more</a></p>
    </body>

    </html>
<?php
} else {
    header("Location: login.php");
}
?>
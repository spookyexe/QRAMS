<?php

include '../config/db_conn.php';

date_default_timezone_set('Asia/Manila');

// Your API key
$apiKey = '9ccb6fe7';

// Check if the provided API key matches the expected key
if (!isset($_GET['api_key']) || $_GET['api_key'] !== $apiKey) {
    echo json_encode(array('error' => 'Invalid API key'));
    exit;
}

function getDataById($id)
{
    global $connect; // Assuming $connect is your PDO connection

    // SQL query to retrieve data based on the given ID
    $sql = "SELECT * FROM registeredStudents WHERE id = :id";

    try {
        // Prepare and execute the query with the provided ID as a parameter
        $stmt = $connect->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the row as an associative array
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    } catch (PDOException $e) {
        // Handle any errors that occurred during the query
        return array('error' => 'Query failed: ' . $e->getMessage());
    }
}

// Function to check if a student with the given ID is already in the database
function isStudentInDatabase($id)
{
    global $connect;

    // Use a prepared statement to query the database
    $sql = "SELECT COUNT(*) as count FROM attendance WHERE id = :id";

    try {
        $stmt = $connect->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the count result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the count is greater than 0 (student exists) or not
        return $result['count'] > 0;
    } catch (PDOException $e) {
        // Handle any errors that occurred during the query
        // You might want to log the error or return a specific value based on your application's needs
        return false;
    }
}


// Function to add a new student record to the attendance table
function attendance($id)
{
    global $connect;

    // $currentTime = date("H:i:s");
    $currentTime = "10:31:00";
    $currentTimestamp = strtotime($currentTime);


    if (isStudentInDatabase($id) === false) {

        if ($currentTimestamp >= strtotime("05:00:00") && $currentTimestamp <= strtotime("10:30:00")) {
            $columnName = "morning_out";

            // Retrieve data for the student with the given ID
            $studentData = getDataById($id);

            // Check if data is retrieved successfully
            if (!$studentData || !is_array($studentData)) {
                return array('error' => 'Unable to retrieve student data');
            }

            // Extract relevant data

            $gradeLevel = $studentData["grade_level"];
            $section = $studentData["section"];
            $emptyTime = "00:00:00";

            // Use prepared statement to insert data into the attendance table
            $sql = "INSERT INTO attendance (id, gradeLevel, section, morning_in, morning_out, afternoon_in, afternoon_out) 
                VALUES (:id, :gradeLevel, :section, :currentTime, :emptyTime, :emptyTime, :emptyTime)";

            try {
                $stmt = $connect->prepare($sql);

                // Bind parameters
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':gradeLevel', $gradeLevel);
                $stmt->bindParam(':section', $section);
                $stmt->bindParam(':currentTime', $currentTime);
                $stmt->bindParam(':emptyTime', $emptyTime);

                // Execute the prepared statement
                $stmt->execute();

                return array('message' => 'Student added to attendance table successfully');
            } catch (PDOException $e) {
                // Handle any errors that occurred during the query
                return array('error' => 'Query failed: ' . $e->getMessage());
            }
        } else {
            return array('error' => "Current Time is outside of Attendance Monitoring Time.");
        }
    } else {

        // Check if a record with the same timestamp and non-zero column value already exists
        $checkDuplicateSql = "SELECT id FROM attendance WHERE id = :id AND (
                                (morning_out = :currentTime AND morning_out != '00:00:00') OR 
                                (afternoon_in = :currentTime AND afternoon_in != '00:00:00') OR 
                                (afternoon_out = :currentTime AND afternoon_out != '00:00:00')
                              )";

        $checkDuplicateStmt = $connect->prepare($checkDuplicateSql);
        $checkDuplicateStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkDuplicateStmt->bindParam(':currentTime', $currentTime);
        $checkDuplicateStmt->execute();

        $existingRecord = $checkDuplicateStmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingRecord) {
            // No duplicate found, proceed with the update
            if ($currentTimestamp >= strtotime("10:31:00") && $currentTimestamp <= strtotime("12:30:00")) {
                $columnName = "morning_out";
                echo "10:31AM - 12:30PM";
            } elseif ($currentTimestamp >= strtotime("12:31:00") && $currentTimestamp <= strtotime("14:00:00")) {
                $columnName = "afternoon_in";
                echo "12:31PM - 02:00PM";
            } elseif ($currentTimestamp >= strtotime("14:01:00") && $currentTimestamp <= strtotime("18:00:00")) {
                $columnName = "afternoon_out";
                echo "02:01PM - 06:00PM";
            } else {
                if ($currentTimestamp >= strtotime("05:00:00") && $currentTimestamp <= strtotime("10:30:00")) {
                    return array('error' => "Duplicate entry found");
                } else {
                    return array('error' => "Current Time is outside of Attendance Monitoring Time.");
                }
            }

            // Check if the column is '00:00:00' before updating
            $checkZeroSql = "SELECT id FROM attendance WHERE id = :id AND $columnName = '00:00:00'";
            $checkZeroStmt = $connect->prepare($checkZeroSql);
            $checkZeroStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkZeroStmt->execute();

            $isZeroRecord = $checkZeroStmt->fetch(PDO::FETCH_ASSOC);

            if ($isZeroRecord) {
                // Update only if the existing value is '00:00:00'
                $sql = "UPDATE attendance SET $columnName = :currentTime WHERE id = :id";

                try {
                    $stmt = $connect->prepare($sql);
                    $stmt->bindParam(':currentTime', $currentTime);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();

                    return array('message' => "Column $columnName updated successfully");
                } catch (PDOException $e) {
                    // Handle any errors that occurred during the query
                    return array('error' => 'Query failed: ' . $e->getMessage());
                }
            } else {
                // Existing record is not '00:00:00'
                return array('error' => "Duplicate entry found");
            }
        } else {
            // Duplicate found, handle accordingly
            return array('error' => "Duplicate entry found");
        }
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'get':
            // Retrieve data by ID
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $data = getDataById($id);

                // Output the data as JSON
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                // Handle the case when no ID is provided
                echo json_encode(array('error' => 'No ID provided'));
            }
            break;

        case 'add':
            // Retrieve data by ID
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $data = attendance($id);

                // Output the data as JSON
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                // Handle the case when no ID is provided
                echo json_encode(array('error' => 'No ID provided'));
            }
            break;
    }
} else {
    // Handle the case when no action is provided
    echo json_encode(array('error' => 'No action provided'));
}

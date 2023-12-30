<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
} else {
    $error = '';
}

$emailValue = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRAMS | LOGIN</title>

    <link rel="icon" type="image/x-icon" href="../assets/images/icon.png">

    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/nav.css">
    <link rel="stylesheet" href="../assets/css/glass.css">
    <link rel="stylesheet" href="../assets/css/template.css">

</head>

<body>
    <nav class="navbar">
        <div class="navbar-left">
            <div class="logo">
                <a href="https://web.facebook.com/pagsci" target="_blank"><img src="../assets/images/PAGSCI.png" alt="pagsci logo"></a>
            </div>
            <div class="school-name">
                PAGADIAN CITY SCIENCE HIGH SCHOOL
                <div class="school-address">
                    NATIONAL HIGHWAY, TUBURAN DISTRICT, PAGADIAN CITY
                </div>
            </div>
        </div>
        <div class="navbar-right">
            <ul>
                <li><a href="about.php">ABOUT</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-container glass">
        <div class="image-container"></div>

        <div class="login-container">
            <div class="header">
                <h1>QRAMS</h1>
                <p>QR-CODE ATTENDANCE MONITORING SYSTEM</p>
            </div>
            <form action="../scripts/auth.php" method="post">
                <div role="alert">
                    <p id="alert"><?= $error ?></p>
                </div>
                <input placeholder="Email..." type="email" name="email" value="<?= $emailValue ?>" aria-describedby="emailHelp">
                <input placeholder="Password..." type="password" name="password">
                <button type="submit">LOGIN</button>
            </form>
            <a id="forgot" href="">Forgot Password</a>
        </div>
    </div>

    <div class="footer">
        <img src="../assets/images/gg.png" alt="gian.gg logo">
        <hr id="vertical-hr">
        <p>© GIAN EPANTO, 2023</p>

    </div>

    <p id="disclaimer">In Partial Fulfillment of the Requirements for the Strand: Science, Technology, Engineering, Mathematics (STEM). <a href="about.php">Learn more</a></p>
</body>

</html>
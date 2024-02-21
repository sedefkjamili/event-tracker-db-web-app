<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="dashboard1.css">
    <link rel="stylesheet" type="text/css" href="dashcss.css">

</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="logos">
                <img src="trackingimage.png" alt="Logo">
            </div>

            <h2></h2>
            <a href="dashboard.php">Gösterge Paneli</a>
            <a href="events.php">Etkinlikler</a>
            <a href="attendance.php">Yoklama</a>
            <a href="search.php">Ara</a><br>
            <form method="POST">
                <button type="submit" name="logout">ÇIKIŞ YAP</button>
            </form>
            
        </div>

        <div class="content">
            <h2 class="white-heading">Gösterge Paneli</h2>
            <div class="card">
                <h1>500</h1>
                <h3>Katılımcı</h3>
                <img src="students.png" class="symbol1">
            </div>

          
        </div>
         
    </div>
    
    <?php
    session_start(); // Start the session
    if(isset($_POST['logout'])){
        session_destroy();
        header("location: login.php"); // Redirect to the login page after logout
    }
    ?>

</body>

</html>

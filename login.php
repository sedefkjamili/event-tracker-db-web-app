<?php
    require("connection.php");
?>
<html>
    <head>
        <title>Admin Login Panel</title>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fir=no">
        <link rel="stylesheet" type="text/css" href="mycss.css">

        <img src="trackingimage.png" alt="Logo" class="logo1">

        <title>Admin Login Panel</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <link rel="stylesheet" type="text/css" href="login.css">
    </head>

    <body style="background-image: url('backgroud.jpg'); background-size: cover;">
        <div class="login-form">
            <h2>YÖNETİCİ GİRİŞ PANELİ</h2>
            <h1></h1>
            <form method="POST">
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Kullanıcı Adı" name="AdminName">
                </div>

                <div class="input-field">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Şifre" name="AdminPassword">  
                </div>

                <button type="submit" name="login">GİRİŞ YAP</button>
                
                <div class="extra">
                    <a href="#">Şifreyi Unuttum?</a>
                </div>
            </form>

            <div class="footer">
            <h1> </h1>

                    <a>Bu proje Sedef Kjamılı (21290987) tarafından hazırlanmıştır.</a>
            </div>

        </div>


        <?php
        if (isset($_POST['login'])){
            $query = "SELECT * FROM admin_login WHERE admin_name = '$_POST[AdminName]' AND admin_password = '$_POST[AdminPassword]'";
            $result = mysqli_query($con, $query);
            if(mysqli_num_rows($result))
            {
                session_start();
                $_SESSION['AdminLoginId']=$_POST['AdminName'];
                header("location: Dashboard.php");
            }
            else
            {
                echo '<script>alert("Password incorrect!");</script>';
            }
        }
        ?>


    </body>
</html>
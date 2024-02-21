<?php
    $id = $_GET['id'];
    include("connection.php");
    mysqli_query($con, "DELETE FROM students WHERE id='$id'");
    header('location: add_students.php');
?>
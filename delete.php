<?php
    $id = $_GET['id'];
    include("connection.php");
    mysqli_query($con, "DELETE FROM events WHERE id='$id'");
    header('location: events.php');
?>
<?php
include('dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$user = "CREATE TABLE usertb
(
    UserID int not null primary key auto_increment,
    UserName varchar(30),
    UserEmail varchar(30),
    UserPassword varchar(30),
    UserPhone varchar(30),
    SignupDate varchar(30)
)";

try {
    $query = mysqli_query($connect, $user);
    echo "Data Successfully saved";
} catch (mysqli_sql_exception) {
    echo "Data has been saved";
}

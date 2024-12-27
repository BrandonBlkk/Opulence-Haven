<?php
include('dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// $admin = "CREATE TABLE admintb
// (
//     AdminID int not null primary key auto_increment,
//     AdminProfile varchar(30),
//     FirstName varchar(30),
//     LastName varchar(30),
//     UserName varchar(30),
//     AdminEmail varchar(30),
//     AdminPassword varchar(30),
//     AdminPhone varchar(30),
//     Position varchar(30),
//     SignupDate varchar(30),
//     LastSignIn varchar(30),
//     Status varchar(10)
// )";

// try {
//     $query = mysqli_query($connect, $admin);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $user = "CREATE TABLE usertb
// (
//     UserID int not null primary key auto_increment,
//     UserName varchar(30),
//     UserEmail varchar(30),
//     UserPassword varchar(30),
//     UserPhone varchar(30),
//     SignupDate varchar(30),
//     LastSignIn varchar(30),
//     Status varchar(10)
// )";

// try {
//     $query = mysqli_query($connect, $user);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

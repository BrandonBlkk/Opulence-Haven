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

// $producttype = "CREATE TABLE producttypetb
// (
//     ProductTypeID int NOT NULL Primary Key auto_increment,
//     ProductType varchar(30),
//     Description VARCHAR(255),
//     DateAdded varchar(30),
//     LastUpdate varchar(30)
// )";

// try {
//     $query = mysqli_query($connect, $producttype);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

$supplier = "CREATE TABLE suppliertb
(
    SupplierID int not null primary key auto_increment,
    SupplierName varchar(30),
    SupplierEmail varchar(30),
    SupplierContact varchar(30),
    SupplierCompany varchar(30),
    Address varchar(50),
    City varchar(30),
    State varchar(30),
    PostalCode varchar(15),
    Country varchar(30),
    DateAdded varchar(30),
    LastUpdate varchar(30),
    ProductTypeID int,
    FOREIGN KEY (ProductTypeID) REFERENCES producttypetb (ProductTypeID) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
)";

try {
    $query = mysqli_query($connect, $supplier);
    echo "Data Successfully saved";
} catch (mysqli_sql_exception) {
    echo "Data has been saved";
}

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

// $product = "CREATE TABLE producttb
// (
//     ProductID int not null primary key auto_increment,
//     Title varchar(255),
//     img1 varchar(255),
//     img2 varchar(255),
//     img3 varchar(255),
//     Price decimal(10, 2),
//     DiscountPrice decimal(10, 2),
//     Color varchar(30),
//     ProductDetail varchar(255),
//     Brand varchar(30),
//     ModelHeight varchar(30),
//     ProductSize varchar(30),
//     LookAfterMe varchar(255),
//     AboutMe varchar(255)
//     ExtendedSizing varchar(30),
//     MoreColors varchar(30),
//     SellingFast varchar(30),
//     Stock int,
//     AddedDate varchar(30),
//     LastUpdate varchar(30),
//     ProductTypeID int,
//     FOREIGN KEY (ProductTypeID) REFERENCES producttypetb (ProductTypeID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $product);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

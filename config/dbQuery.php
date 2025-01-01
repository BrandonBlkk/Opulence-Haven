<?php
include('dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// $role = "CREATE TABLE roletb
// (
//     RoleID int not null primary key auto_increment,
//     Role varchar(50),
//     Description text
// )";

// try {
//     $query = mysqli_query($connect, $role);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $admin = "CREATE TABLE admintb
// (
//     AdminID varchar(30) not null primary key,
//     AdminProfile text,
//     FirstName varchar(30),
//     LastName varchar(30),
//     UserName varchar(30),
//     AdminEmail varchar(30),
//     AdminPassword varchar(30),
//     AdminPhone varchar(30),
//     RoleID int,
//     FOREIGN KEY (RoleID) REFERENCES roletb (RoleID) 
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE,
//     SignupDate datetime default current_timestamp,
//     LastSignIn datetime default current_timestamp on update current_timestamp,
//     Status varchar(10) default 'inactive'
// )";

// try {
//     $query = mysqli_query($connect, $admin);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

$user = "CREATE TABLE usertb
(
    UserID varchar(30) not null primary key,
    UserName varchar(30),
    UserEmail varchar(30),
    UserPassword varchar(30),
    UserPhone varchar(30),
    SignupDate datetime default current_timestamp,
    LastSignIn datetime default current_timestamp on update current_timestamp,
    Status varchar(10) default 'inactive'
)";

try {
    $query = mysqli_query($connect, $user);
    echo "Data Successfully saved";
} catch (mysqli_sql_exception) {
    echo "Data has been saved";
}

// $contact = "CREATE TABLE cuscontacttb
// (
//     ContactID int not null primary key auto_increment,
//     ContactMessage text,
//     ContactDate datetime default current_timestamp,
//     Status varchar(30),
//     UserID int
//     FOREIGN KEY (UserID) REFERENCES usertb (UserID)
//     ON DELETE CASCADE
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $contact);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $producttype = "CREATE TABLE producttypetb
// (
//     ProductTypeID int NOT NULL Primary Key auto_increment,
//     ProductType varchar(30),
//     Description text,
//     DateAdded datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp
// )";

// try {
//     $query = mysqli_query($connect, $producttype);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $supplier = "CREATE TABLE suppliertb
// (
//     SupplierID varchar(30) not null primary key,
//     SupplierName varchar(30),
//     SupplierEmail varchar(30),
//     SupplierContact varchar(30),
//     SupplierCompany varchar(30),
//     Address varchar(50),
//     City varchar(30),
//     State varchar(30),
//     PostalCode varchar(15),
//     Country varchar(30),
//     DateAdded datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp,
//     ProductTypeID int,
//     FOREIGN KEY (ProductTypeID) REFERENCES producttypetb (ProductTypeID) 
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $supplier);
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
//     ProductDescription text,
//     Brand varchar(30),
//     ProductSize varchar(30),
//     LookAfterMe text,
//     AboutMe text,
//     MoreColors boolean default false,
//     SellingFast boolean default false,
//     Stock int default 0,
//     AddedDate datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp,
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

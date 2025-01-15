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

// $user = "CREATE TABLE usertb
// (
//     UserID varchar(30) not null primary key,
//     UserName varchar(30),
//     UserEmail varchar(30),
//     UserPassword varchar(30),
//     UserPhone varchar(15),
//     SignupDate datetime default current_timestamp,
//     LastSignIn datetime default current_timestamp on update current_timestamp,
//     Status varchar(10) default 'inactive'
// )";

// try {
//     $query = mysqli_query($connect, $user);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $contact = "CREATE TABLE contacttb
// (
//     ContactID varchar(20) not null primary key,
//     UserID varchar(30),
//     FullName varchar(50),
//     UserEmail varchar(30),
//     UserPhone varchar(15),
//     Country varchar(15),
//     ContactMessage text,
//     ContactDate datetime default current_timestamp,
//     Status varchar(15) default 'pending',
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
//     ProductTypeID varchar(30) not null primary key,
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
//     ProductTypeID varchar(30),
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
//     ProductID varchar(20) not null primary key,
//     Title text,
//     AdminImg1 text,
//     AdminImg2 text,
//     AdminImg3 text,
//     UserImg1 text,
//     UserImg2 text,
//     UserImg3 text,
//     Price decimal(10, 2),
//     DiscountPrice decimal(10, 2),
//     Description text,
//     Specification text,
//     Information text,
//     DeliveryInfo text,
//     Brand varchar(30),
//     ProductSize varchar(30),
//     SellingFast boolean default false,
//     Stock int default 0,
//     AddedDate datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp,
//     ProductTypeID varchar(30),
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

// $rule = "CREATE TABLE ruletb
// (
//     RuleID varchar(20) not null primary key,
//     Rule text,
//     RuleIcon varchar(30),
//     AddedDate datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp
// )";

// try {
//     $query = mysqli_query($connect, $rule);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $facility = "CREATE TABLE facilitytb
// (
//     FacilityID varchar(20) not null primary key,
//     Facility text,
//     FacilityIcon varchar(30),
//     AddedDate datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp
// )";

// try {
//     $query = mysqli_query($connect, $facility);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $amenity = "CREATE TABLE amenitytb
// (
//     AmenityID varchar(20) not null primary key,
//     Amenity text,
//     AmenityIcon varchar(30),
//     AddedDate datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp
// )";

// try {
//     $query = mysqli_query($connect, $amenity);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

$roomtype = "CREATE TABLE roomtypetb
(
    RoomTypeID varchar(20) not null primary key,
    RoomType varchar(30),
    RoomDescription text,
    RoomCapacity int,
    AddedDate datetime default current_timestamp,
    LastUpdate datetime default current_timestamp on update current_timestamp
)";

try {
    $query = mysqli_query($connect, $roomtype);
    echo "Data Successfully saved";
} catch (mysqli_sql_exception) {
    echo "Data has been saved";
}

// $room = "CREATE TABLE roomtb
// (
//     RoomID varchar(20) not null primary key,
//     RoomName varchar(50),
//     RoomDescription text,
//     RoomPrice decimal(10, 2),
//     RoomStatus varchar(15) default 'available',
//     RoomImage1 text,
//     RoomImage2 text,
//     RoomImage3 text,
//     RoomImage4 text,
//     RoomImage5 text,
//     RoomImage6 text,
//     RoomImage7 text,
//     RoomImage8 text,
//     RoomImage9 text,
//     RoomImage10 text,
//     RoomTypeID varchar(20),
//     FOREIGN KEY (RoomTypeID) REFERENCES roomtypetb (RoomTypeID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $room);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $schedule = "CREATE TABLE scheduletb
// (
//     ScheduleID varchar(20) not null primary key,
//     ScheduleDate date,
//     ScheduleStartTime time,
//     ScheduleEndTime time,
//     ScheduleStatus varchar(15) DEFAULT 'available',
//     AdminID varchar(30),
//     RoomID varchar(20),
//     FOREIGN KEY (AdminID) REFERENCES admintb (AdminID)
//     ON DELETE CASCADE
//     ON UPDATE CASCADE,
//     FOREIGN KEY (RoomID) REFERENCES roomtb (RoomID)
//     ON DELETE CASCADE
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $schedule);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $scheduledetail = "CREATE TABLE scheduledetailtb
// (
//     ScheduleID varchar(20) not null primary key,
//     RoomID varchar(20),
//     FOREIGN KEY (ScheduleID) REFERENCES scheduletb (ScheduleID)
//     ON DELETE CASCADE
//     ON UPDATE CASCADE,
//     FOREIGN KEY (RoomID) REFERENCES roomtb (RoomID)
//     ON DELETE CASCADE
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $scheduledetail);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $favorite = "CREATE TABLE favoritetb
// (
//     FavoriteID int not null primary key auto_increment,
//     UserID varchar(30),
//     RoomID varchar(20),
//     FOREIGN KEY (UserID) REFERENCES usertb (UserID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE,
//     FOREIGN KEY (RoomID) REFERENCES producttb (RoomID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE 
// )";

// try {
//     $query = mysqli_query($connect, $favorite);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $roomrule = "CREATE TABLE roomruletb
// (
//     RoomID varchar(20),
//     RuleID varchar(20),
//     FOREIGN KEY (RoomID) REFERENCES roomtb (RoomID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE,
//     FOREIGN KEY (RuleID) REFERENCES ruletb (RuleID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE 
// )";

// try {
//     $query = mysqli_query($connect, $roomrule);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $roomfacility = "CREATE TABLE roomfacilitytb
// (
//     RoomID varchar(20),
//     FacilityID varchar(20),
//     FOREIGN KEY (RoomID) REFERENCES roomtb (RoomID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE,
//     FOREIGN KEY (FacilityID) REFERENCES facilitytb (FacilityID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE 
// )";

// try {
//     $query = mysqli_query($connect, $roomfacility);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $roomamenity = "CREATE TABLE roomamenitytb
// (
//     RoomID varchar(20),
//     AmenityID varchar(20),
//     FOREIGN KEY (RoomID) REFERENCES roomtb (RoomID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE,
//     FOREIGN KEY (AmenityID) REFERENCES amenitytb (AmenityID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE 
// )";

// try {
//     $query = mysqli_query($connect, $roomamenity);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

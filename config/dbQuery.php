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
//     LastSignIn datetime default null,
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
//     LastSignIn datetime default null,
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
//     Price decimal(10, 2),
//     DiscountPrice decimal(10, 2),
//     Description text,
//     Specification text,
//     Information text,
//     DeliveryInfo text,
//     Brand varchar(30),
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

// $productimage = "CREATE TABLE productimagetb
// (
//     ImageID int not null primary key auto_increment,
//     ImageAdminPath text,
//     ImageUserPath text,
//     PrimaryImage boolean default false,
//     SecondaryImage boolean default false,
//     ImageAlt text,
//     ProductID varchar(20),
//     FOREIGN KEY (ProductID) REFERENCES producttb (ProductID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $productimage);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $productfavorite = "CREATE TABLE productfavoritetb
// (
//     FavoriteID int not null primary key auto_increment,
//     UserID varchar(30),
//     ProductID varchar(20),
//     FavoritedAt datetime DEFAULT current_timestamp,
//     FOREIGN KEY (UserID) REFERENCES usertb (UserID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE,
//     FOREIGN KEY (ProductID) REFERENCES producttb (ProductID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $productfavorite);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $productreview = "CREATE TABLE productreviewtb
// (
//     ReviewID int not null primary key auto_increment,
//     Rating int,
//     Comment text,
//     AddedDate datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp,
//     UserID varchar(30),
//     ProductID varchar(20),
//     FOREIGN KEY (UserID) REFERENCES usertb (UserID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE,
//     FOREIGN KEY (ProductID) REFERENCES producttb (ProductID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $productreview);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $reviewReaction = "CREATE TABLE reviewreactiontb
// (
//     ReactionID int NOT NULL PRIMARY KEY AUTO_INCREMENT,
//     ReviewID int NOT NULL,
//     UserID varchar(30) NOT NULL,
//     ReactionType ENUM('like', 'dislike') NOT NULL,
//     ReactedAt datetime DEFAULT current_timestamp,
//     FOREIGN KEY (ReviewID) REFERENCES productreviewtb(ReviewID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE,
//     FOREIGN KEY (UserID) REFERENCES usertb(UserID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $reviewReaction);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $size = "CREATE TABLE sizetb
// (
//     SizeID int not null primary key auto_increment,
//     Size varchar(30),
//     PriceModifier decimal(10, 2),
//     ProductID varchar(20),
//     FOREIGN KEY (ProductID) REFERENCES producttb (ProductID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $size);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $facilitytype = "CREATE TABLE facilitytypetb
// (
//     FacilityTypeID varchar(20) not null primary key,
//     FacilityType varchar(30),
//     FacilityTypeIcon text,
//     IconSize varchar(10),
//     AddedDate datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp
// )";

// try {
//     $query = mysqli_query($connect, $facilitytype);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $rule = "CREATE TABLE ruletb
// (
//     RuleID varchar(20) not null primary key,
//     RuleTitle text,
//     Rule text,
//     RuleIcon text,
//     IconSize varchar(10),
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
//     FacilityIcon text,
//     IconSize varchar(10),
//     AdditionalCharge boolean default false,
//     Popular boolean default false,
//     FacilityTypeID varchar(20),
//     AddedDate datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp,
//     FOREIGN KEY (FacilityTypeID) REFERENCES facilitytypetb (FacilityTypeID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $facility);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $roomtype = "CREATE TABLE roomtypetb
// (
//     RoomTypeID varchar(20) not null primary key,
//     RoomCoverImage text,
//     RoomType varchar(30),
//     RoomDescription text,
//     RoomCapacity int,
//     RoomPrice decimal(10, 2),
//     AddedDate datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp
// )";

// try {
//     $query = mysqli_query($connect, $roomtype);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $room = "CREATE TABLE roomtb
// (
//     RoomID varchar(20) not null primary key,
//     RoomName varchar(50),
//     RoomStatus varchar(15) default 'available',
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

// $roomimage = "CREATE TABLE roomimagetb
// (
//     ImageID int not null primary key auto_increment,
//     ImagePath text,
//     RoomTypeID varchar(20),
//     FOREIGN KEY (RoomTypeID) REFERENCES roomtypetb (RoomTypeID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $roomimage);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $roomview = "CREATE TABLE roomviewtb
// (
//     ReviewID int not null primary key auto_increment,
//     Rating int,
//     Comment text,
//     AddedDate datetime default current_timestamp,
//     LastUpdate datetime default current_timestamp on update current_timestamp,
//     UserID varchar(30),
//     RoomTypeID varchar(20),
//     FOREIGN KEY (UserID) REFERENCES usertb (UserID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE,
//     FOREIGN KEY (RoomTypeID) REFERENCES roomtypetb (RoomTypeID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE
// )";

// try {
//     $query = mysqli_query($connect, $roomview);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

$roomfavorite = "CREATE TABLE roomfavoritetb
(
    FavoriteID int not null primary key auto_increment,
    UserID varchar(30),
    RoomTypeID varchar(20),
    CheckInDate date,
    CheckOutDate date,
    Adult int default 1,
    Children int default 0,
    FOREIGN KEY (UserID) REFERENCES usertb (UserID)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
    FOREIGN KEY (RoomTypeID) REFERENCES roomtypetb (RoomTypeID)
    ON DELETE CASCADE 
    ON UPDATE CASCADE 
)";

try {
    $query = mysqli_query($connect, $roomfavorite);
    echo "Data Successfully saved";
} catch (mysqli_sql_exception) {
    echo "Data has been saved";
}

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
//     RoomTypeID varchar(20),
//     FacilityID varchar(20),
//     PRIMARY KEY (RoomTypeID, FacilityID),
//     FOREIGN KEY (RoomTypeID) REFERENCES roomtypetb (RoomTypeID)
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

// $dining = "CREATE TABLE diningreservationtb
// (
//     ReservationID int not null primary key auto_increment,
//     Date varchar(20),
//     Time varchar(20),
//     NumberOfGuests int,
//     SpecialRequest text,
//     Name varchar(50),
//     Email varchar(50),
//     PhoneNumber varchar(20),
//     Status varchar(20) default 'Pending',
//     UserID varchar(30) DEFAULT NULL, 
//     FOREIGN KEY (UserID) REFERENCES usertb (UserID)
//     ON DELETE CASCADE 
//     ON UPDATE CASCADE,
//     ReservationDate datetime default current_timestamp,
//     UpdatedAt datetime default current_timestamp
// )";

// try {
//     $query = mysqli_query($connect, $dining);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $purchase = "CREATE TABLE purchasetb
// (
//     PurchaseID varchar(30) not null primary key,
//     AdminID varchar(30),
//     SupplierID varchar(30),
//     TotalAmount decimal(10, 2),
//     PurchaseTax decimal(10, 2),
//     Status varchar(30),
//     PurchaseDate datetime default current_timestamp,
//     FOREIGN KEY (AdminID) REFERENCES admintb (AdminID),
//     FOREIGN KEY (SupplierID) REFERENCES suppliertb (SupplierID)
// )";

// try {
//     $query = mysqli_query($connect, $purchase);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

// $purchasedetail = "CREATE TABLE purchasedetailtb
// (
//     PurchaseID varchar(30) not null,
//     ProductID varchar(20),
//     PurchaseUnitQuantity int,
//     PurchaseUnitPrice decimal(10, 2),
//     PurchaseDate datetime default current_timestamp,
//     PRIMARY KEY (PurchaseID, ProductID),
//     FOREIGN KEY (PurchaseID) REFERENCES purchasetb(PurchaseID),
//     FOREIGN KEY (ProductID) REFERENCES producttb(ProductID)
// )";

// try {
//     $query = mysqli_query($connect, $purchasedetail);
//     echo "Data Successfully saved";
// } catch (mysqli_sql_exception) {
//     echo "Data has been saved";
// }

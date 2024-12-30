<?php
function AutoID($tableName, $fieldName, $prefix, $noOfLeadingZeros)
{
    $newID = "";
    $sql = "";
    $value = 1;
    $connect = mysqli_connect('localhost', 'root', '', 'OpulenceHavendb'); // Establish connection
    $sql = "SELECT " . $fieldName . " FROM " . $tableName . " ORDER BY " . $fieldName . " DESC";
    $result = mysqli_query($connect, $sql);
    $noOfRow = mysqli_num_rows($result);
    $row = mysqli_fetch_array($result);
    if ($noOfRow < 1) {
        return $prefix . "000001";
    } else {
        $oldID = $row[$fieldName];    // Reading Last ID
        $oldID = str_replace($prefix, "", $oldID);    // Removing "Prefix"
        $value = (int)$oldID;    // Convert to Integer
        $value++;    // Increment        
        $newID = $prefix . str_pad($value, $noOfLeadingZeros, "0", STR_PAD_LEFT);
        return $newID;
    }
}

<?php
session_start();
/**
 * Created by PhpStorm.
 * User: d.osoev
 * Date: 15.07.2016
 * Time: 8:43
 */
if($_GET["town"]=="Санкт-Петербург") {
    $_SESSION["TOWN"] = "spb";
    $_SESSION['CITY_ID'] = 617;
} else
{
    $_SESSION["TOWN"] = "msk";
    $_SESSION['CITY_ID'] = 618;
}
?>


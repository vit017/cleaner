<?
$arResult["totalPrice"]=$_SESSION["totalPrice"];
$arResult["duration"]=$_SESSION["duration"];
$arResult["totalPriceDiscount"]=$_SESSION["totalPriceDiscount"];
$arResult["periodName"]=$_SESSION["periodName"];
$arResult["periodDiscountPercent"]=$_SESSION["periodDiscountPercent"];
$arResult["periodDiscount"]=$_SESSION["periodDiscount"];
$arResult["periodTotalPrice"]=number_format($_SESSION["periodTotalPrice"],0,'.',' ');
?>

<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-clean-tools.twig') ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/modal-what-we-clean.twig') ?>
<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/views/service/regular_cleaning.twig') ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); ?>


<?
if ($_POST["phone"])
	$phone=$_POST["phone"];


if (!$phone)
	echo "<p class='error_p'>Заполните обязательные поля!</p>";


if ($phone){
	echo "<p class='ok_p'>Ваша заявка успешно отправлена!</p>";
	$arEventFields = array(
		"PHONE"  => $phone
	);
	CEvent::Send("FEEDBACK_FORM", "s1", $arEventFields, "N", 69);
	unset($phone);
}
?>


<input name="phone" type="tel" class="phoneInput" placeholder="Введите номер">
<input class="tell_buttom btn" type="submit" value="Жду звонка">

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); ?>


<?
if ($_POST["comment"])
	$comment=$_POST["comment"];
if ($_POST["name"])
	$name=$_POST["name"];
if ($_POST["email"])
	$email=$_POST["email"];
if ($_POST["phone"])
	$phone=$_POST["phone"];

unset($text);


if (!$comment || !$name || !$email || !$phone)
	$text="<p class='error_p'>Заполните все поля!</p>";


if ($comment && $name && $email && $phone){

	$text="<p class='ok_p'>Ваша заявка успешно отправлена!</p>";
	$arEventFields = array(
		"NAME"  	=> $name,
		"EMAIL"  	=> $email,		
		"PHONE"  	=> $phone,
		"COMMENT"  	=> $comment
	);
	CEvent::Send("FEEDBACK_FORM", "s1", $arEventFields, "N", 71);
	unset($comment);
	unset($email);
	unset($phone);
	unset($name);
}
?>


<h2>Форма заявки</h2>
<p>Отправьте заявку или позвоните по телефону 8 (800) 222-83-30</p>

<?=$text;?>
<label class="input-txt input-txt_width_full" data-placeholder="Кратко о себе">
    <textarea name="comment" class="input-txt__field input-txt__field_area" placeholder="Кратко о себе"><?=$comment?></textarea>
</label>
<label class="input-txt input-txt_width_full input-txt_state_focused" data-placeholder="Ваше имя">
    <input type="text" class="input-txt__field" placeholder="Ваше имя" name="name" value="<?=$name?>">
</label>
<label class="input-txt input-txt_width_full input-txt_state_focused" data-placeholder="Телефон">
    <input type="text" class="input-txt__field" placeholder="Телефон" id="phone" name="phone" value="<?=$phone?>">
</label>
<label class="input-txt input-txt_width_full input-txt_state_focused" data-placeholder="E-mail">
    <input type="email" class="input-txt__field" placeholder="E-mail" name="email" value="<?=$email?>">
</label>
<div class="form__controls">
    <input class="btn btn_type_second" type="submit" value="Отправить">
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
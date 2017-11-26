<?
if (!$_POST["phone"]){
    echo "<p style='color:red;'>Введите номер телефона!</p>";
}

if (!$_POST["text"]){
    echo "<p style='color:red;'>Введите текст сообщения!</p>";
}

if ($_POST["text"] && $_POST["phone"]){
    echo "<p style='color:green;'>Сообщение успешно отправлено!</p>";
    $validPhone = mb_ereg_replace('[^\d]+' ,'', $_POST["phone"]);
    if ($validPhone[0] == 7)
        $validPhone[0] = 8;
    $url = 'https://intra.becar.ru/f8/spservice/request.php?xml=&dima-phone='.$validPhone.'&messagebody='.$_POST["text"].'&MaxClean=';
    file_get_contents($url);
    unset($_POST);
}
?>

<input type="text" name="phone" placeholder="номер телефона (формат: 89111234567)" value="<?=$_POST["phone"];?>">
<textarea name="text" placeholder="текст сообщения" value="<?=$_POST["text"];?>"></textarea>
<input type="submit" name="submit" value="Отправить смс">
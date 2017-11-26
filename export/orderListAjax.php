<?if (!$_POST["text"]){
    echo "<p style='color:red;margin-bottom:10px;'>Введите текст сообщения!</p>";
}

if (!$_POST["phone"]){
    echo "<p style='color:green;margin-bottom:10px;'>Что-то пошло не так:( Обратитесь к разработчику!</p>";
}

if ($_POST["text"] && $_POST["phone"]){
    echo "<p style='color:green;margin-bottom:10px;'>Сообщение успешно отправлено!</p>";
    $validPhone = mb_ereg_replace('[^\d]+' ,'', $_POST["phone"]);
    if ($validPhone[0] == 7)
        $validPhone[0] = 8;
    $url = 'https://intra.becar.ru/f8/spservice/request.php?xml=&dima-phone='.$validPhone.'&messagebody='.$_POST["text"].'&MaxClean=';
    file_get_contents($url);
}
?>

<input type="hidden" name="phone" value="<?=$_POST["phone"]?>">
<textarea name="text" placeholder="текст сообщения"></textarea>
<input type="submit" name="submit" value="Отправить смс">
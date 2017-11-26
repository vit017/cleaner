<?
/**
 * Company developer: ALTASIB
 * Developer: Andrew N. Popov
 * Site: http://www.altasib.ru
 * E-mail: dev@altasib.ru
 * @copyright (c) 2006-2016 ALTASIB
 */
?>
<?
$CookiePX = COption::GetOptionString("main", "cookie_name", "BITRIX_SM");

$MESS['ALTASIB_IS'] = "Магазин готовых решений для 1С-Битрикс";
$MESS['ALTASIB_GEOIP_DESCR'] = "Модуль получает местоположение посетителя по его IP и сохраняет эти данные в сессию и cookies. <br /><br />
<b>Для разработчиков</b><br/>
Данные хранятся виде сериализованного массива в переменной куки ".$CookiePX."_GEOIP и в виде обычного массива — в \$_SESSION[\"GEOIP\"]. <br /><br />
Получить данные можно так:
<pre>
if(CModule::IncludeModule(\"altasib.geoip\"))
{
	\$arData = ALX_GeoIP::GetAddr();
	print_r(\$arData);
}
</pre>
Данные из переменной cookies (если включено):
<pre>
global \$APPLICATION;
\$strData = \$APPLICATION->get_cookie(\"GEOIP\");
\$arData = unserialize(\$strData);
print_r(\$arData);
</pre>
";

$MESS['ALTASIB_GEOIP_SET_COOKIE'] = "Cохранять в cookies информацию о местоположении";

$MESS['altasib_geoip_options_geoiplibru'] = "Настройки для geoip.elib.ru";
$MESS['ALTASIB_GEOIP_NEW_RULES'] = "Код для каждого сайта можно получить бесплатно в <a href=\"https://geoip.top/cgi-bin/kernel.pl?Reg=1\" target=\"_blank\">личном кабинете.</a><br/>Если поле кода не заполнено, то работа с сервисом geoip.top для этого сайта будет невозможна.";

$MESS['ALTASIB_GEOIP_CODE_SITES'] = "Код SID сайта #SITE#:";
?>
<?
$MESS["LDAP_OPTIONS_SAVE"] = "Сохранить";
$MESS["LDAP_OPTIONS_RESET"] = "Отменить";
$MESS["LDAP_OPTIONS_GROUP_LIMIT"] = "Максимальное количество LDAP записей, выбираемых за один запрос:";
$MESS["LDAP_OPTIONS_USE_NTLM"] = "Использовать NTLM авторизацию<sup><span class=\"required\">1</span></sup>:";
$MESS["LDAP_OPTIONS_USE_NTLM_MSG"] = "<span class=\"required\">1</span> - Для работы NTLM авторизации требуется выполнить настройку соответствующих модулей веб-сервера, а также задать домены для NTLM авторизации в настройках AD-серверов на портале.";
$MESS["LDAP_CURRENT_USER"] = "Текущий логин пользователя NTLM авторизации (домен\\логин):";
$MESS["LDAP_CURRENT_USER_ABS"] = "Не определен";
$MESS["LDAP_OPTIONS_NTLM_VARNAME"] = "Имя переменной PHP, в которой хранится логин пользователя NTLM (обычно REMOTE_USER):";
$MESS["LDAP_NOT_USE_DEFAULT_NTLM_SERVER"] = "Не использовать";
$MESS["LDAP_DEFAULT_NTLM_SERVER"] = "Сервер домена по умолчанию:";
$MESS["LDAP_OPTIONS_DEFAULT_EMAIL"] = "E-mail для пользователей, у которых он не указан:";
$MESS["LDAP_OPTIONS_NEW_USERS"] = "Создавать новых пользователей при первой успешной авторизации:";
$MESS["LDAP_BITRIXVM_BLOCK"] = "Переадресация Ntlm авторизации на порты 8890 8891:";
$MESS["LDAP_BITRIXVM_SUPPORT"] = "Включить переадресацию NTLM авторизации:";
$MESS["LDAP_BITRIXVM_NET"] = "Ограничить NTLM переадресацию следующей подсетью:";
$MESS["LDAP_BITRIXVM_HINT"] = "Укажите здесь подсеть, NTLM авторизацию пользователей которой, необходимо переадресовывать.<br> Например: <b>192.168.1.0/24</b> или <b>192.168.1.0/255.255.255.0</b>.<br>Можно указать несколько диапазонов через точку с запятой (;).<br> Если поле оставить пустым, тогда переадресация будет работать для всех пользователей.";
$MESS["LDAP_WRONG_NET_MASK"] = "Адрес и маска подсети для NTLM авторизации указаны в неверном формате.<br> Приемлемые варианты:<br> сеть/маска <br> xxx.xxx.xxx.xxx/xxx.xxx.xxx.xxx <br> xxx.xxx.xxx.xxx/xx. <br> Можно указать несколько диапазонов через точку с запятой (;)";
$MESS["LDAP_WITHOUT_PREFIX"] = "Проверять авторизацию на всех доступных ldap серверах, если в логине не указан префикс:";
$MESS["LDAP_DUPLICATE_LOGIN_USER"] = "Создавать пользователя, если пользователь с таким логином уже существует:";
?>
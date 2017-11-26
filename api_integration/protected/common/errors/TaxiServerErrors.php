<?php


class TaxiServerErrors
{
    
    const BAD_COMMAND = 'bad_command';
    
    const BAD_PARAMS = 'bad_params';
    
    const BAD_SIGN = 'bad_sign';
    
    const BAD_SERVER_STATE = 'bad_server_state';
    
    const OTHER_ERROR = 'other_error';
    
    const INTERNAL_ERROR = 'server_internal_error';
    
    const ACCESS_ERROR = 'access_error';
    
    public static $errorsLabels = array(
        self::BAD_COMMAND => 'Команды не существует',
        self::BAD_PARAMS => 'Неверные параметры команды',
        self::BAD_SIGN => 'Неверная подпись',
        self::BAD_SERVER_STATE => 'Сервер не ответил вовремя или ответил с ошибками',
        self::OTHER_ERROR => 'Прочая ошибка',
        self::ACCESS_ERROR => 'Ошибка доступа',
    );
    
    
    public static function createMessage($errorCode, $customMessage)
    {
        if (isset(self::$errorsLabels[$errorCode])) {
            return self::$errorsLabels[$errorCode] . ': ' . $customMessage;
        } else {
            return $customMessage;
        }
    }
}
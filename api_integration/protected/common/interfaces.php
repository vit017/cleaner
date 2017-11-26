<?php

/**
 * Интерфейс объекта
 */
interface ITaxiModel
{

    /**
     * 
     * @param  $propertyName - имя свойства
     * @param mixed $value - значение
     * @return boolean
     */
    public function validate($propertyName, $value);

    /**
     * Проверить если такое свойство 
     * @param string $propertyName - имя свойства
     * @return boolean
     */
    public function hasProperty($propertyName);

    /**
     * Получить текущие свойства для этого объекта
     * @return array - асс.массив всех свойств со значениями
     */
    public function getProperties();
}

/**
 * Интерфейс объекта кеша
 */
interface ITaxiCache
{

    /**
     * Получить значение из кеша по его уникальному ключу
     * @param string $key - ключ
     * @return mixed - объект\строка и т.д. или же Null если значение не было определено
     */
    public function getValue($key);

    /**
     * Установка значения в кеш
     * @param string $key - ключ 
     * @param mixed $value - значение
     * @param integer $expire - через сколько сек значение уже будет неатуальным - 
     * ! интервал относительный
     */
    public function setValue($key, $value, $expire = null);

    /**
     * Сбросить кеш
     * @return boolean - успешно ли это прошло
     */
    public function flush();
}

/**
 * Интерфейс лога - объекта, фиксирующего сообщения от других объектов
 */
interface ITaxiLog
{

    /**
     * Логировать ошибку
     * @param string $message - строка сообщения
     * @param object $senderObject - объект который "посылает" сообщение
     * @param const $level - уровень важности 
     * @return boolean - успешность записи
     */
    public function error($message, $senderObject = null);

    /**
     * Логировать предупреждение
     * @param string $message - строка сообщения
     * @param object $senderObject - объект который "посылает" сообщение
     * @param const $level - уровень важности 
     * @return boolean - успешность записи
     */
    public function warning($message, $senderObject = null);

    /**
     * Логировать обычное сообщения
     * @param string $message - строка сообщения
     * @param object $senderObject - объект который "посылает" сообщение
     * @param const $level - уровень важности 
     * @return boolean - успешность записи
     */
    public function info($message, $senderObject = null);

    /**
     * объект - от которого идут логирующие сообщения
     * @param stdClass $value
     */
    public function setSenderObject($value);

    /**
     * объект - от которого идут логирующие сообщения
     * @return stdClass
     */
    public function getSenderObject();
}

/**
 * Интерфейс клиентского объекта подключения и выполнения рутинных запросов к "нашему" серверу
 */
interface ITaxiClientConnection
{

    /**
     * Изменить хост адрес сервера
     * @param type $value
     */
    public function setHost($value);

    /**
     * Изменить секретный ключ для подписи
     * @param type $value
     */
    public function setSecretKey($value);

    /**
     * хост адрес сервера
     */
    public function getHost();

    /**
     * секретный ключ для подписи
     */
    public function getSecretKey();

    /**
     * Отослать команду на наш сервер
     * @param string $commandName - имя комадны
     * @param array $params - ассиц. массив параметров
     * @return array - ассиц. массив ответа или NULL| пустой массив в случае критических ошибок
     */
    public function send($commandName, $params = array());

    /**
     * Берем наш ИП + команда + параметры + ключ
     * @param type $commandName
     * @param type $paramsEncodedString
     * @return string - полученная подпись
     */
    public function createSign($commandName, $paramsEncodedString);
}

/* ! TODO */

/**
 * Интерфейс компонента, который может обработать точку входа
 */
interface ITaxiEntryPoint
{

    /**
     * Обработать AJAX / POST запрос на клиент
     */
    public function processRequest();
}

/**
 * 
 * Интерфейс универсального клиента запросов, он может быть как локальным - и использовать АПИ на этом сервере,
 * так и обращаться к "нашему" удаленному АПИ, которе уже лежит на другом сервере
 */
interface ITaxiClient extends ITaxiEntryPoint
{

    /**
     * Изменить хост адрес сервера
     * @param type $value
     */
    public function setHost($value);

    /**
     * Изменить секретный ключ для подписи
     * @param type $value
     */
    public function setSecretKey($value);

    /**
     * хост адрес сервера
     */
    public function getHost();

    /**
     * секретный ключ для подписи
     */
    public function getSecretKey();

    /**
     * Установить ключ код - текущего выбранного адаптера
     */
    public function setAdapterKey($adapterKey);

    /**
     * ключ код - текущего выбранного адаптера
     */
    public function getAdapterKey();

    /**
     * Выполнить внутренний запрос на команду с стороннему апи или промежутчно через "наш" сервер ретранслятор команд
     * @param string $commandName - имя сторонней комадны\запроса - 'createOrder'
     * @param array $params - ассиц. массив параметров - array('date' => '2013.11.13', ... ) подбирается 
     * специально для некоторой команды - зависит от особенностей стороннего ПО
     * @return TaxiApiCommandResult - объект ответа на команду == запросы, с указанием и расшифровками, в случае произошедших ошибок
     */
    public function executeServerCommand($commandName, $params = array());

    /**
     * Выполнить комадну нашего АПИ - промежуточной прослойки, для универсализации всех сторонних АПИ
     * @param TaxiApiCommand $commandObject - объект запроса нашей команды - для прослойки адаптера команд
     * @return TaxiApiResult - объект ответа на "нашу" команду от универсальной прослойки - адаптера команд
     */
    public function executeOnServer($commandObject);

    /**
     * Выполнить комадну нашего АПИ - на клиентской стороне - для специализированных общих, 
     * т.е. не зависящих от сервера и адаптера комманд
     * @param TaxiApiCommand $commandObject - объект запроса нашей команды - для прослойки адаптера команд
     * @return TaxiApiResult - объект ответа на "нашу" команду от универсальной прослойки - адаптера команд
     */
    public function executeOnClient($commandObject);
}

/**
 * Интерфейс объекта команды или запроса
 */
interface ITaxiQueryCommand
{

    /**
     * Название метода команды \ запроса
     * @return string - 
     */
    public function getCommandName();

    /**
     * Название метода команды \ запроса
     * @param string $value - 
     */
    public function setCommandName($value);

    /**
     * Параметры запроса
     * @return array
     */
    public function getParams();

    /**
     * Параметры запроса
     * @param array $value - 
     */
    public function setParams($value);

    /**
     * Проверка на служебную корректность - есть ли неустранимые ошибки 
     * @return boolean - 
     */
    public function hasErrors();

    /**
     * Провести валидацию параметров
     * @return boolean - true - если все параметры были успешно проверены и соответствуют формату
     */
    public function validate();
}

/**
 * Интерфейс объекта ответа\результата запроса или команды к нашему серверу
 */
interface ITaxiQueryCommandResult
{

    /**
     * Проверка на служебную корректность - есть ли неустранимые ошибки 
     * @return boolean - 
     */
    public function hasErrors();

    /**
     * Провести валидацию параметров
     * @return boolean - true - если все параметры были успешно проверены и соответствуют формату
     */
    public function validate();
}

/**
 * Интерфейс стандартного HTTP подключения
 */
interface ITaxiHttpConnection
{

    /**
     * Текущий адаптер
     * @return TaxiAdapter
     */
    public function getAdapter();

    /**
     * Текущий адаптер
     * @param TaxiAdapter $adapter - 
     */
    public function setAdapter($adapter);

    /**
     * Событие после успешного выполнения запроса
     * @param string $rawResult сырой результат ответа - при необходимости можеть 
     *      быть раскодированным JSON объектом
     * @return boolean - продолжать ли дальше
     */
    public function afterQuery($rawResult);

    /**
     * Событие до выполнения запроса на УРЛ
     * @param string $url - УРЛ запроса
     * @param array|false $postOptions - опции для передачи в запрос типа POST | false - если запрос типа GET
     * @return boolean - продолжать ли дальше
     */
    public function beforeQuery($url, $postOptions);

    /**
     * @param string $url - Текущий УРЛ для запроса
     * @return string - сырая строка полученного контента без заголовков
     */
    public function executeGetQuery($url);

    /**
     * @param string $url - Текущий УРЛ для запроса
     * @param array $postOptions - POST параметры запроса
     * @return string - сырая строка полученного контента без заголовков
     */
    public function executePostQuery($url, $postOptions);
}

/**
 * Интерфейс "Нашего сервера"
 */
interface ITaxiServer
{

    /**
     * Выполнить команду на наш сервере
     * @param string $commandName - имя комадны
     * @param array $params - ассиц. массив параметров
     * @return array - реальный ответ этой команды
     */
    public function executeCommand($commandName, $params = array());

    /**
     * Берем наш ИП + команда + параметры + ключ
     * @param type $commandName
     * @param type $paramsEncodedString
     * @return string - полученная подпись
     */
    public function createSign($commandName, $paramsEncodedString);

    /**
     * метод должен исполнять POST
     * @return mixed - некий ответ от сервера
     */
    public function processPostRequest();

    /**
     * Точка входа - метод проверяет и выполняет запрос
     * запрос и возвращать в выводе ответ в виде json строки
     * @return boolean - флаг успешности 
     */
    public function processRequest();
}

/**
 * Интерфейс любого адаптера
 */
interface ITaxiAdapter
{

    /**
     * Применить фильтры к входным параметрам
     * @param string $commandName - имя метода
     * @param array $params - набор параметров методаы
     */
    public function applyFilters($commandName, $params);

    /**
     * Валидация входных параметров для метода
     * @param string $commandName - имя метода
     * @param array $params - набор параметров методаы
     */
    public function validateParams($commandName, $params);

    /**
     * Текущий для адаптера фильтр
     * @return TaxiFilter - 
     */
    public function createFilter();

    /**
     * Текущий для адаптера валидатор
     * @return TaxiValidator
     */
    public function createValidator();
}

/**
 * Интерфейс адаптера, требующего авторизации через СМС
 */
interface ITaxiAuthorizedAdapter
{

    /**
     * Проверить - есть сохраненная информация об авторизации на
     * этот телефон
     * @param string $phone - телефон
     */
    public function hasStoredAuthorization($phone);

    /**
     * Сохранить авторизацию на этот телефон и код в хранилище
     * @param string $phone - телефон
     * @param string $smsCode - код смс
     */
    public function storeAuthorization($phone, $smsCode);

    /**
     * Попытка автоавторизации, беря информацию об авторизации из автоматического 
     * хранилища
     * @param string $phone - телефон
     */
    public function tryAuthorizationFromStore($phone);

    /**
     * Очистка всего хранилища данных для автоавторизаций
     * @return boolean - успешно ли прошла операция
     */
    public function clearAuthorizationStore();
}

/**
 * Интерфейс структуры данных
 */
interface ITaxiInfo
{
    
}

/**
 * Интерфейс фильтра
 */
interface ITaxiFilter
{

    /**
     * Фильтрация входных параметров метода АПИ
     * @param string $methodName - метод - 'findStreets'
     * @param array $params - массив входных параметров метода
     * @return array - массив отфильтрованных параметров
     */
    public function filterParams($methodName, $params);

    /**
     * Фильтрация результата выполнения метода АПИ
     * @param TaxiFilterData $filterData - данные-параметры фильтрации
     * @result mixed - фильтрованный результат
     */
    public function filterResult($filterData);
}

/**
 * Интерфейс валидатора
 */
interface ITaxiValidator
{

    /**
     * Валидация входных параметров метода АПИ
     * @param string $methodName - метод - 'findStreets'
     * @param array $params - массив входных параметров метода
     * @return array - массив отфильтрованных параметров
     */
    public function validateParams($methodName, $params);

    /**
     * Валидация результата выполнения метода АПИ
     * @param string $methodName - метод - 'findStreets'
     * @result mixed
     */
    public function validateResult($methodName, $result);
}

/**
 * Интерфейс обработчика событий лога
 */
interface ITaxiLogEventHandler
{

    /**
     * Обработка события записи в лог
     * @@param TaxiLogInfo $info - сообщение
     */
    public function onLogWrite($info);
}

/**
 * Интерфейс множества параметров метода АПИ
 */
interface ITaxiApiMethodParamsListBase
{

    /**
     * Тестирование внутренней актуальности информации о параметрах метода
     * @return boolean - успешно ли прошло тестирование
     */
    public function testActualityInfo();

    /**
     * Тестирование внутренней схемы атрибутов
     * @return boolean - успешно ли прошло тестирование
     */
    public function testAttributesScheme();
}

/**
 * Интрейфейс множества входных параметров какого-либо метода
 */
interface ITaxiApiMethodParamsList
{

    /**
     * Слияние с другим множеством параметров, если слияние невозможно (например,
     * другое множество содержит элементы с такими же именами, то будет
     * вызвано исключение, поэтому нельзя сливать например:
     *   (OrderSet: fromLat fromLon clientName) с (RouteSet: length fromLat fromLon)
     * @@param TaxiApiMethodParamsSet $anotherSet - другое множество
     * @return TaxiApiMethodParamsSet
     */
    public function mergeWith($anotherSet);

    /**
     * Получить список всех атрибутов этого множества в правильном порядке
     * с значениями по умолчанию, тип значений будет использован в качестве эталона при приведении типов
     * @return array - array('fromLat' => '', 'fromLon' => '', 'phone' => '', 'clientName' => '')
     */
    public function getDefaultAttributes();

    /**
     * Представить все линейные параметры в виде массива
     * @return array - array('fromLat' => '55.222', 'fromLon' => '33.55', 'clientName' => 'Иван Петров')
     */
    public function getAttrubutes();

    /**
     * Заполнить все линейные параметры этого множества с помощью массива входных данных
     * при этом будет проведена попытка приведения типов, их дополнения и сортировки
     * согласно списку getDefaultAttributes()
     */
    public function setAttributes($paramsArray);

    /**
     * Получить массив специализированной информации по атрибутам в виде
     * хепл строк для автосоставления документации:
     * см. @see TaxiApiMethodInfo
     * @return array - array('fromLat' => 'Координаты точки откуда/куда - широта/долгота @type string @sample '55.22333' @sample '' @sample null @more 
     * координаты могут быть поулчены через Yandex карты @return TaxiOrderInfo - информация по заказу @sample {....}')
     * @return array - асс. массив с специализированной информацией
     */
    public function getAttributesInfo();
}

/**
 * Интерфейс - Абстрактное подключение к БД
 */
interface ITaxiDbConnection
{

    /**
     * Исполнить запрос
     * @param string $sql - текст запроса
     * @param boolean $fetchAsArray - парсить результат как ассициативный массив - false
     * @return array - то что вернет БД
     */
    public function query($sql, $fetchAsArray = false);
}

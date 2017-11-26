<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 27.07.2016
 * Time: 16:50
 */
//sleep(2);
//http_response_code(404);die;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$headers = 'From: maxclean.help <noreply@maxclean.help>' . "\r\n" .
    'Reply-To: noreply@becar.ru' . "\r\n" .
    'X-Mailer: PHP/' . phpversion() . "\r\n" .
    "Content-type: text/html; charset=utf-8\r\n";
$emails = array();
//$emails[] = 'r.blonov@naibecar.com';
$emails[] = 'ju.kazachenko@naibecar.com';
$emails[] = 'ivrok@yandex.ru';
$to = implode(',', $emails);
$result = false;
if (count($_POST)) {
    $fields = array('name' => 'Имя клиента', 'phone' => 'Телефон', 'email' => 'Email', 'square' => "Выбранная площадь");
    $data = array();
    foreach ($fields as $fieldName => $fieldTr) {
        if (isset($_POST[$fieldName]) && $_POST[$fieldName]) {
            $data[] = $fieldTr . ': ' . $_POST[$fieldName];
        }
    }
    $subject = 'maxclean.help, заявка на уборку.';
    if (count($data)) {
        $message = 'Поступила заявка от клиента<br />' . "\r\n";
        $message .= implode("<br /> \r\n", $data);
        $result = mail($to, $subject, $message, $headers);
    }
    if (function_exists('sendsms')) {
        $phoneMessage = 'Поступила заявка от клиента' . "\n";
        $phoneMessage .= implode("\n", $data);;
        sendsms(KAZAKOVA_PHONE, $phoneMessage);
    }
}
if ($result) {
    http_response_code(200);
} else {
    http_response_code(404);
}
die;
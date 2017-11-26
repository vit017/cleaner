<?php

/*
 * Проверки на существования сохраненного заказа в сессии
 */
/* ! TODO - проверку на валидность заказа */

$cookieOrderId = $orderId = $_COOKIE['api_order_id'];

if ($cookieOrderId > 0 && $cookieOrderId != $orderId){
    $_SESSION['orderId'] = $orderId = null;
}
if (!$cookieOrderId){
    $_SESSION['orderId'] = $orderId = null;
}

if (!is_integer($orderId) && !is_string($orderId)) {
    $_SESSION['orderId'] = $orderId = null;
}
$hasTaxiApiOrderId = isset($orderId) && $orderId > 0;


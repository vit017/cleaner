<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 07.04.14
 * Time: 15:47
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => 'Подписка MailChimp',
    "DESCRIPTION" => 'Подписка в MailChimp',
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "service",
        "CHILD" => array(
            "ID" => "subscribe.mc",
            "NAME" => 'Подписка MailChimp',
        )
    ),
);
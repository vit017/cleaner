<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 14.10.2016
 * Time: 12:36
 */
class MailSmsRecipients{
    private static $orderDoneSMS = array(
        'DEV' => array('SPB' => array(89522310637, KAZAKOVA_PHONE), 'MSK' => array(89522310637, KAZAKOVA_PHONE)),
        'PROD' => array('SPB' => array(MANAGER_PHONE, MANAGER_PHONE_SPB_TEST), 'MSK' => array(MANAGER_PHONE_MSK, MANAGER_PHONE_MSK_TEST)),
    );
    private static $orderDoneMail = array(
        'DEV' => array(
            'SPB' => array("ivrok@rambler.ru", "i.vasilev@naibecar.com"),
            'MSK' => array("ivrok@rambler.ru", "i.vasilev@naibecar.com")
        ),
        'PROD' => array(
            'SPB' => array("t.morozova@naibecar.com", "i.vasilev@naibecar.com", 'r.blonov@naibecar.com'),
            'MSK' => array("v.sazhnev@naibecar.com", "i.vasilev@naibecar.com", 'r.blonov@naibecar.com')
        )
    );
    private static function curPlatform(){
        return IS_PRODUCTION ? 'PROD' : 'DEV';
    }
    public static function getOrderDoneMail($city = 'spb')
    {
        $city = mb_strtoupper($city);
        return self::$orderDoneMail[self::curPlatform()][$city];
    }
    public static function getOrderDoneSMS($city = 'spb')
    {
        $city = mb_strtoupper($city);
        return self::$orderDoneSMS[self::curPlatform()][$city];
    }
}
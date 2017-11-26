<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 08.04.15
 * Time: 15:24
 */
CModule::IncludeModule('iblock');
CModule::IncludeModule('sale');

/**
 * BHTODO В публичные методы не должны приходить большие массивы данных, это признак неверной реализации общения с классом
 */

class bhOrder{
//bhOrder:addCleaner();
    public static function setProp($orderID, $code, $val, $personType = false){
        if ( $orderID<=0 || !$orderID || strlen($code) <= 0 )
            return false;

        $db_props = CSaleOrderPropsValue::GetList(
            array("SORT" => "ASC"),
            array(
                "CODE" => $code,
                "ORDER_ID" => $orderID
            )
        );
        if($prop = $db_props->Fetch()){
            if ( $prop['VALUE'] != $val ){
                return self::updateProp($prop['ID'], $val);
            } else{
                return true;
            }
        } else {
            if ( !$personType || $personType <= 0 ){
                if ( $arOrder = CSaleOrder::getByID($orderID) ) {
                    $personType = $arOrder['PERSON_TYPE_ID'];
                } else {
                    return false;
                }
            }
            return self::addProp($personType, $orderID, $code, $val);
        }
    }

    private static function addProp($personType, $orderID, $code, $val){
        if ( !$personType || strlen($code) <=0 || $orderID <= 0 ) return false;

        $filter = array(
            "CODE" => $code,
            "PERSON_TYPE_ID" => $personType,
            "ACTIVE" => "Y",
        );
        $arProps = self::getPropList($filter);
        foreach($arProps as $prop){
            $prop["ORDER_ID"] = $orderID;
            $prop["VALUE"] = $val;
            if ( !CSaleOrderPropsValue::Add($prop) ){
                return false;
            } else {
                break;
            }
        }
        return true;
    }

    private static function getPropList($arFilter = array()){
        $arProps = array();
        $db_props = CSaleOrderProps::GetList(
            array("SORT" => "ASC"),
            $arFilter
        );
        while($prop = $db_props->Fetch()){
            $arFields = array(
                "ORDER_PROPS_ID" => $prop['ID'],
                "NAME" => $prop['NAME'],
                "CODE" => $prop['CODE'],
            );
            $arProps[$prop['CODE']] = $arFields;
        }
        return $arProps;
    }

    private function updateProp($propValID, $val){
        if ( $propValID <= 0 ) return false;

        $arFields = array("VALUE" => $val);
        if ( !CSaleOrderPropsValue::Update($propValID, $arFields) ){
            return false;
        };
        return true;
    }

    public static function addProps($personType, $orderID, $data){
        if ( !is_array($data) || empty($data) ) return false;
        if ( !$personType || $orderID <= 0 ) return false;

        $filter = array(
            "CODE" => array_keys($data),
            "PERSON_TYPE_ID" => $personType,
            "ACTIVE" => "Y");
        $arProps = self::getPropList($filter);
        foreach($arProps as $prop){
            $prop["ORDER_ID"] = $orderID;
            $prop["VALUE"] = $data[$prop['CODE']];
            if ( !CSaleOrderPropsValue::Add($prop) ){
                return false;
            } else {
                //break;
            }
        }
        return true;
    }
    public static function get($id)
    {
        $order = false;
        if ($order = CSaleOrder::GetByID($id)) {
            $propsRes = CSaleOrderPropsValue::GetOrderProps($id);
            $props = array();
            while ($prop = $propsRes->fetch()) {
                $props[$prop['CODE']] = $prop['VALUE'];
            }
            $order['PROPS'] = $props;
        }
        return $order;
    }
    public static function getProps($orderID){
        $arReturn = array();

        if ( is_array($orderID) && !empty($orderID) ){
            $dbP = CSaleOrderPropsValue::getList(
                array('ORDER_ID' => 'DESC'),
                array('ORDER_ID' => $orderID)
            );
            while($arProps = $dbP->fetch()) {
                $arReturn[$arProps['ORDER_ID']][$arProps['CODE']] = $arProps;
            }
            foreach ($orderID as $id){
                if ( isset($current))
                    unset($current);
                $current = &$arReturn[$id];
                $current['TIME_TO']['VALUE'] = $current['TIME']['VALUE'] + $arReturn['DURATION']['VALUE'];
                $current['TIME']['PRINT_VALUE'] = $current['TIME']['VALUE'] != floor($current['TIME']['VALUE'])?floor($current['TIME']['VALUE']).':30':$current['TIME']['VALUE'].':00';
                $current['TIME_TO']['PRINT_VALUE'] = $current['TIME_TO']['VALUE'] != floor($current['TIME_TO']['VALUE'])?floor($current['TIME_TO']['VALUE']).':30':$current['TIME_TO']['VALUE'].':00';

                $current['DATE']['PRINT_VALUE'] = $current['DATE']['VALUE'];
                $arDate = preg_split('[\.]',trim($current['DATE']['VALUE']));
                $months = bhTools::months(true);
                $current['DATE']['PRINT_VALUE'] = $arDate[0].' '.$months[trim($arDate[1], '0')].' '.$arDate[2];
                $current['DURATION']['PRINT_VALUE'] = $current['DURATION']['VALUE'].' '.bhTools::words(floor($current['DURATION']['VALUE']), array('час', 'часа', 'часов'));
            }

        } elseif ( !is_array($orderID) ){
            $dbP = CSaleOrderPropsValue::GetOrderProps($orderID);
            while($arProps = $dbP->fetch()) {
                $arReturn[$arProps['CODE']] = $arProps;
            }
        } else {
            return false;
        }

        return $arReturn;
    }

    //Выполнен
    public static function setStatusF($arOrder, $arProps){
        if ( $arOrder['STATUS_ID'] != 'F' ){
            CSaleOrder::StatusOrder($arOrder['ID'], "F");
        }

        if ( $arOrder['PAY_SYSTEM_ID'] == 2 ){
            if ( strlen($arProps['CardId']['VALUE']) >0 ){
                $cardID = $arProps['CardId']['VALUE'];
            }
            $attemp = 0;
            if ( strlen($arProps['PAYTURE_ATTEMP']['VALUE']) >0 ){
                $attemp = $arProps['PAYTURE_ATTEMP']['VALUE'];
            }

            if($cardID) {
                if(strlen($cardID)>0){
                    //unblock 1RUB
                    $blockAmount  = bhPayture::getStatus($arOrder['ID']);
                    if ($blockAmount > 0){
                        $unblock = bhPayture::getUnblock($arOrder['ID'], $blockAmount);
                        file($unblock);
                    }

                    $new_amount = round($arOrder["PRICE"] - $arOrder['SUM_PAID'], -1);
                    $payStatus = bhPayture::getPay($arOrder['ID'], $cardID, $new_amount, $attemp);
                    $attemp++;
                    bhOrder::setProp($arOrder['ID'], 'PAYTURE_ATTEMP', $attemp, $arOrder["PERSON_TYPE_ID"]);

                    if($payStatus){
                        CSaleOrder::Update($arOrder['ID'], array('PS_STATUS'=>'Y', 'PS_SUM' => $new_amount));
                        CSaleOrder::PayOrder($arOrder['ID'], 'Y', false);
                    }
                }
            }
        }else{
            CSaleOrder::PayOrder($arOrder['ID'], 'Y', false);
        }
    }

    //Подтвержден
    public static function setStatusA($arOrder){
        if ( $arOrder['STATUS_ID'] != 'A' ){
            CSaleOrder::StatusOrder($arOrder['ID'], "A");
        }
    }


    private function busyFilter($arOrders){
        $arProps = self::getProps(array_keys($arOrders));

        $tmpByDate = array();
        foreach ($arProps as $order => $props){
            if ( isset($props['Cleaner']) && $props['Cleaner']['VALUE'] > 0 ){
                $arOrders[$order]['PROPS'] = $props;
                $date = new dateTime($props['DATE']['VALUE'].' '.$props['TIME']['VALUE'].':00');
                $tStamp = $date->getTimestamp();
                $tmpByDate[$tStamp][] = $order;
            } else {
                unset($arOrders[$order]);
            }
        }
        $ordersByDate = self::rsort($tmpByDate, $arOrders);
        return $ordersByDate;
    }

    public static function sortByDate($arOrders, $sortOrder = false){
        $arProps = self::getProps(array_keys($arOrders));
        $tmpByDate = array();
//        die(print_r($arProps));
        foreach ($arProps as $order => $props){
            try {
                $date = new dateTime($props['DATE']['VALUE'].' '.$props['TIME']['VALUE'].':00');
                $tStamp = $date->getTimestamp();
            } catch (Exception $e) {
                $tStamp = 0;
            }
            $tmpByDate[$tStamp][] = $order;
            $arOrders[$order]['PROPS'] = $props;
        }
        if ( $sortOrder =='R' ) {
            $ordersByDate = self::rsort($tmpByDate, $arOrders);
        } else {
            $ordersByDate = self::sort($tmpByDate, $arOrders);
        }

        return $ordersByDate;
    }

    private function sort($tmpByDate, $arOrders){
        $ordersByDate = array();
        ksort($tmpByDate);
        foreach ($tmpByDate as $orders){
            foreach ($orders as $order){
                $date = $arOrders[$order]['PROPS']['DATE']['VALUE'];
                $ordersByDate[$date][$order] = $arOrders[$order];
            }
        }

        return $ordersByDate;
    }

    private function rsort($tmpByDate, $arOrders){
        $ordersByDate = array();
        krsort($tmpByDate);

        foreach ($tmpByDate as $orders){
            foreach ($orders as $order){
                $date = $arOrders[$order]['PROPS']['DATE']['VALUE'];
                $ordersByDate[$date][$order] = $arOrders[$order];
            }
        }
        return $ordersByDate;
    }

    public static function formatProps($arProps){
        $return = array();
        $arFields = array('DATE', 'DURATION', 'TIME', 'PERSONAL_STREET', 'PERSONAL_PHONE', 'PERSONAL_CITY', 'NAME', 'Cleaner');
        foreach($arFields as $code){
            if ( isset($arProps[$code]) && strlen($arProps[$code]['VALUE']) > 0 ){
                $val = $arProps[$code]['VALUE'];
                $return[$code] = array();
                switch ($code){
                    case 'DATE':
                        $val = bhTools::dateFormat($val, 'detail');
                        break;
                    case 'TIME':
                        $arPeriod = bhTools::setDuration($val, $arProps['DURATION']['VALUE']);
                        $val = 'С '.$arPeriod['START_FORMATED'] .' по '.$arPeriod['FINISH_FORMATED'];
                        break;
                    case 'DURATION':
                        $val = round($val, 1).' ч';
                        break;
                    case 'PERSONAL_CITY':
                        $city = CSaleLocation::GetByID($val, 'ru');
                        $val = $city['CITY_NAME'];
                        break;
                }
                $return[$code] = $arProps[$code];
                $return[$code]['VALUE_FORMATED'] = $val;
            }
        }
        return $return;
    }

    public static function getNotDone($cleanerID){
        $arFilter = array("STATUS_ID" => "A", "PROPERTY_VAL_BY_CODE_Cleaner" => $cleanerID);
        $arOrders = self::getList($arFilter);
        $arOrders = self::busyFilter($arOrders);

        return $arOrders;
    }

    public static function getList($arFilter = array()){

        $arOrders = array();
        $arSort = array('ID' => 'DESC');
        $arrFilter = array(
            '!CANCELED' => 'Y'
        );
        if ( is_array($arFilter) ){
            $arrFilter = array_merge($arrFilter, $arFilter);
        }

        $db = CSaleOrder::getList($arSort, $arrFilter);
        while( $arOrder = $db -> fetch()){
            $arOrders[$arOrder['ID']] = $arOrder;
        }
        return $arOrders;
    }

    public static function getDone($cleanerID){
        $arFilter = array("STATUS_ID" => "F", "PROPERTY_VAL_BY_CODE_Cleaner" => $cleanerID);
        $arOrders = self::getList($arFilter);
        $arOrders = self::busyFilter($arOrders);

        return $arOrders;
    }


    public static function getSummary($orderID = false, $arBasket, $paySystem){
        $DISCOUNT_PRICE = 0;
        $BASKET_PRICE = 0;
        $ids = array();
        $cnt = array();

        foreach ($arBasket['MAIN'] as $item) {
            if ( $item['DISCOUNT_PRICE'] > 0 )
                $DISCOUNT_PRICE += $item['DISCOUNT_PRICE']* $item['QUANTITY'];
            $BASKET_PRICE += $item['PRICE'] * $item['QUANTITY'];
            if ( $item['QUANTITY'] > 0 ){
                $ids[] = $item['PRODUCT_ID'];
                $cnt[$item['PRODUCT_ID']] = $item['QUANTITY'];
            }
        }

        foreach ($arBasket['ADDITIONAL'] as $item) {
            if ( $item['DISCOUNT_PRICE'] > 0 )
                $DISCOUNT_PRICE += $item['DISCOUNT_PRICE']* $item['QUANTITY'];
            $BASKET_PRICE += $item['PRICE'] * $item['QUANTITY'];
            if ( $item['QUANTITY'] > 0 ){
                $ids[] = $item['PRODUCT_ID'];
                $cnt[$item['PRODUCT_ID']] = $item['QUANTITY'];
            }
        }

        foreach ($arBasket['SERVICES'] as $item) {
            if ( $item['DISCOUNT_PRICE'] > 0 )
                $DISCOUNT_PRICE += $item['DISCOUNT_PRICE']* $item['QUANTITY'];
            $BASKET_PRICE += $item['PRICE'] * $item['QUANTITY'];
            if ( $item['QUANTITY'] > 0 ){
                $ids[] = $item['PRODUCT_ID'];
                $cnt[$item['PRODUCT_ID']] = $item['QUANTITY'];
            }
        }

        $BASKET_PRICE = $BASKET_PRICE + $DISCOUNT_PRICE;

        $SUM_PAID = 0;
        if ( $orderID > 0 ){
            $arOrder = CSaleOrder::getByID($orderID);

            if ( $arOrder['DISCOUNT_VALUE'] > 0 ){
                $DISCOUNT_PRICE += $arOrder['DISCOUNT_VALUE'];
            }

            if ( $BASKET_PRICE > $arOrder['PRICE'] ){
                $DISCOUNT_PRICE = $BASKET_PRICE - $arOrder['PRICE'];
            }
            $res = CSaleUserTransact::GetList(
                Array("ID" => "DESC"),
                array(
                    "ORDER_ID" => $orderID,
                    'DEBIT'=>'N',
                    'DESCRIPTION'=>'PAYED by free hours')
            );

            while ($r = $res->Fetch() ){
                if ( $r['ORDER_ID']>0 ) $SUM_PAID += $r['AMOUNT'];
            }
        }

        if ( $SUM_PAID > 0 ){
            $SUM_PAID = round($SUM_PAID);
            $SUM_PAID_FORMATED = SaleFormatCurrency($SUM_PAID, 'RUB');
        }

        if ( $DISCOUNT_PRICE > 0 ){
            $DISCOUNT_PRICE = floor($DISCOUNT_PRICE);
        }

        $ORDER_PRICE = $BASKET_PRICE - $DISCOUNT_PRICE;
        $NEED_TO_PAY = $ORDER_PRICE - $SUM_PAID;
        if ( $NEED_TO_PAY < 0 ){
            $NEED_TO_PAY = 0;
        }

        if ( $paySystem == 1 ){
            $pay_line = ' (оплата наличными)';
        }elseif ( $paySystem == 2 ){
            $pay_line = ' (оплата картой)';
        }

        $REWARD = $BASKET_PRICE * bhSettings::$reward;
        $return = array(
            'MINS' => bhBasket::getDuration($ids, $cnt),
            'BASKET_PRICE' => $BASKET_PRICE,
            'BASKET_PRICE_FORMATED' => $BASKET_PRICE,
//            'BASKET_PRICE_FORMATED' => SaleFormatCurrency(round($BASKET_PRICE, -1), 'RUB'),
            'ORDER_PRICE' => $ORDER_PRICE,
            'ORDER_PRICE_FORMATED' => $ORDER_PRICE,
//            'ORDER_PRICE_FORMATED' => SaleFormatCurrency(round($ORDER_PRICE, -1), 'RUB'),
            'DISCOUNT_PRICE' =>$DISCOUNT_PRICE,
            'DISCOUNT_PRICE_FORMATED' => $DISCOUNT_PRICE,
//            'DISCOUNT_PRICE_FORMATED' => SaleFormatCurrency(round($DISCOUNT_PRICE, -1), 'RUB'),
            'SUM_PAID' => $SUM_PAID,
            'SUM_PAID_FORMATED' => $SUM_PAID_FORMATED,
            'REWARD' => $REWARD,
            'REWARD_FORMATED' => SaleFormatCurrency($REWARD, 'RUB'),
            'NEED_TO_PAY' => $NEED_TO_PAY,
            'NEED_TO_PAY_FORMATED' => $NEED_TO_PAY,
//            'NEED_TO_PAY_FORMATED' => SaleFormatCurrency(round($NEED_TO_PAY, -1), 'RUB'),
            'PAYMENT' => $pay_line
        );
        return $return;
    }

    public static function getActions($arOrder, $props, $cleanerID = 0){
        $canCancel = false;
        $today = new dateTime('');
        $tomorrow = new dateTime('tomorrow');

        $date = new dateTime($props['DATE']['VALUE']);
        if ( bhTools::dateFormat($tomorrow, 'js') < bhTools::dateFormat($date, 'js') ){
            $canCancel = true;
        }

        $canTake = false;
        if ( $arOrder['STATUS_ID'] == 'A' && $arOrder['CANCEL'] != 'Y' ){
            if ( !isset($props['Cleaner']['VALUE']) || $props['Cleaner']['VALUE'] == '' ){
                $arOrders = self::getByDate($date, $cleanerID);
                $times = bhSettings::$times;
                foreach ($arOrders as $orders){
                    foreach ($orders as $id => $order){
                        $start = $order['PROPS']['TIME']['VALUE'];
                        $dur = $order['PROPS']['DURATION']['VALUE'];
                        $finish = $start + $dur + bhSettings::$SaveConst;
                        if ( in_array($start, $times) ){
                            $i = array_search($start, $times);
                            while ($times[$i] < $finish){
                                unset($times[$i]);
                                $i++;
                                if (!isset($times[$i]))
                                    break;
                            }
                        }
                    }
                }

                $start = $props['TIME']['VALUE'];
                $dur = $props['DURATION']['VALUE'];
                $finish = $start + $dur + bhSettings::$SaveConst;

                if ( in_array($start, $times) ){
                    $i = array_search($start, $times);
                    $j = 0;
                    while ($times[$i] < $finish){
                        if ( $j > 0 && $times[$i] != $j + 2 ){
                            $canTake = false;
                            break;
                        } else{
                            $canTake = true;
                        }
                        $j = $times[$i];
                        $i++;
                        if (!isset($times[$i])) break;
                    }
                } else {

                }
            }
        }

        $canFinish = false;
        if ( bhTools::dateFormat($today, 'js') >= bhTools::dateFormat($date, 'js') ){
            if ( $today->format('H') > $props['TIME']['VALUE'] && $arOrder['STATUS_ID'] == 'A'){
                $canFinish = true;
            }
        }

        $canEdit = false;
        if ( $arOrder['PAYED'] != 'Y' ){
            $canEdit = true;
        }

        $deny = false;
        if ( !isset($props['Cleaner']['VALUE']) || $props['Cleaner']['VALUE'] != $cleanerID){
            $deny = true;
        }

        return array(
            'TAKE' => $canTake ?'Y':'N',
            'FINISH' => $canFinish ?'Y':'N',
            'EDIT' => $canEdit ?'Y':'N',
            'CANCEL' => $canCancel ?'Y':'N',
            'DENY' => $deny ? 'Y':'N'
        );
    }

    public static function getByDate($date, $cleanerID = false){
        $date = bhTools::dateFormat($date, 'date');

        $arFilter = array("STATUS_ID" => "A", "PROPERTY_VAL_BY_CODE_DATE" => $date);
        if ( $cleanerID > 0 ){
            $arFilter['PROPERTY_VAL_BY_CODE_Cleaner'] = $cleanerID;
        }
        $arOrders = self::getList($arFilter);
        $arOrders = self::sortByDate($arOrders);
        return $arOrders;
    }

    public static function getByDatesCount($work_days){
        $arCount = array();

        $arOrders = self::getList(array('!STATUS' => 'F', 'PROPERTY_VAL_BY_CODE_DATE' => $work_days));
        if ( !empty( $arOrders) ) {
            $db = CSaleOrderPropsValue::GetList(array('VALUE' => 'ASC'),
                array(
                    'ORDER_ID' => array_keys($arOrders),
                    'CODE' => 'DATE')
            );
            while ( $prop = $db->fetch() ) {
                $arCount[$prop['VALUE']] += 1;
            }
        }

        return $arCount;
    }

    public static function getOrderPropVariants($ID = array()){
        $arVariant = array();

        if ( !empty($ID) ) {
            $db2 = CSaleOrderPropsVariant::GetList(array(), array('ORDER_PROPS_ID' => array_keys($ID)));
            while ( $arVals = $db2->fetch() ) {
                $arVariant[$arVals['ORDER_PROPS_ID']][$arVals['VALUE']] = $arVals;
            }
        }
        return $arVariant;
    }

    public static function getOrderPropIDsByCode($code){
        $db_ptype = CSalePersonType::GetList(Array("SORT" => "ASC"), Array());
        $arTypes = array();
        while ($ptype = $db_ptype->Fetch()){
            $arTypes[$ptype["ID"]] = $ptype["ID"];
        }

        if ( empty($arTypes) ) return;

        //get order props by person type
        $arPropIDs = array();
        $db = CSaleOrderProps::getList(array(),
            array(
                'PERSON_TYPE_ID' => $arTypes,
                'CODE' => $code,
                'ACTIVE' => 'Y'
            )
        );
        while ($arProp = $db ->fetch()){
            if($arProp['TYPE'] == 'SELECT'){
                $arPropIDs[$arProp['ID']] = $arProp['PERSON_TYPE_ID'];
            }
        }

        return $arPropIDs;
    }

    public static function onCancel($ID){
        $arOrder = CSaleOrder::getByID($ID);
        if ( !empty($arOrder) ){
            global $USER;
            if($arOrder['USER_ID'] == $USER->GetID()){
                CSaleOrder::StatusOrder($ID, 'C');
            }else{
                CSaleOrder::StatusOrder($ID, 'M');
            }
            $cleanerID = 0;
            $db_props = CSaleOrderPropsValue::GetOrderProps($ID);
            while ($prop = $db_props->Fetch()) {
                if ( $prop['CODE'] == 'PERSONAL_PHONE' ){
                    $phone = trim($prop['VALUE']);
                } elseif ($prop['CODE'] == 'DATE'){
                    $date = new DateTime($prop['VALUE']);
                } elseif ( $prop['CODE'] == 'TIME' ){
                    $time = trim($prop['VALUE']);
                } elseif ( $prop['CODE'] == 'Cleaner' ){
                    $cleanerID = trim($prop['VALUE']);
                }
            }

            //$stringSms = 'Ваша уборка отменена. До свежей встречи!';
            //if ( strlen($phone) > 0 ){
                //bhTools::sendSms($phone, $stringSms);
            //}
           // if ( $cleanerID > 0 ) {
            //    bhCleaner::sendCancel($ID, $cleanerID, $date, $time);

              //  $arCleaner = bhTools::formatUser($cleanerID);
              //  $arCleaner = $arCleaner[$cleanerID];
               // $token = bhSettings::$mandrillKey;
               // $mandrill = new Mandrill($token);
                //$mandrill->messages->sendTemplate(
                 //   'cancel-to-cleaner',
                 //   array(),
                 //   array(
                        //'subject'=>Заказ *|ORDER_ID|* отменен,
                 //       'to' => array(
                  //          array(
                  //              'email' => $arCleaner['EMAIL'],
                  //              'name' => $arCleaner['NAME'],
                  ////              'type' => 'to'
                   //         )
                 //       ),
                 //       'global_merge_vars' => array(
                  //          array('name' => 'ORDER_ID', 'content' => $ID),
                 //           array('name' => 'NAME', 'content' => $arCleaner['NAME']),
                 //           array('name' => 'DEFAULT_EMAIL_FROM', 'content' => 'hello@' . $_SERVER['SERVER_NAME']),
                //            array('name' => 'SITE_NAME', 'content' => $_SERVER['SERVER_NAME']),
                 //           array('name' => 'SERVER_NAME', 'content' => $_SERVER['SERVER_NAME'])
                 //       ),
                 //       'merge' => 'Y')
                //);
            //}
            //$token = bhSettings::$mandrillKey;
            //$mandrill = new Mandrill($token);
            //$mandrill->messages->sendTemplate(
             //   'cancel-to-manager',
             //   array(),
             //   array(
                    //'subject'=>Заказ *|ORDER_ID|* отменен,
             //       'to' => array(
             //           array(
             //               'email' => 'hello@' . $_SERVER['SERVER_NAME'],
             //               'name' => 'getTidy',
              //              'type' => 'to'
             //           )
             //       ),
             //       'global_merge_vars' => array(
             //           array('name' => 'ORDER_ID', 'content' => $ID),
             //           array('name' => 'DEFAULT_EMAIL_FROM', 'content' => 'hello@' . $_SERVER['SERVER_NAME']),
             //           array('name' => 'SITE_NAME', 'content' => $_SERVER['SERVER_NAME']),
             //           array('name' => 'SERVER_NAME', 'content' => $_SERVER['SERVER_NAME'])
            //        ),
             //       'merge' => 'Y')
            //);
          //  $_SESSION['count']

            if ( $arOrder['PAY_SYSTEM_ID'] == 2 ){
                //unblock 1RUB
                $blockAmount  = bhPayture::getStatus($ID);
                if ($blockAmount > 0){
                    $unblock = bhPayture::getUnblock($ID, $blockAmount);
                    file($unblock);
                }
            }
        }

    }

    public static function onPay($ID){
        $utm_source = false;
        $db_vals = CSaleOrderPropsValue::GetList(
            array("SORT" => "ASC"),
            array(
                "ORDER_ID" => $ID,
                "CODE" => array('SALE_SOURCE_UTM', 'HOUR_PRICE')
            )
        );
        while ($arVals = $db_vals->Fetch()){
            if(strlen($arVals["VALUE"])>0){
                switch($arVals["CODE"]){
                    case 'SALE_SOURCE_UTM':
                        $utm_source = $arVals["VALUE"];
                        break;
                    case 'SALE_COUPON_UTM':
                        $utm_coupon = $arVals["VALUE"];
                        break;
                    case 'HOUR_PRICE':
                        $hour_price = $arVals["VALUE"];
                        break;
                }
            }
        }
        if($utm_source)
        {
            if(!$hour_price || $hour_price<=0){
                $hour_price = bhSettings::$hour_price_spb;
            }
            if($utm_coupon) {
                $db_coupon = CCatalogDiscountCoupon::GetList(array(), array('COUPON' => $utm_coupon, 'ACTIVE' => 'Y'));
                if ($arCoupon = $db_coupon->fetch()) {
                    $discount_id = $arCoupon['DISCOUNT_ID'];
                    $arDiscount = CCatalogDiscount::getByID($discount_id);
                    $arSaleDiscount = CSaleDiscount::GetByID($arDiscount['SORT']);
                    $value = $arSaleDiscount['DISOCUNT_VALUE'];
                }
            }else{
                $value = $hour_price/2;
            }
            CSaleUserAccount::UpdateAccount($utm_source, $value, "RUB", "BONUS FOR REPOST", intVal($ID));
            $friends = 0;
            $db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$utm_source), array('SELECT'=>array('UF_FRIENDS')));
            while($sr = $db->Fetch()){
                if(strlen($sr['UF_FRIENDS']))
                    $friends = intVal($sr['UF_FRIENDS']);
            }
            $oUser = new CUser;

            $friends = $friends +1;
            $aFields = array(
                'UF_FRIENDS' => $friends
            );

            $oUser->Update($utm_source, $aFields);
        }
    }
}
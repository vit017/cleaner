<?php
IncludeModuleLangFile(__FILE__);
class RetailCrmOrder
{
    public static $MODULE_ID = 'intaro.retailcrm';
    public static $CRM_API_HOST_OPTION = 'api_host';
    public static $CRM_API_KEY_OPTION = 'api_key';
    public static $CRM_ORDER_TYPES_ARR = 'order_types_arr';
    public static $CRM_DELIVERY_TYPES_ARR = 'deliv_types_arr';
    public static $CRM_PAYMENT_TYPES = 'pay_types_arr';
    public static $CRM_PAYMENT_STATUSES = 'pay_statuses_arr';
    public static $CRM_PAYMENT = 'payment_arr'; //order payment Y/N
    public static $CRM_ORDER_LAST_ID = 'order_last_id';
    public static $CRM_SITES_LIST = 'sites_list';
    public static $CRM_ORDER_PROPS = 'order_props';
    public static $CRM_LEGAL_DETAILS = 'legal_details';
    public static $CRM_CUSTOM_FIELDS = 'custom_fields';
    public static $CRM_CONTRAGENT_TYPE = 'contragent_type';
    public static $CRM_ORDER_FAILED_IDS = 'order_failed_ids';
    public static $CRM_ORDER_HISTORY_DATE = 'order_history_date';
    public static $CRM_CATALOG_BASE_PRICE = 'catalog_base_price';
    public static $CRM_ORDER_NUMBERS = 'order_numbers';

    const CANCEL_PROPERTY_CODE = 'INTAROCRM_IS_CANCELED';

    /**
     *
     * Creates order or returns order for mass upload
     *
     * @param array $arFields
     * @param $api
     * @param $arParams
     * @param $send
     * @return boolean
     * @return array - array('order' = $order, 'customer' => $customer)
     */
    public static function orderSend($arFields, $api, $arParams, $send = false, $site = null, $methodApi = 'ordersEdit')
    {
        if (!$api || empty($arParams)) { // add cond to check $arParams
            return false;
        }
        if (empty($arFields)) {
            RCrmActions::eventLog('RetailCrmOrder::orderSend', 'empty($arFields)', 'incorrect order');
            return false;
        }

        $order = array(
            'number'          => $arFields['NUMBER'],
            'externalId'      => $arFields['ID'],
            'createdAt'       => new \DateTime($arFields['DATE_INSERT']),
            'customer'        => array('externalId' => $arFields['USER_ID']),
            'paymentType'     => isset($arParams['optionsPayTypes'][$arFields['PAYMENTS'][0]]) ?
                                     $arParams['optionsPayTypes'][$arFields['PAYMENTS'][0]] : '',
            'paymentStatus'   => isset($arParams['optionsPayment'][$arFields['PAYED']]) ?
                                     $arParams['optionsPayment'][$arFields['PAYED']] : '',
            'orderType'       => isset($arParams['optionsOrderTypes'][$arFields['PERSON_TYPE_ID']]) ?
                                     $arParams['optionsOrderTypes'][$arFields['PERSON_TYPE_ID']] : '',
            'status'          => isset($arParams['optionsPayStatuses'][$arFields['STATUS_ID']]) ?
                                     $arParams['optionsPayStatuses'][$arFields['STATUS_ID']] : '',
            'statusComment'   => $arFields['REASON_CANCELED'],
            'customerComment' => $arFields['USER_DESCRIPTION'],
            'managerComment'  => $arFields['COMMENTS'],
            'delivery' => array(
                'cost' => $arFields['PRICE_DELIVERY']
            ),
        );
        if (isset($_COOKIE['_rc']) && $_COOKIE['_rc'] != '') {
            $order['customer']['browserId'] = $_COOKIE['_rc'];
        }
        $order['contragent']['contragentType'] = $arParams['optionsContragentType'][$arFields['PERSON_TYPE_ID']];

        //��������
        foreach ($arFields['PROPS']['properties'] as $prop) {
            if ($search = array_search($prop['CODE'], $arParams['optionsLegalDetails'][$arFields['PERSON_TYPE_ID']])) {
                $order['contragent'][$search] = $prop['VALUE'][0];//�� ������ ������
            } elseif ($search = array_search($prop['CODE'], $arParams['optionsCustomFields'][$arFields['PERSON_TYPE_ID']])) {
                $order['customFields'][$search] = $prop['VALUE'][0];//��������� ��������
            } elseif ($search = array_search($prop['CODE'], $arParams['optionsOrderProps'][$arFields['PERSON_TYPE_ID']])) {//���������
                if (in_array($search, array('fio', 'phone', 'email'))) {//���, �������, �����
                    if ($search == 'fio') {
                        $order = array_merge($order, RCrmActions::explodeFIO($prop['VALUE'][0]));//��������� ���� ���
                    } else {
                        $order[$search] = $prop['VALUE'][0];//������� � �����
                    }
                } else {//��������� - �����
                    if ($prop['TYPE'] == 'LOCATION' && isset($prop['VALUE'][0]) && $prop['VALUE'][0] != '') {
                        $arLoc = \Bitrix\Sale\Location\LocationTable::getByCode($prop['VALUE'][0])->fetch();
                        if ($arLoc) {
                            $server = \Bitrix\Main\Context::getCurrent()->getServer()->getDocumentRoot();
                            $countrys = array();
                            if (file_exists($server . '/bitrix/modules/intaro.retailcrm/classes/general/config/objects.xml')) {
                                $countrysFile = simplexml_load_file($server . '/bitrix/modules/intaro.retailcrm/classes/general/config/country.xml'); 
                                foreach ($countrysFile->country as $country) {
                                    $countrys[RCrmActions::fromJSON((string) $country->name)] = (string) $country->alpha;
                                }
                            }
                            $location = \Bitrix\Sale\Location\Name\LocationTable::getList(array(
                                'filter' => array('=LOCATION_ID' => $arLoc['CITY_ID'], 'LANGUAGE_ID' => 'ru')
                            ))->fetch();
                            if (count($countrys) > 0) {
                                $countryOrder = \Bitrix\Sale\Location\Name\LocationTable::getList(array(
                                    'filter' => array('=LOCATION_ID' => $arLoc['COUNTRY_ID'], 'LANGUAGE_ID' => 'ru')
                                ))->fetch();
                                if(isset($countrys[$countryOrder['NAME']])){
                                    $order['countryIso'] = $countrys[$countryOrder['NAME']];
                                }
                            }
                            
                        }
                        $prop['VALUE'][0] = $location['NAME'];
                    }

                    $order['delivery']['address'][$search] = $prop['VALUE'][0];
                }
            }
        }

        //��������
        if (array_key_exists($arFields['DELIVERYS'][0]['id'], $arParams['optionsDelivTypes'])) {
            $order['delivery']['code'] = $arParams['optionsDelivTypes'][$arFields['DELIVERYS'][0]['id']];
            if (isset($arFields['DELIVERYS'][0]['service']) && $arFields['DELIVERYS'][0]['service'] != '') {
                $order['delivery']['service']['code'] = $arFields['DELIVERYS'][0]['service'];
            }
        }

        //�������
        foreach ($arFields['BASKET'] as $product) {
            $item = array(
                'quantity'        => $product['QUANTITY'],
                'offer'           => array('externalId' => $product['PRODUCT_ID'],
                                           'xmlId' => $product['PRODUCT_XML_ID']
                                        ),
                'productName'     => $product['NAME']
            );

            $pp = CCatalogProduct::GetByID($product['PRODUCT_ID']);
            if (is_null($pp['PURCHASING_PRICE']) == false) {
                $item['purchasePrice'] = $pp['PURCHASING_PRICE'];
            }
            $item['discount'] = (double) $product['DISCOUNT_PRICE'];
            $item['discountPercent'] = 0;
            $item['initialPrice'] = (double) $product['PRICE'] + (double) $product['DISCOUNT_PRICE'];

            $order['items'][] = $item;
        }

        //��������
        if (function_exists('retailCrmBeforeOrderSend')) {
            $newResOrder = retailCrmBeforeOrderSend($order, $arFields);
            if (is_array($newResOrder) && !empty($newResOrder)) {
                $order = $newResOrder;
            }
        }

        $normalizer = new RestNormalizer();
        $order = $normalizer->normalize($order, 'orders');

        if (isset($arParams['optionsSitesList']) && is_array($arParams['optionsSitesList']) &&
                array_key_exists($arFields['LID'], $arParams['optionsSitesList'])) {
            $site = $arParams['optionsSitesList'][$arFields['LID']];
        }

        $log = new Logger();
        $log->write($order, 'order');

        if($send) {
            if (!RCrmActions::apiMethod($api, $methodApi, __METHOD__, $order, $site)) {
                return false;
            }
        }

        return $order;
    }
    
    /**
     * Mass order uploading, without repeating; always returns true, but writes error log
     * @param $pSize
     * @param $failed -- flag to export failed orders
     * @return boolean
     */
    public static function uploadOrders($pSize = 50, $failed = false, $orderList = false)
    {
        if (!CModule::IncludeModule("iblock")) {
            RCrmActions::eventLog('RetailCrmOrder::uploadOrders', 'iblock', 'module not found');
            return true;
        }
        if (!CModule::IncludeModule("sale")) {
            RCrmActions::eventLog('RetailCrmOrder::uploadOrders', 'sale', 'module not found');
            return true;
        }
        if (!CModule::IncludeModule("catalog")) {
            RCrmActions::eventLog('RetailCrmOrder::uploadOrders', 'catalog', 'module not found');
            return true;
        }

        $resOrders = array();
        $resCustomers = array();
        $orderIds = array();

        $lastUpOrderId = COption::GetOptionString(self::$MODULE_ID, self::$CRM_ORDER_LAST_ID, 0);
        $failedIds = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_ORDER_FAILED_IDS, 0));

        if ($failed == true && $failedIds !== false && count($failedIds) > 0) {
            $orderIds = $failedIds;
        } elseif ($orderList !== false && count($orderList) > 0) {
            $orderIds = $orderList;
        } else {
            $dbOrder = \Bitrix\Sale\Internals\OrderTable::GetList(array(
                'order'   => array("ID" => "ASC"),
                'filter'  => array('>ID' => $lastUpOrderId),
                'limit'   => $pSize,
                'select'  => array('ID')
            ));
            while ($arOrder = $dbOrder->fetch()) {
                $orderIds[] = $arOrder['ID'];
            }
        }

        if (count($orderIds)<=0) {
            return false;
        }

        $api_host = COption::GetOptionString(self::$MODULE_ID, self::$CRM_API_HOST_OPTION, 0);
        $api_key = COption::GetOptionString(self::$MODULE_ID, self::$CRM_API_KEY_OPTION, 0);

        $optionsSitesList = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_SITES_LIST, 0));        
        $optionsOrderTypes = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_ORDER_TYPES_ARR, 0));
        $optionsDelivTypes = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_DELIVERY_TYPES_ARR, 0));
        $optionsPayTypes = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_PAYMENT_TYPES, 0));
        $optionsPayStatuses = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_PAYMENT_STATUSES, 0)); // --statuses
        $optionsPayment = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_PAYMENT, 0));
        $optionsOrderProps = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_ORDER_PROPS, 0));
        $optionsLegalDetails = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_LEGAL_DETAILS, 0));
        $optionsContragentType = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_CONTRAGENT_TYPE, 0));
        $optionsCustomFields = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_CUSTOM_FIELDS, 0));

        $api = new RetailCrm\ApiClient($api_host, $api_key);

        $arParams = array(
            'optionsOrderTypes'     => $optionsOrderTypes,
            'optionsDelivTypes'     => $optionsDelivTypes,
            'optionsPayTypes'       => $optionsPayTypes,
            'optionsPayStatuses'    => $optionsPayStatuses,
            'optionsPayment'        => $optionsPayment,
            'optionsOrderProps'     => $optionsOrderProps,
            'optionsLegalDetails'   => $optionsLegalDetails,
            'optionsContragentType' => $optionsContragentType,
            'optionsSitesList'      => $optionsSitesList,
            'optionsCustomFields'   => $optionsCustomFields,
        );

        $recOrders = array();
        foreach ($orderIds as $orderId) {
            $id = \Bitrix\Sale\Order::load($orderId);
            if (!$id) {
                continue;
            }
            $order = self::orderObjToArr($id);
            $user = Bitrix\Main\UserTable::getById($order['USER_ID'])->fetch();
            
            $site = count($optionsSitesList) > 1 ? $optionsSitesList[$order['LID']] : null;

            $arCustomers = RetailCrmUser::customerSend($user, $api, $optionsContragentType[$order['PERSON_TYPE_ID']], false, $site);
            $arOrders = self::orderSend($order, $api, $arParams, false, $site); 

            if (!$arCustomers || !$arOrders) {
                continue;
            }
            
            $resCustomers[$order['LID']][] = $arCustomers;
            $resOrders[$order['LID']][] = $arOrders; 
            
            $recOrders[] = $orderId;
        }

        if (count($resOrders) > 0) {
            foreach ($resCustomers as $key => $customerLoad) {
                $site = count($optionsSitesList) > 1 ? $optionsSitesList[$key] : null;
                if (RCrmActions::apiMethod($api, 'customersUpload', __METHOD__, $customerLoad, $site) === false) {
                    return false;
                }
                if (count($optionsSitesList) > 1) {
                    time_nanosleep(0, 250000000);
                }
            }
            foreach ($resOrders as $key => $orderLoad) {
                $site = count($optionsSitesList) > 1 ? $optionsSitesList[$key] : null;
                if (RCrmActions::apiMethod($api, 'ordersUpload', __METHOD__, $orderLoad, $site) === false) {
                    return false;
                }
                if (count($optionsSitesList) > 1) {
                    time_nanosleep(0, 250000000);
                }
            }
            if ($failed == true && $failedIds !== false && count($failedIds) > 0) {
                COption::SetOptionString(self::$MODULE_ID, self::$CRM_ORDER_FAILED_IDS, serialize(array_diff($failedIds, $recOrders)));
            } elseif ($lastUpOrderId < max($recOrders) && $orderList === false) {
                COption::SetOptionString(self::$MODULE_ID, self::$CRM_ORDER_LAST_ID, max($recOrders));
            }
        }

        return true;
    }

    public static function orderObjToArr($obOrder)
    {
        $arOrder = array(
            'ID'               => $obOrder->getId(),
            'NUMBER'           => $obOrder->getField('ACCOUNT_NUMBER'),
            'LID'              => $obOrder->getSiteId(),
            'DATE_INSERT'      => $obOrder->getDateInsert(),
            'STATUS_ID'        => $obOrder->getField('STATUS_ID'),
            'USER_ID'          => $obOrder->getUserId(),
            'PERSON_TYPE_ID'   => $obOrder->getPersonTypeId(),
            'CURRENCY'         => $obOrder->getCurrency(),
            'PAYMENTS'         => $obOrder->getPaymentSystemId(),
            'PAYED'            => $obOrder->isPaid() ? 'Y' : 'N',
            'DELIVERYS'        => array(),
            'PRICE_DELIVERY'   => $obOrder->getDeliveryPrice(),
            'PROPS'            => $obOrder->getPropertyCollection()->getArray(),
            'DISCOUNTS'        => $obOrder->getDiscount()->getApplyResult(),
            'BASKET'           => array(),
            'USER_DESCRIPTION' => $obOrder->getField('USER_DESCRIPTION'),
            'COMMENTS'         => $obOrder->getField('COMMENTS'),
            'REASON_CANCELED'  => $obOrder->getField('REASON_CANCELED'),
        );
        
        $shipmentList = $obOrder->getShipmentCollection();
        foreach ($shipmentList as $shipmentData) {
            if ($shipmentData->getDeliveryId()) {
                $delivery = \Bitrix\Sale\Delivery\Services\Manager::getById($shipmentData->getDeliveryId());
                if ($delivery['PARENT_ID']) {
                    $servise = explode(':', $delivery['CODE']);
                    $shipment = array('id' => $delivery['PARENT_ID'], 'service' => $servise[1]);
                } else {
                    $shipment = array('id' => $delivery['ID']);
                }
                $arOrder['DELIVERYS'][] = $shipment;
            }
        }

        $basketItems = $obOrder->getBasket();
        foreach ($basketItems as $item) {
            $arOrder['BASKET'][] = $item->getFields();
        }
     
        return $arOrder;
    }
}

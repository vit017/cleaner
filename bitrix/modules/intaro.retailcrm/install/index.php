<?php

/**
 * Module Install/Uninstall script
 * Module name: intaro.retailcrm
 * Class name:  intaro_retailcrm
 */
global $MESS;
IncludeModuleLangFile(__FILE__);
if (class_exists('intaro_retailcrm'))
    return;

class intaro_retailcrm extends CModule 
{
    var $MODULE_ID = 'intaro.retailcrm';
    var $OLD_MODULE_ID = 'intaro.intarocrm';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = 'N';
    
    var $PARTNER_NAME;
    var $PARTNER_URI;
    
    var $RETAIL_CRM_API;
    var $RETAIL_CRM_EXPORT = 'retailcrm';
    var $CRM_API_HOST_OPTION = 'api_host';
    var $CRM_API_KEY_OPTION = 'api_key';
    var $CRM_SITES_LIST= 'sites_list';
    var $CRM_ORDER_TYPES_ARR = 'order_types_arr';
    var $CRM_DELIVERY_TYPES_ARR = 'deliv_types_arr';
    var $CRM_DELIVERY_SERVICES_ARR = 'deliv_services_arr';
    var $CRM_PAYMENT_TYPES = 'pay_types_arr';
    var $CRM_PAYMENT_STATUSES = 'pay_statuses_arr';
    var $CRM_PAYMENT = 'payment_arr'; //order payment Y/N
    var $CRM_ORDER_LAST_ID = 'order_last_id';
    var $CRM_ORDER_PROPS = 'order_props';
    var $CRM_LEGAL_DETAILS = 'legal_details';
    var $CRM_CUSTOM_FIELDS = 'custom_fields';
    var $CRM_CONTRAGENT_TYPE = 'contragent_type';
    var $CRM_ORDER_DISCHARGE = 'order_discharge';
    var $CRM_ORDER_FAILED_IDS = 'order_failed_ids';
    var $CRM_ORDER_HISTORY = 'order_history';
    var $CRM_CUSTOMER_HISTORY = 'customer_history';
    var $CRM_CATALOG_BASE_PRICE = 'catalog_base_price';
    var $CRM_CATALOG_IBLOCKS = 'catalog_base_iblocks';
    var $CRM_ORDER_NUMBERS = 'order_numbers';
    var $CRM_CANSEL_ORDER = 'cansel_order';
    var $INSTALL_PATH;

    function intaro_retailcrm()
    {
        $arModuleVersion = array();
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        $this->INSTALL_PATH = $path;
        include($path . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage('RETAIL_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('MODULE_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('MODULE_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('MODULE_PARTNER_URI');
    }

    /**
     * Functions DoInstall and DoUninstall are
     * All other functions are optional
     */
    function DoInstall()
    {
        global $APPLICATION, $step, $arResult;

        if (!in_array('curl', get_loaded_extensions())) {
            $APPLICATION->ThrowException(GetMessage("RETAILCRM_CURL_ERR"));
            return false;
        }
        
        $infoSale = CModule::CreateModuleObject('sale')->MODULE_VERSION;
        if (version_compare($infoSale, '16', '<=')) {
            $APPLICATION->ThrowException(GetMessage("SALE_VERSION_ERR"));
            
            return false;
        }

        if (!date_default_timezone_get()) {
            if (!ini_get('date.timezone')) {
                $APPLICATION->ThrowException(GetMessage("DATE_TIMEZONE_ERR"));
                
                return false;
            }
        }

        include($this->INSTALL_PATH . '/../classes/general/ApiClient.php');
        include($this->INSTALL_PATH . '/../classes/general/Http/Client.php');
        include($this->INSTALL_PATH . '/../classes/general/Response/ApiResponse.php');
        include($this->INSTALL_PATH . '/../classes/general/RCrmActions.php');
        include($this->INSTALL_PATH . '/../classes/general/user/RetailCrmUser.php');
        include($this->INSTALL_PATH . '/../classes/general/order/RetailCrmOrder.php');
        include($this->INSTALL_PATH . '/../classes/general/history/RetailCrmHistory.php');
        include($this->INSTALL_PATH . '/../classes/general/events/RetailCrmEvent.php');
        include($this->INSTALL_PATH . '/../classes/general/icml/RetailCrmICML.php');
        include($this->INSTALL_PATH . '/../classes/general/Exception/InvalidJsonException.php');
        include($this->INSTALL_PATH . '/../classes/general/Exception/CurlException.php');
        include($this->INSTALL_PATH . '/../classes/general/RestNormalizer.php');
        include($this->INSTALL_PATH . '/../classes/general/Logger.php');

        $step = intval($_REQUEST['step']);

        if (file_exists($this->INSTALL_PATH . '/../classes/general/config/options.xml')) {
            $options = simplexml_load_file($this->INSTALL_PATH . '/../classes/general/config/options.xml'); 

            foreach ($options->contragents->contragent as $contragent) {
                $type["NAME"] = $APPLICATION->ConvertCharset((string)$contragent, 'utf-8', SITE_CHARSET);
                $type["ID"] = (string)$contragent["id"];
                $arResult['contragentType'][] = $type;
                unset ($type);
            }
            foreach($options->fields->field as $field) {
                $type["NAME"] = $APPLICATION->ConvertCharset((string)$field, 'utf-8', SITE_CHARSET);
                $type["ID"] = (string)$field["id"];

                if ($field["group"] == 'custom') {
                    $arResult['customFields'][] = $type;
                } elseif (!$field["group"]) {
                    $arResult['orderProps'][] = $type;
                } else {
                    $groups = explode(",", (string)$field["group"]);
                    foreach ($groups as $group) {   
                        $type["GROUP"][] = trim($group);   
                    }
                    $arResult['legalDetails'][] = $type;
                }
                unset($type);
            }
        }

        if ($step == 11) {
            $arResult['arSites'] = RCrmActions::SitesList();
            if (count($arResult['arSites']) < 2) {
                $step = 2;
            }
        }
        if ($step <= 1) {
            if (!CModule::IncludeModule("sale")) {
                $arResult['errCode'] = 'ERR_SALE';
            }

            if (!CModule::IncludeModule("iblock")) {
                $arResult['errCode'] = 'ERR_IBLOCK';
            }

            if (!CModule::IncludeModule("catalog")) {
                $arResult['errCode'] = 'ERR_CATALOG';
            }
            
            if ($api_host = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_API_HOST_OPTION, 0)) {
                $arResult['API_HOST'] = $api_host;
            }
            if ($api_key = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_API_KEY_OPTION, 0)) {
                $arResult['API_KEY'] = $api_key;
            }
                
            $APPLICATION->IncludeAdminFile(
                GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step1.php'
            );
        } elseif ($step == 11) {
            //new page
            if (!CModule::IncludeModule("sale")) {
                $arResult['errCode'] = 'ERR_SALE';
            }

            if (!CModule::IncludeModule("iblock")) {
                $arResult['errCode'] = 'ERR_IBLOCK';
            }

            if (!CModule::IncludeModule("catalog")) {
                $arResult['errCode'] = 'ERR_CATALOG';
            }

            if (isset($arResult['errCode']) && $arResult['errCode']) {
                $APPLICATION->IncludeAdminFile(
                    GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step1.php'
                );
                
                return;
            }
            
            $api_host = htmlspecialchars(trim($_POST[$this->CRM_API_HOST_OPTION]));
            $api_key = htmlspecialchars(trim($_POST[$this->CRM_API_KEY_OPTION]));

            // form correct url
            $api_host = parse_url($api_host);
            if ($api_host['scheme'] != 'https') {
                $api_host['scheme'] = 'https';               
            }
            $api_host = $api_host['scheme'] . '://' . $api_host['host'];

            if (!$api_host || !$api_key) {
                $arResult['errCode'] = 'ERR_FIELDS_API_HOST';
                $APPLICATION->IncludeAdminFile(
                        GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step1.php'
                );
                
                return;
            }

            $this->RETAIL_CRM_API = new RetailCrm\ApiClient($api_host, $api_key);
            //api key ok and sites list
            try {
                $arResult['sitesList'] = $APPLICATION->ConvertCharsetArray($this->RETAIL_CRM_API->sitesList()->sites, 'utf-8', SITE_CHARSET);
            } catch (\RetailCrm\Exception\CurlException $e) {
                RCrmActions::eventLog(
                    'intaro.retailcrm/install/index.php', 'RetailCrm\ApiClient::sitesList',
                    $e->getCode() . ': ' . $e->getMessage()
                );

                $arResult['errCode'] = 'ERR_' . $e->getCode();

                $APPLICATION->IncludeAdminFile(
                    GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step1.php'
                );
                
                return;
            }
            
            COption::SetOptionString($this->MODULE_ID, $this->CRM_API_HOST_OPTION, $api_host);
            COption::SetOptionString($this->MODULE_ID, $this->CRM_API_KEY_OPTION, $api_key);
            
            if ($sites_list = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_SITES_LIST, 0)) {
                $arResult['SITES_LIST'] = unserialize($sites_list);
            }

            $APPLICATION->IncludeAdminFile(
                GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step11.php'
            );
        } else if ($step == 2) {//��������, ������, ���� �������
            if (!CModule::IncludeModule("sale")) {
                $arResult['errCode'] = 'ERR_SALE';
            }
            if (!CModule::IncludeModule("iblock")) {
                $arResult['errCode'] = 'ERR_IBLOCK';
            }
            if (!CModule::IncludeModule("catalog")) {
                $arResult['errCode'] = 'ERR_CATALOG';
            }

            if (isset($arResult['errCode']) && $arResult['errCode']) {
                $APPLICATION->IncludeAdminFile(
                        GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step1.php'
                );
                
                return;
            }
            
            $arResult['arSites'] = RCrmActions::SitesList();
            /*
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') && isset($_POST['ajax']) && ($_POST['ajax'] == 1)) {

                $api_host = COption::GetOptionString($this->MODULE_ID, $this->CRM_API_HOST_OPTION, 0);
                $api_key = COption::GetOptionString($this->MODULE_ID, $this->CRM_API_KEY_OPTION, 0);
                $this->RETAIL_CRM_API = new \RetailCrm\ApiClient($api_host, $api_key);

                //prepare crm lists
                try {
                    $arResult['orderTypesList'] = $this->RETAIL_CRM_API->orderTypesList()->orderTypes;
                } catch (\RetailCrm\Exception\CurlException $e) {
                    RCrmActions::eventLog(
                        'intaro.retailcrm/install/index.php', 'RetailCrm\ApiClient::orderTypesList::CurlException',
                        $e->getCode() . ': ' . $e->getMessage()
                    );

                    $APPLICATION->RestartBuffer();
                    header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
                    die(json_encode(array("success" => false)));
                }

                try {
                    $arResult['deliveryTypesList'] = $this->RETAIL_CRM_API->deliveryTypesList()->deliveryTypes;
                    $arResult['deliveryServicesList'] = $this->RETAIL_CRM_API->deliveryServicesList()->deliveryServices;
                    $arResult['paymentTypesList'] = $this->RETAIL_CRM_API->paymentTypesList()->paymentTypes;
                    $arResult['paymentStatusesList'] = $this->RETAIL_CRM_API->paymentStatusesList()->paymentStatuses; // --statuses
                    $arResult['paymentList'] = $this->RETAIL_CRM_API->orderStatusesList()->statuses;
                    $arResult['paymentGroupList'] = $this->RETAIL_CRM_API->orderStatusGroupsList()->statusGroups; // -- statuses groups
                } catch (\RetailCrm\Exception\CurlException $e) {
                    RCrmActions::eventLog(
                        'intaro.retailcrm/install/index.php', 'RetailCrm\ApiClient::*List::CurlException',
                        $e->getCode() . ': ' . $e->getMessage()
                    );
                }
                //bitrix orderTypesList -- personTypes
                $dbOrderTypesList = CSalePersonType::GetList(
                    array(
                        "SORT" => "ASC",
                        "NAME" => "ASC"
                    ), 
                    array(
                        "ACTIVE" => "Y",
                    ), 
                    false, false, array()
                );


                //form order types ids arr
                $orderTypesArr = array();
                if ($arOrderTypesList = $dbOrderTypesList->Fetch()) {
                    do {
                        $arResult['bitrixOrderTypesList'][] = $arOrderTypesList;
                        $orderTypesArr[$arOrderTypesList['ID']] = htmlspecialchars(trim($_POST['order-type-' . $arOrderTypesList['ID']]));
                    } while ($arOrderTypesList = $dbOrderTypesList->Fetch());
                }

                //bitrix deliveryTypesList
                $dbDeliveryTypesList = CSaleDelivery::GetList(
                                array(
                            "SORT" => "ASC",
                            "NAME" => "ASC"
                                ), array(
                            "ACTIVE" => "Y",
                                ), false, false, array()
                );

                //form delivery types ids arr
                $deliveryTypesArr = array();
                if ($arDeliveryTypesList = $dbDeliveryTypesList->Fetch()) {
                    do {
                        $arResult['bitrixDeliveryTypesList'][] = $arDeliveryTypesList;
                        $deliveryTypesArr[$arDeliveryTypesList['ID']] = htmlspecialchars(trim($_POST['delivery-type-' . $arDeliveryTypesList['ID']]));
                    } while ($arDeliveryTypesList = $dbDeliveryTypesList->Fetch());
                }

                //bitrix deliveryServicesList
                $dbDeliveryServicesList = CSaleDeliveryHandler::GetList(
                    array(
                        'SORT' => 'ASC',
                        'NAME' => 'ASC'
                    ),
                    array(
                        'ACTIVE'  => 'Y',
                        'SITE_ID' => $arResult['arSites'][0]['LID']
                    )
                );

                //form delivery services ids arr
                if ($arDeliveryServicesList = $dbDeliveryServicesList->Fetch()) {
                    do {
                        //auto delivery types
                        $deliveryTypesArr[$arDeliveryServicesList['SID']] = htmlspecialchars(trim($_POST['delivery-type-' . $arDeliveryServicesList['SID']]));
                    } while ($arDeliveryServicesList = $dbDeliveryServicesList->Fetch());
                }

                //bitrix paymentTypesList
                $dbPaymentTypesList = CSalePaySystem::GetList(
                                array(
                            "SORT" => "ASC",
                            "NAME" => "ASC"
                                ), array(
                            "ACTIVE" => "Y"
                                )
                );

                //form payment types ids arr
                $paymentTypesArr = array();
                if ($arPaymentTypesList = $dbPaymentTypesList->Fetch()) {
                    do {
                        $arResult['bitrixPaymentTypesList'][] = $arPaymentTypesList;
                        $paymentTypesArr[$arPaymentTypesList['ID']] = htmlspecialchars(trim($_POST['payment-type-' . $arPaymentTypesList['ID']]));
                    } while ($arPaymentTypesList = $dbPaymentTypesList->Fetch());
                }

                //bitrix paymentStatusesList
                $dbPaymentStatusesList = CSaleStatus::GetList(
                                array(
                            "SORT" => "ASC",
                            "NAME" => "ASC"
                                ), array(
                            "LID" => "ru", //ru 
                            "ACTIVE" => "Y"
                                )
                );

                //form payment statuses ids arr
                $paymentStatusesArr['YY'] = htmlspecialchars(trim($_POST['payment-status-YY']));
                if ($arPaymentStatusesList = $dbPaymentStatusesList->Fetch()) {
                    do {
                        $arResult['bitrixPaymentStatusesList'][$arPaymentStatusesList['ID']] = $arPaymentStatusesList;
                        $paymentStatusesArr[$arPaymentStatusesList['ID']] = htmlspecialchars(trim($_POST['payment-status-' . $arPaymentStatusesList['ID']]));
                    } while ($arPaymentStatusesList = $dbPaymentStatusesList->Fetch());
                }

                $arResult['bitrixPaymentStatusesList'][] = array(
                    'ID' => 'YY',
                    'NAME' => GetMessage('CANCELED')
                );

                //form payment ids arr
                $paymentArr = array();
                $paymentArr['Y'] = htmlspecialchars(trim($_POST['payment-Y']));
                $paymentArr['N'] = htmlspecialchars(trim($_POST['payment-N']));

                COption::SetOptionString($this->MODULE_ID, $this->CRM_ORDER_TYPES_ARR, serialize(RCrmActions::clearArr($orderTypesArr)));
                COption::SetOptionString($this->MODULE_ID, $this->CRM_DELIVERY_TYPES_ARR, serialize(RCrmActions::clearArr($deliveryTypesArr)));
                COption::SetOptionString($this->MODULE_ID, $this->CRM_PAYMENT_TYPES, serialize(RCrmActions::clearArr($paymentTypesArr)));
                COption::SetOptionString($this->MODULE_ID, $this->CRM_PAYMENT_STATUSES, serialize(RCrmActions::clearArr($paymentStatusesArr)));
                COption::SetOptionString($this->MODULE_ID, $this->CRM_PAYMENT, serialize(RCrmActions::clearArr($paymentArr)));

                // generate updated select inputs  
                $input = array();

                foreach ($arResult['bitrixDeliveryTypesList'] as $bitrixDeliveryType) {
                    $input['delivery-type-' . $bitrixDeliveryType['ID']] =
                            '<select name="delivery-type-' . $bitrixDeliveryType['ID'] . '" class="typeselect">';
                    $input['delivery-type-' . $bitrixDeliveryType['ID']] .= '<option value=""></option>';

                    foreach ($arResult['deliveryTypesList'] as $deliveryType) {
                        if ($deliveryTypesArr[$bitrixDeliveryType['ID']] == $deliveryType['code']) {
                            $input['delivery-type-' . $bitrixDeliveryType['ID']] .=
                                    '<option value="' . $deliveryType['code'] . '" selected>';
                        } else {
                            $input['delivery-type-' . $bitrixDeliveryType['ID']] .=
                                    '<option value="' . $deliveryType['code'] . '">';
                        }

                        $input['delivery-type-' . $bitrixDeliveryType['ID']] .=
                                $APPLICATION->ConvertCharset($deliveryType['name'], 'utf-8', SITE_CHARSET);
                        $input['delivery-type-' . $bitrixDeliveryType['ID']] .= '</option>';
                    }

                    $input['delivery-type-' . $bitrixDeliveryType['ID']] .= '</select>';
                }

                foreach ($arResult['bitrixPaymentTypesList'] as $bitrixPaymentType) {
                    $input['payment-type-' . $bitrixPaymentType['ID']] =
                            '<select name="payment-type-' . $bitrixPaymentType['ID'] . '" class="typeselect">';
                    $input['payment-type-' . $bitrixPaymentType['ID']] .= '<option value=""></option>';

                    foreach ($arResult['paymentTypesList'] as $paymentType) {
                        if ($paymentTypesArr[$bitrixPaymentType['ID']] == $paymentType['code']) {
                            $input['payment-type-' . $bitrixPaymentType['ID']] .=
                                    '<option value="' . $paymentType['code'] . '" selected>';
                        } else {
                            $input['payment-type-' . $bitrixPaymentType['ID']] .=
                                    '<option value="' . $paymentType['code'] . '">';
                        }

                        $input['payment-type-' . $bitrixPaymentType['ID']] .=
                                $APPLICATION->ConvertCharset($paymentType['name'], 'utf-8', SITE_CHARSET);
                        $input['payment-type-' . $bitrixPaymentType['ID']] .= '</option>';
                    }

                    $input['payment-type-' . $bitrixPaymentType['ID']] .= '</select>';
                }

                foreach ($arResult['bitrixPaymentStatusesList'] as $bitrixPaymentStatus) {
                    $input['payment-status-' . $bitrixPaymentStatus['ID']] =
                            '<select name="payment-status-' . $bitrixPaymentStatus['ID'] . '" class="typeselect">';
                    $input['payment-status-' . $bitrixPaymentStatus['ID']] .= '<option value=""></option>';

                    foreach ($arResult['paymentGroupList'] as $orderStatusGroup) {
                        if (empty($orderStatusGroup['statuses']))
                            continue;

                        $input['payment-status-' . $bitrixPaymentStatus['ID']].=
                                '<optgroup label="' . $orderStatusGroup['name'] . '">';

                        foreach ($orderStatusGroup['statuses'] as $payment) {
                            if(!isset($arResult['paymentList'][$payment])) continue;

                            if ($paymentStatusesArr[$bitrixPaymentStatus['ID']] == $arResult['paymentList'][$payment]['code']) {
                                $input['payment-status-' . $bitrixPaymentStatus['ID']] .=
                                        '<option value="' . $arResult['paymentList'][$payment]['code'] . '" selected>';
                            } else {
                                $input['payment-status-' . $bitrixPaymentStatus['ID']] .=
                                        '<option value="' . $arResult['paymentList'][$payment]['code'] . '">';
                            }

                            $input['payment-status-' . $bitrixPaymentStatus['ID']] .=
                                    $APPLICATION->ConvertCharset($arResult['paymentList'][$payment]['name'], 'utf-8', SITE_CHARSET);
                            $input['payment-status-' . $bitrixPaymentStatus['ID']] .= '</option>';
                        }

                        $input['payment-status-' . $bitrixPaymentStatus['ID']] .= '</optgroup>';
                    }

                    $input['payment-status-' . $bitrixPaymentStatus['ID']] .= '</select>';
                }

                foreach ($arResult['bitrixPaymentList'] as $bitrixPayment) {
                    $input['payment-' . $bitrixPayment['ID']] =
                            '<select name="payment-' . $bitrixPayment['ID'] . '" class="typeselect">';
                    $input['payment-' . $bitrixPayment['ID']] .= '<option value=""></option>';

                    foreach ($arResult['paymentStatusesList'] as $paymentStatus) {
                        if ($paymentArr[$bitrixPayment['ID']] == $paymentStatus['code']) {
                            $input['payment-' . $bitrixPayment['ID']] .=
                                    '<option value="' . $paymentStatus['code'] . '" selected>';
                        } else {
                            $input['payment-' . $bitrixPayment['ID']] .=
                                    '<option value="' . $paymentStatus['code'] . '">';
                        }

                        $input['payment-' . $bitrixPayment['ID']] .=
                                $APPLICATION->ConvertCharset($paymentStatus['name'], 'utf-8', SITE_CHARSET);
                        $input['payment-' . $bitrixPayment['ID']] .= '</option>';
                    }

                    $input['payment-' . $bitrixPayment['ID']] .= '</select>';
                }

                foreach ($arResult['bitrixOrderTypesList'] as $bitrixOrderType) {
                    $input['order-type-' . $bitrixOrderType['ID']] =
                            '<select name="order-type-' . $bitrixOrderType['ID'] . '" class="typeselect">';
                    $input['order-type-' . $bitrixOrderType['ID']] .= '<option value=""></option>';

                    foreach ($arResult['orderTypesList'] as $orderType) {
                        if ($orderTypesArr[$bitrixOrderType['ID']] == $orderType['code']) {
                            $input['order-type-' . $bitrixOrderType['ID']] .=
                                    '<option value="' . $orderType['code'] . '" selected>';
                        } else {
                            $input['order-type-' . $bitrixOrderType['ID']] .=
                                    '<option value="' . $orderType['code'] . '">';
                        }

                        $input['order-type-' . $bitrixOrderType['ID']] .=
                                $APPLICATION->ConvertCharset($orderType['name'], 'utf-8', SITE_CHARSET);
                        $input['order-type-' . $bitrixOrderType['ID']] .= '</option>';
                    }

                    $input['order-type-' . $bitrixOrderType['ID']] .= '</select>';
                }



                $APPLICATION->RestartBuffer();
                header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
                die(json_encode(array("success" => true, "result" => $input)));
            }*/
            
            if (count($arResult['arSites']) > 1) {
                
                $api_host = COption::GetOptionString($this->MODULE_ID, $this->CRM_API_HOST_OPTION, 0);
                $api_key = COption::GetOptionString($this->MODULE_ID, $this->CRM_API_KEY_OPTION, 0);

                foreach ($arResult['arSites'] as $site) {
                    if ($_POST['sites-id-'.$site['LID']] && !empty($_POST['sites-id-'.$site['LID']])) {
                        $siteCode[$site['LID']] = htmlspecialchars(trim($_POST['sites-id-'.$site['LID']]));
                    }
                }
                if (count($arResult['arSites']) != count($siteCode)) {
                    $arResult['errCode'] = 'ERR_FIELDS_API_HOST';
                    $APPLICATION->IncludeAdminFile(
                        GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step11.php'
                    );
                    return;
                }

                $this->RETAIL_CRM_API = new \RetailCrm\ApiClient($api_host, $api_key);
                COption::SetOptionString($this->MODULE_ID, $this->CRM_SITES_LIST, serialize($siteCode));
            } else {//���� 1 ����
                $api_host = htmlspecialchars(trim($_POST[$this->CRM_API_HOST_OPTION]));
                $api_key = htmlspecialchars(trim($_POST[$this->CRM_API_KEY_OPTION]));

                // form correct url
                $api_host = parse_url($api_host);
                if($api_host['scheme'] != 'https') {
                    $api_host['scheme'] = 'https';
                }
                $api_host = $api_host['scheme'] . '://' . $api_host['host'];

                if (!$api_host || !$api_key) {
                    $arResult['errCode'] = 'ERR_FIELDS_API_HOST';
                    $APPLICATION->IncludeAdminFile(
                            GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step1.php'
                    );
                    return;
                }
                
                $this->RETAIL_CRM_API = new \RetailCrm\ApiClient($api_host, $api_key);

                try {
                    $this->RETAIL_CRM_API->paymentStatusesList()->paymentStatuses;
                } catch (\RetailCrm\Exception\CurlException $e) {
                    RCrmActions::eventLog(
                        'intaro.retailcrm/install/index.php', 'RetailCrm\ApiClient::paymentStatusesList::CurlException',
                        $e->getCode() . ': ' . $e->getMessage()
                    );

                    $arResult['errCode'] = 'ERR_' . $e->getCode();

                    $APPLICATION->IncludeAdminFile(
                        GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step1.php'
                    );

                    return;
                }
                COption::SetOptionString($this->MODULE_ID, $this->CRM_API_HOST_OPTION, $api_host);
                COption::SetOptionString($this->MODULE_ID, $this->CRM_API_KEY_OPTION, $api_key);
            }
            
            //prepare crm lists
            try {                   
                $arResult['orderTypesList'] = $this->RETAIL_CRM_API->orderTypesList()->orderTypes;
                $arResult['deliveryTypesList'] = $this->RETAIL_CRM_API->deliveryTypesList()->deliveryTypes;
                $arResult['deliveryServicesList'] = $this->RETAIL_CRM_API->deliveryServicesList()->deliveryServices;
                $arResult['paymentTypesList'] = $this->RETAIL_CRM_API->paymentTypesList()->paymentTypes;
                $arResult['paymentStatusesList'] = $this->RETAIL_CRM_API->paymentStatusesList()->paymentStatuses;
                $arResult['paymentList'] = $this->RETAIL_CRM_API->statusesList()->statuses;
                $arResult['paymentGroupList'] = $this->RETAIL_CRM_API->statusGroupsList()->statusGroups;
            } catch (\RetailCrm\Exception\CurlException $e) {
                RCrmActions::eventLog(
                    'intaro.retailcrm/install/index.php', 'RetailCrm\ApiClient::*List::CurlException',
                    $e->getCode() . ': ' . $e->getMessage()
                );
            }
            $delivTypes = array();
            foreach ($arResult['deliveryTypesList'] as $delivType) {
                if ($delivType['active'] === true) {
                    $delivTypes[$delivType['code']] = $delivType;
                }
            }
            $arResult['deliveryTypesList'] = $delivTypes;
            
            //bitrix personTypes
            $arResult['bitrixOrderTypesList'] = RCrmActions::OrderTypesList($arResult['arSites']);
            
            //bitrix deliveryList
            $arResult['bitrixDeliveryTypesList'] = RCrmActions::DeliveryList();

            //bitrix paymentList
            $arResult['bitrixPaymentTypesList'] = RCrmActions::PaymentList();

            //bitrix statusesList --statuses
            $arResult['bitrixStatusesList'] = RCrmActions::StatusesList();
           
            if ($order_types = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_ORDER_TYPES_ARR, 0)) {
                $arResult['ORDER_TYPES'] = array_flip(unserialize($order_types));
            }
            if ($delivery_types = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_DELIVERY_TYPES_ARR, 0)) {
                $arResult['DELIVERY_TYPES'] = array_flip(unserialize($delivery_types));
            }
            if ($payment_types = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_PAYMENT_TYPES, 0)) {
                $arResult['PAYMENT_TYPES'] = array_flip(unserialize($payment_types));
            }
            if ($payment_statuses = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_PAYMENT_STATUSES, 0)) {
                $arResult['PAYMENT_STATUSES'] = array_flip(unserialize($payment_statuses));
            }
            if ($payment = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_PAYMENT, 0)) {
                $arResult['PAYMENT'] = array_flip(unserialize($payment));
            }

            $APPLICATION->IncludeAdminFile(
                    GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step2.php'
            );
        } elseif ($step == 3) {//������������� ������� ������
            if (!CModule::IncludeModule("sale")) {
                $arResult['errCode'] = 'ERR_SALE';
            }

            if (isset($_POST['back']) && $_POST['back']) {
                $APPLICATION->IncludeAdminFile(
                        GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step1.php'
                );
            }

            // api load
            $api_host = COption::GetOptionString($this->MODULE_ID, $this->CRM_API_HOST_OPTION, 0);
            $api_key = COption::GetOptionString($this->MODULE_ID, $this->CRM_API_KEY_OPTION, 0);
            $this->RETAIL_CRM_API = new \RetailCrm\ApiClient($api_host, $api_key);

            //bitrix orderTypesList
            $arResult['arSites'] = RCrmActions::SitesList();
            $arResult['bitrixOrderTypesList'] = RCrmActions::OrderTypesList($arResult['arSites']);
            
            $orderTypesArr = array();
            foreach ($arResult['bitrixOrderTypesList'] as $orderType) {
                $orderTypesArr[$orderType['ID']] = htmlspecialchars(trim($_POST['order-type-' . $orderType['ID']]));
            }

            //bitrix deliveryTypesList
            $arResult['bitrixDeliveryTypesList'] = RCrmActions::DeliveryList();
            
            if (htmlspecialchars(trim($_POST['delivery-types-export'])) == 'false') {//���� ����������� ��������
                $deliveryTypesArr = array();
                foreach ($arResult['bitrixDeliveryTypesList'] as $delivery) {
                    $deliveryTypesArr[$delivery['ID']] = htmlspecialchars(trim($_POST['delivery-type-' . $delivery['ID']]));
                }
            } elseif (htmlspecialchars(trim($_POST['delivery-types-export'])) == 'true') {//�������� �������� � ���
                // send to intaro crm and save delivery types!
                $arDeliveryServiceAll = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
                foreach ($arResult['bitrixDeliveryTypesList'] as $deliveryType) {
                    $load = true;
                    try {
                        $this->RETAIL_CRM_API->deliveryTypesEdit(RCrmActions::clearArr(array(
                            'code' => $deliveryType['ID'],
                            'name' => RCrmActions::toJSON($deliveryType['NAME']),
                            'defaultCost' => $deliveryType['CONFIG']['MAIN']['PRICE'],
                            'description' => RCrmActions::toJSON($deliveryType['DESCRIPTION']),
                            'paymentTypes' => ''
                        )));
                    } catch (\RetailCrm\Exception\CurlException $e) {
                        $load = false;
                        RCrmActions::eventLog(
                            'intaro.crm/install/index.php', 'RetailCrm\ApiClient::deliveryTypeEdit::CurlException',
                            $e->getCode() . ': ' . $e->getMessage()
                        );
                    }
                    if ($load) {
                        $deliveryTypesArr[$deliveryType['ID']] = $deliveryType['ID'];
                        foreach ($arDeliveryServiceAll as $deliveryService) {
                            if ($deliveryService['PARENT_ID'] != 0 && $deliveryService['PARENT_ID'] == $deliveryType['ID']) {
                                $srv = explode(':', $deliveryService['CODE']);
                                if (count($srv) == 2) {
                                    try {
                                        $this->RETAIL_CRM_API->deliveryServicesEdit(RCrmActions::clearArr(array(
                                            'code' => $srv[1],
                                            'name' => RCrmActions::toJSON($deliveryService['NAME']),
                                            'deliveryType' => $deliveryType['ID']
                                        )));
                                    } catch (\RetailCrm\Exception\CurlException $e) {
                                        RCrmActions::eventLog(
                                            'intaro.crm/install/index.php', 'RetailCrm\ApiClient::deliveryServiceEdit::CurlException',
                                            $e->getCode() . ': ' . $e->getMessage()
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //bitrix paymentTypesList
            $arResult['bitrixPaymentTypesList'] = RCrmActions::PaymentList();
            
            $paymentTypesArr = array();
            foreach ($arResult['bitrixPaymentTypesList'] as $payment) {
                $paymentTypesArr[$payment['ID']] = htmlspecialchars(trim($_POST['payment-type-' . $payment['ID']]));
            }

            //bitrix statusesList
            $arResult['bitrixStatusesList'] = RCrmActions::StatusesList();
            
            $paymentStatusesArr = array();
            $canselOrderArr = array();

            foreach ($arResult['bitrixStatusesList'] as $status) {
                $paymentStatusesArr[$status['ID']] = htmlspecialchars(trim($_POST['payment-status-' . $status['ID']]));
                if (trim($_POST['order-cansel-' . $status['ID']]) == 'Y') {
                    $canselOrderArr[] = $status['ID'];
                }
            }

            //form payment ids arr
            $paymentArr = array();
            $paymentArr['Y'] = htmlspecialchars(trim($_POST['payment-Y']));
            $paymentArr['N'] = htmlspecialchars(trim($_POST['payment-N']));
            
            //new page
            //form orderProps 
            $arResult['arProp'] = RCrmActions::OrderPropsList();

            COption::SetOptionString($this->MODULE_ID, $this->CRM_ORDER_TYPES_ARR, serialize(RCrmActions::clearArr($orderTypesArr)));
            COption::SetOptionString($this->MODULE_ID, $this->CRM_DELIVERY_TYPES_ARR, serialize(RCrmActions::clearArr($deliveryTypesArr)));
            COption::SetOptionString($this->MODULE_ID, $this->CRM_PAYMENT_TYPES, serialize(RCrmActions::clearArr($paymentTypesArr)));
            COption::SetOptionString($this->MODULE_ID, $this->CRM_PAYMENT_STATUSES, serialize(RCrmActions::clearArr($paymentStatusesArr)));
            COption::SetOptionString($this->MODULE_ID, $this->CRM_PAYMENT, serialize(RCrmActions::clearArr($paymentArr)));
            COption::SetOptionString($this->MODULE_ID, $this->CRM_ORDER_LAST_ID, 0);
            COption::SetOptionString($this->MODULE_ID, $this->CRM_ORDER_DISCHARGE, 1);
            COption::SetOptionString($this->MODULE_ID, $this->CRM_ORDER_FAILED_IDS, serialize(array()));
            COption::SetOptionString($this->MODULE_ID, $this->CRM_CANSEL_ORDER, serialize(RCrmActions::clearArr($canselOrderArr)));
            
            if ($orderProps = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_ORDER_PROPS, 0)) {
                $arResult['ORDER_PROPS'] = unserialize($orderProps);
            }
            if ($customFields = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_CUSTOM_FIELDS, 0)) {
                $arResult['CUSTOM_FIELDS'] = unserialize($customFields);
            }
            if ($legalDetails = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_LEGAL_DETAILS, 0)) {
                $arResult['LEGAL_DETAILS'] = unserialize($legalDetails);
            }
            if ($contragentType = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_CONTRAGENT_TYPE, 0)) {
                $arResult['CONTRAGENT_TYPES'] = unserialize($contragentType);
            }
            
            $APPLICATION->IncludeAdminFile(
                    GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step3.php'
            );
        } elseif ($step == 4) {//�������� ������ �������
            if (!CModule::IncludeModule("sale")) {
                $arResult['errCode'] = 'ERR_SALE';
            }

            if (isset($_POST['back']) && $_POST['back']) {
                $APPLICATION->IncludeAdminFile(
                    GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step2.php'
                );
            }
            //order upload
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') && isset($_POST['ajax']) && ($_POST['ajax'] == 1)) {
                $historyTime = Date('');
                RetailCrmOrder::uploadOrders(); // each 50

                $lastUpOrderId = COption::GetOptionString($this->MODULE_ID, $this->CRM_ORDER_LAST_ID, 0);
                $countLeft = (int) \Bitrix\Sale\Internals\OrderTable::getCount(array('>ID' => $lastUpOrderId));
                $countAll = (int) \Bitrix\Sale\Internals\OrderTable::getCount();

                if (!isset($_POST['finish'])) {
                    $finish = 0;
                } else {
                    $finish = (int) $_POST['finish'];
                }
                $percent = round(100 - ($countLeft * 100 / $countAll), 1);

                if (!$countLeft) {
                    $finish = 1;
                }
                $APPLICATION->RestartBuffer();
                header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
                die(json_encode(array("finish" => $finish, "percent" => $percent)));
            }
            
            if (isset($_POST['back']) && $_POST['back']) {
                $APPLICATION->IncludeAdminFile(
                        GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step2.php'
                );
            }
            
            //bitrix orderTypesList
            $orderTypesList = RCrmActions::OrderTypesList(RCrmActions::SitesList());
            
            $orderTypesArr = array();
            foreach ($orderTypesList as $orderType) {
                $orderTypesArr[$orderType['ID']] = htmlspecialchars(trim($_POST['order-type-' . $orderType['ID']]));
            }

            $orderPropsArr = array();
            foreach ($orderTypesList as $orderType) {
                $propsCount = 0;
                $_orderPropsArr = array();
                foreach ($arResult['orderProps'] as $orderProp) {
                    if ((!(int) htmlspecialchars(trim($_POST['address-detail-' . $orderType['ID']]))) && $propsCount > 4){
                        break;
                    }
                    $_orderPropsArr[$orderProp['ID']] = htmlspecialchars(trim($_POST['order-prop-' . $orderProp['ID'] . '-' . $orderType['ID']]));
                    $propsCount++;
                }
                $orderPropsArr[$orderType['ID']] = $_orderPropsArr;
            }
            
            //legal details props
            $legalDetailsArr = array();
            foreach ($orderTypesList as $orderType) {
                $_legalDetailsArr = array();
                foreach ($arResult['legalDetails'] as $legalDetails) {
                    $_legalDetailsArr[$legalDetails['ID']] = htmlspecialchars(trim($_POST['legal-detail-' . $legalDetails['ID'] . '-' . $orderType['ID']]));
                }
                $legalDetailsArr[$orderType['ID']] = $_legalDetailsArr;
            }

            $customFieldsArr = array();
            foreach ($orderTypesList as $orderType) {
                $_customFieldsArr = array();
                foreach ($arResult['customFields'] as $custom) {
                    $_customFieldsArr[$custom['ID']] = htmlspecialchars(trim($_POST['custom-fields-' . $custom['ID'] . '-' . $orderType['ID']]));
                }
                $customFieldsArr[$orderType['ID']] = $_customFieldsArr;
            }

            //contragents type list
            $contragentTypeArr = array();//������� ��������
            foreach ($orderTypesList as $orderType) {
                $contragentTypeArr[$orderType['ID']] = htmlspecialchars(trim($_POST['contragent-type-' . $orderType['ID']]));
            }

            COption::SetOptionString($this->MODULE_ID, $this->CRM_ORDER_PROPS, serialize(RCrmActions::clearArr($orderPropsArr)));
            COption::SetOptionString($this->MODULE_ID, $this->CRM_CUSTOM_FIELDS, serialize(RCrmActions::clearArr($customFieldsArr)));
            COption::SetOptionString($this->MODULE_ID, $this->CRM_LEGAL_DETAILS, serialize(RCrmActions::clearArr($legalDetailsArr)));
            COption::SetOptionString($this->MODULE_ID, $this->CRM_CONTRAGENT_TYPE, serialize(RCrmActions::clearArr($contragentTypeArr)));
   
            $APPLICATION->IncludeAdminFile(
                    GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step4.php'
            );

        } elseif ($step == 5) {//������� ��������
            if (!CModule::IncludeModule("iblock")) {
                $arResult['errCode'] = 'ERR_IBLOCK';
            }
            if (!CModule::IncludeModule("catalog")) {
                $arResult['errCode'] = 'ERR_CATALOG';
            }
            
            $api_host = COption::GetOptionString($this->MODULE_ID, $this->CRM_API_HOST_OPTION, 0);
            $api_key = COption::GetOptionString($this->MODULE_ID, $this->CRM_API_KEY_OPTION, 0);
            $api = new \RetailCrm\ApiClient($api_host, $api_key);
            
            $customerH = self::historyLoad($api, 'customersHistory');
            COption::SetOptionString($this->MODULE_ID, $this->CRM_CUSTOMER_HISTORY, $customerH);
            //���� ���� ������ ��������, ������������ ��� �� ����� ���, ����� ���������
            if ($historyDate = COption::GetOptionString($this->OLD_MODULE_ID, 'order_history_date', 0)) {
                try {
                    $history = $api->ordersHistory(array('startDate' => $historyDate));
                } catch (\RetailCrm\Exception\CurlException $e) {
                    RCrmActions::eventLog(
                        'intaro.retailcrm/install/index.php', 'RetailCrm\RestApi::ordersHistory::CurlException',
                        $e->getCode() . ': ' . $e->getMessage()
                    );

                } catch (InvalidArgumentException $e) {
                    RCrmActions::eventLog(
                        'intaro.retailcrm/install/index.php', 'RetailCrm\RestApi::ordersHistory::InvalidArgumentException',
                        $e->getCode() . ': ' . $e->getMessage()
                    );

                }
                if (isset($history['history'])) {
                    $hIs = (int)$history['history'][0]['id'] - 1;
                    $orderH = $hIs;
                } else {
                    $orderH = self::historyLoad($api, 'ordersHistory');
                }
            } else {
                $orderH = self::historyLoad($api, 'ordersHistory');
            }
            
            COption::SetOptionString($this->MODULE_ID, $this->CRM_ORDER_HISTORY, $orderH);
            
            if ($orderLastId = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_ORDER_LAST_ID, 0)) {
                COption::SetOptionString($this->MODULE_ID, $this->CRM_ORDER_LAST_ID, $orderLastId);
            }
            if ($orderFailedIds = COption::GetOptionString($this->OLD_MODULE_ID, $this->CRM_ORDER_FAILED_IDS, 0)) {
                COption::SetOptionString($this->MODULE_ID, $this->CRM_ORDER_FAILED_IDS, $orderFailedIds);
            }

            $arResult['PRICE_TYPES'] = array();

            $dbPriceType = CCatalogGroup::GetList(
                array("SORT" => "ASC"), array(), array(), array(), array("ID", "NAME", "BASE")
            );

            while ($arPriceType = $dbPriceType->Fetch()) {
                $arResult['PRICE_TYPES'][$arPriceType['ID']] = $arPriceType;
            }

            $APPLICATION->IncludeAdminFile(
                    GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step5.php'
            );
        } elseif ($step == 6) {//����������� ������
            if (!CModule::IncludeModule("iblock")) {
                $arResult['errCode'] = 'ERR_IBLOCK';
            }
            if (!CModule::IncludeModule("catalog")) {
                $arResult['errCode'] = 'ERR_CATALOG';
            }

            if (isset($arResult['errCode']) && $arResult['errCode']) {
                $APPLICATION->IncludeAdminFile(
                        GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step5.php'
                );
                
                return;
            }

            if (isset($_POST['back']) && $_POST['back']) {
                $APPLICATION->IncludeAdminFile(
                        GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step3.php'
                );
            }

            if (!isset($_POST['IBLOCK_EXPORT'])) {
                $arResult['errCode'] = 'ERR_FIELDS_IBLOCK';
            } else {
                $iblocks = $_POST['IBLOCK_EXPORT'];
            }
            
            $iblockProperties = array(
                    "article"      => "article",
                    "manufacturer" => "manufacturer",
                    "color"        => "color",
                    "weight"       => "weight",
                    "size"         => "size",
                    "length"       => "length",
                    "width"        => "width",
                    "height"       => "height",
                );
            
            $propertiesSKU = array();
            $propertiesUnitSKU = array();
            foreach ($iblockProperties as $prop) {
                foreach ($_POST['IBLOCK_PROPERTY_SKU'. '_' . $prop] as $iblock => $val) {
                    $propertiesSKU[$iblock][$prop] = $val;
                }
                foreach ($_POST['IBLOCK_PROPERTY_UNIT_SKU'. '_' . $prop] as $iblock => $val) {
                    $propertiesUnitSKU[$iblock][$prop] = $val;
                }
            }
            
            $propertiesProduct = array();
            $propertiesUnitProduct = array();
            foreach ($iblockProperties as $prop) {
                foreach ($_POST['IBLOCK_PROPERTY_PRODUCT'. '_' . $prop] as $iblock => $val) {
                    $propertiesProduct[$iblock][$prop] = $val;
                }
                foreach ($_POST['IBLOCK_PROPERTY_UNIT_PRODUCT'. '_' . $prop] as $iblock => $val) {
                    $propertiesUnitProduct[$iblock][$prop] = $val;
                }
            }

            if (!isset($_POST['SETUP_FILE_NAME'])) {
                $arResult['errCode'] = 'ERR_FIELDS_FILE';
            } else {
                $filename = $_POST['SETUP_FILE_NAME'];
            }
            
            if (!isset($_POST['TYPE_LOADING'])) {
                $typeLoading = 0;
            } else {
                $typeLoading = $_POST['TYPE_LOADING'];
            }

            if (!isset($_POST['SETUP_PROFILE_NAME'])) {
                $profileName = "";
            } else {
                $profileName = $_POST['SETUP_PROFILE_NAME'];
            }

            if ($typeLoading != 'none' && $profileName == "") {
                $arResult['errCode'] = 'ERR_FIELDS_PROFILE';
            }
            
            if ($filename == "") {
                $arResult['errCode'] = 'ERR_FIELDS_FILE';
            }
            
            if (isset($arResult['errCode']) && $arResult['errCode']) {
                $arOldValues = array(
                    'IBLOCK_EXPORT' => $iblocks,
                    'IBLOCK_PROPERTY_SKU' => $propertiesSKU,
                    'IBLOCK_PROPERTY_UNIT_SKU' => $propertiesUnitSKU,
                    'IBLOCK_PROPERTY_PRODUCT' => $propertiesProduct,
                    'IBLOCK_PROPERTY_UNIT_PRODUCT' => $propertiesUnitProduct,
                    'SETUP_FILE_NAME' => $filename,
                    'SETUP_PROFILE_NAME' => $profileName
                );
                global $oldValues;
                $oldValues = $arOldValues;
                $APPLICATION->IncludeAdminFile(
                    GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step5.php'
                );
                
                return;
            }

            RegisterModule($this->MODULE_ID);
            RegisterModuleDependences("sale", "OnOrderUpdate", $this->MODULE_ID, "RetailCrmEvent", "onUpdateOrder");
            RegisterModuleDependences("sale", "OnBeforeOrderAdd", $this->MODULE_ID, "RetailCrmEvent", "onBeforeOrderAdd");
            RegisterModuleDependences("sale", "OnOrderSave", $this->MODULE_ID, "RetailCrmEvent", "OnOrderSave");
            RegisterModuleDependences("main", "OnAfterUserUpdate", $this->MODULE_ID, "RetailCrmEvent", "OnAfterUserUpdate");
            RegisterModuleDependences("sale", "OnSaleOrderEntitySaved", $this->MODULE_ID, "RetailCrmEvent", "orderSave");
            RegisterModuleDependences("sale", "OnSaleOrderEntityDelete", $this->MODULE_ID, "RetailCrmEvent", "orderDelete");
            
            COption::SetOptionString($this->MODULE_ID, $this->CRM_CATALOG_BASE_PRICE, htmlspecialchars(trim($_POST['price-types'])));
            COption::SetOptionString($this->MODULE_ID, $this->CRM_CATALOG_IBLOCKS, $iblocks);

            $this->CopyFiles();
            if (isset($_POST['LOAD_NOW'])) {                
                $loader = new RetailCrmICML();
                $loader->iblocks = $iblocks;
                $loader->propertiesUnitProduct = $propertiesUnitProduct;
                $loader->propertiesProduct = $propertiesProduct;
                $loader->propertiesUnitSKU = $propertiesUnitSKU;
                $loader->propertiesSKU = $propertiesSKU;
                $loader->filename = $filename;
                $loader->serverName = \Bitrix\Main\Context::getCurrent()->getServer()->getHttpHost();
                $loader->application = $APPLICATION;
                $loader->Load();     
            } 
            
            if ($typeLoading == 'agent' || $typeLoading == 'cron') {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export/' . $this->RETAIL_CRM_EXPORT . '_run.php')) {
                    $dbProfile = CCatalogExport::GetList(array(), array("FILE_NAME" => $this->RETAIL_CRM_EXPORT));

                    while ($arProfile = $dbProfile->Fetch()) {
                        if ($arProfile["DEFAULT_PROFILE"] != "Y") {
                            CAgent::RemoveAgent("CCatalogExport::PreGenerateExport(" . $arProfile['ID'] . ");", "catalog");
                            CCatalogExport::Delete($arProfile['ID']);
                        }
                    }
                }
                $ar = $this->GetProfileSetupVars($iblocks, $propertiesProduct, $propertiesUnitProduct, $propertiesSKU, $propertiesUnitSKU, $filename);
                $PROFILE_ID = CCatalogExport::Add(array(
                    "LAST_USE"        => false,
                    "FILE_NAME"       => $this->RETAIL_CRM_EXPORT,
                    "NAME"            => $profileName,
                    "DEFAULT_PROFILE" => "N",
                    "IN_MENU"         => "N",
                    "IN_AGENT"        => "N",
                    "IN_CRON"         => "N",
                    "NEED_EDIT"       => "N",
                    "SETUP_VARS"      => $ar
                    ));
                if (intval($PROFILE_ID) <= 0) {
                    $arResult['errCode'] = 'ERR_IBLOCK';
                    
                    return;
                }
                if ($typeLoading == 'agent') {
                    $dateAgent = new DateTime();
                    $intAgent = new DateInterval('PT60S'); // PT60S - 60 sec;
                    $dateAgent->add($intAgent);
                    CAgent::AddAgent(
                            "CCatalogExport::PreGenerateExport(" . $PROFILE_ID . ");", "catalog", "N", 86400, $dateAgent->format('d.m.Y H:i:s'), // date of first check
                            "Y", // agent is active
                            $dateAgent->format('d.m.Y H:i:s'), // date of first start
                            30
                    );

                    CCatalogExport::Update($PROFILE_ID, array(
                        "IN_AGENT" => "Y"
                    ));
                } else {
                    $agent_period = 24;
                    $agent_php_path = "/usr/local/php/bin/php";

                    if (!file_exists($_SERVER["DOCUMENT_ROOT"] . CATALOG_PATH2EXPORTS . "cron_frame.php")) {
                        CheckDirPath($_SERVER["DOCUMENT_ROOT"] . CATALOG_PATH2EXPORTS);
                        $tmp_file_size = filesize($_SERVER["DOCUMENT_ROOT"] . CATALOG_PATH2EXPORTS_DEF . "cron_frame.php");
                        $fp = fopen($_SERVER["DOCUMENT_ROOT"] . CATALOG_PATH2EXPORTS_DEF . "cron_frame.php", "rb");
                        $tmp_data = fread($fp, $tmp_file_size);
                        fclose($fp);

                        $tmp_data = str_replace("#DOCUMENT_ROOT#", $_SERVER["DOCUMENT_ROOT"], $tmp_data);
                        $tmp_data = str_replace("#PHP_PATH#", $agent_php_path, $tmp_data);

                        $fp = fopen($_SERVER["DOCUMENT_ROOT"] . CATALOG_PATH2EXPORTS . "cron_frame.php", "ab");
                        fwrite($fp, $tmp_data);
                        fclose($fp);
                    }

                    $cfg_data = "";
                    if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/crontab/crontab.cfg")) {
                        $cfg_file_size = filesize($_SERVER["DOCUMENT_ROOT"] . "/bitrix/crontab/crontab.cfg");
                        $fp = fopen($_SERVER["DOCUMENT_ROOT"] . "/bitrix/crontab/crontab.cfg", "rb");
                        $cfg_data = fread($fp, $cfg_file_size);
                        fclose($fp);
                    }

                    CheckDirPath($_SERVER["DOCUMENT_ROOT"] . CATALOG_PATH2EXPORTS . "logs/");

                    if ($arProfile["IN_CRON"] == "Y") {
                        // remove
                        $cfg_data = preg_replace("#^.*?" . preg_quote(CATALOG_PATH2EXPORTS) . "cron_frame.php +" . $PROFILE_ID . " *>.*?$#im", "", $cfg_data);
                    } else {
                        $strTime = "0 */" . $agent_period . " * * * ";
                        if (strlen($cfg_data) > 0)
                            $cfg_data .= "\n";

                        $cfg_data .= $strTime . $agent_php_path . " -f " . $_SERVER["DOCUMENT_ROOT"] . CATALOG_PATH2EXPORTS . "cron_frame.php " . $PROFILE_ID . " >" . $_SERVER["DOCUMENT_ROOT"] . CATALOG_PATH2EXPORTS . "logs/" . $PROFILE_ID . ".txt\n";
                    }

                    CCatalogExport::Update($PROFILE_ID, array(
                        "IN_CRON" => "Y"
                    ));

                    CheckDirPath($_SERVER["DOCUMENT_ROOT"] . "/bitrix/crontab/");
                    $cfg_data = preg_replace("#[\r\n]{2,}#im", "\n", $cfg_data);
                    $fp = fopen($_SERVER["DOCUMENT_ROOT"] . "/bitrix/crontab/crontab.cfg", "wb");
                    fwrite($fp, $cfg_data);
                    fclose($fp);

                    $arRetval = array();
                    @exec("crontab " . $_SERVER["DOCUMENT_ROOT"] . "/bitrix/crontab/crontab.cfg", $arRetval, $return_var);
                }
            }

            //agent
            $dateAgent = new DateTime();
            $intAgent = new DateInterval('PT60S'); // PT60S - 60 sec;
            $dateAgent->add($intAgent);

            CAgent::AddAgent(
                    "RCrmActions::orderAgent();", $this->MODULE_ID, "N", 600, // interval - 10 mins
                    $dateAgent->format('d.m.Y H:i:s'), // date of first check
                    "Y", // agent is active
                    $dateAgent->format('d.m.Y H:i:s'), // date of first start
                    30
            );

            $api_host = COption::GetOptionString($this->MODULE_ID, $this->CRM_API_HOST_OPTION, 0);
            $api_key = COption::GetOptionString($this->MODULE_ID, $this->CRM_API_KEY_OPTION, 0);
            $this->RETAIL_CRM_API = new \RetailCrm\ApiClient($api_host, $api_key);
            try {
                $this->RETAIL_CRM_API->statisticUpdate();
            } catch (\RetailCrm\Exception\CurlException $e) {
                RCrmActions::eventLog(
                    'intaro.retailcrm/install/index.php', 'RetailCrm\ApiClient::statisticUpdate::CurlException',
                    $e->getCode() . ': ' . $e->getMessage()
                );
            }

            $APPLICATION->IncludeAdminFile(
                    GetMessage('MODULE_INSTALL_TITLE'), $this->INSTALL_PATH . '/step6.php'
            );
        }
    }

    function DoUninstall() 
    {
        global $APPLICATION;

        CAgent::RemoveAgent("RCrmActions::orderAgent();", $this->MODULE_ID);

        COption::RemoveOption($this->MODULE_ID, $this->CRM_API_HOST_OPTION);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_API_KEY_OPTION);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_DELIVERY_TYPES_ARR);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_PAYMENT_TYPES);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_PAYMENT_STATUSES);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_PAYMENT);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_ORDER_LAST_ID);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_ORDER_SITES);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_ORDER_PROPS);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_LEGAL_DETAILS);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_CONTRAGENT_TYPE);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_CUSTOM_FIELDS);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_SITES_LIST);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_ORDER_DISCHARGE);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_ORDER_FAILED_IDS);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_CUSTOMER_HISTORY);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_ORDER_HISTORY);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_CATALOG_BASE_PRICE);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_CATALOG_IBLOCKS);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_ORDER_NUMBERS);
        COption::RemoveOption($this->MODULE_ID, $this->CRM_CANSEL_ORDER);

        UnRegisterModuleDependences("sale", "OnOrderUpdate", $this->MODULE_ID, "RetailCrmEvent", "onUpdateOrder");
        UnRegisterModuleDependences("sale", "OnBeforeOrderAdd", $this->MODULE_ID, "RetailCrmEvent", "onBeforeOrderAdd");
        UnRegisterModuleDependences("sale", "OnOrderSave", $this->MODULE_ID, "RetailCrmEvent", "OnOrderSave");
        UnRegisterModuleDependences("main", "OnAfterUserUpdate", $this->MODULE_ID, "RetailCrmEvent", "OnAfterUserUpdate");
        UnRegisterModuleDependences("sale", "OnSaleOrderEntitySaved", $this->MODULE_ID, "RetailCrmEvent", "orderSave");
        UnRegisterModuleDependences("sale", "OnSaleOrderEntityDelete", $this->MODULE_ID, "RetailCrmEvent", "orderDelete");
        
        if (CModule::IncludeModule("catalog")) {
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export/' . $this->RETAIL_CRM_EXPORT . '_run.php')) {
                $dbProfile = CCatalogExport::GetList(array(), array("FILE_NAME" => $this->RETAIL_CRM_EXPORT));

                while ($arProfile = $dbProfile->Fetch()) {
                    if ($arProfile["DEFAULT_PROFILE"] != "Y") {
                        CAgent::RemoveAgent("CCatalogExport::PreGenerateExport(" . $arProfile['ID'] . ");", "catalog");
                        CCatalogExport::Delete($arProfile['ID']);
                    }
                }
            }
        }

        $this->DeleteFiles();

        UnRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
                GetMessage('MODULE_UNINSTALL_TITLE'), $this->INSTALL_PATH . '/unstep1.php'
        );
    }    

    function CopyFiles()
    {
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/export/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export/', true, true
        );
    }

    function DeleteFiles()
    {
        $rsSites = CSite::GetList($by, $sort, array('DEF' => 'Y'));
        $defaultSite = array();
        while ($ar = $rsSites->Fetch()) {
            $defaultSite = $ar;
            break;
        }

        unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export/retailcrm_run.php');
        unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/catalog_export/retailcrm_setup.php');
        unlink($defaultSite['ABS_DOC_ROOT'] . '/retailcrm/agent.php');
        rmdir($defaultSite['ABS_DOC_ROOT'] . '/retailcrm/');
    }

    function GetProfileSetupVars($iblocks, $propertiesProduct, $propertiesUnitProduct, $propertiesSKU, $propertiesUnitSKU, $filename) {
        $strVars = "";
        foreach ($iblocks as $key => $val) 
            $strVars .= 'IBLOCK_EXPORT[' . $key . ']=' . $val . '&';
        foreach ($propertiesSKU as $iblock => $arr) 
            foreach ($arr as $id => $val)
                $strVars .= 'IBLOCK_PROPERTY_SKU_' . $id . '[' . $iblock . ']=' . $val . '&';
        foreach ($propertiesUnitSKU as $iblock => $arr) 
            foreach ($arr as $id => $val)
                $strVars .= 'IBLOCK_PROPERTY_UNIT_SKU_' . $id . '[' . $iblock . ']=' . $val . '&';
        foreach ($propertiesProduct as $iblock => $arr) 
            foreach ($arr as $id => $val)
                $strVars .= 'IBLOCK_PROPERTY_PRODUCT_' . $id . '[' . $iblock . ']=' . $val . '&';
        foreach ($propertiesUnitProduct as $iblock => $arr) 
            foreach ($arr as $id => $val)
                $strVars .= 'IBLOCK_PROPERTY_UNIT_PRODUCT_' . $id . '[' . $iblock . ']=' . $val . '&';
        
        $strVars .= 'SETUP_FILE_NAME=' . urlencode($filename);
        
        return $strVars;
    }
    
    function historyLoad($api, $method)
    {
        $page = null;
        $i = 0;
        while (true) {
            $i++;
            try {
                $history = $api->$method(array(), $page);
            } catch (\RetailCrm\Exception\CurlException $e) {
                RCrmActions::eventLog(
                    'RetailCrmHistory::' . $method, 'RetailCrm\RestApi::' . $method . '::CurlException',
                    $e->getCode() . ': ' . $e->getMessage()
                );
                $end['id'] = 0;

                return $end['id'];
            } catch (InvalidArgumentException $e) {
                RCrmActions::eventLog(
                    'RetailCrmHistory::' . $method, 'RetailCrm\RestApi::' . $method . '::InvalidArgumentException',
                    $e->getCode() . ': ' . $e->getMessage()
                );
                $end['id'] = 0;

                return $end['id'];
            }

            if (isset($history['history'])) {
                $end = array_pop($history['history']);
            } else {
                $end['id'] = 0;
                break;
            }   

            if ($history['pagination']['totalPageCount'] == $history['pagination']['currentPage']) {        
                break;
            }

            $page = $history['pagination']['totalPageCount'];
        }

        return $end['id'];
    }
}
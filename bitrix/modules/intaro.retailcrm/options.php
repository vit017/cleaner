<?php 
IncludeModuleLangFile(__FILE__);
$mid = 'intaro.retailcrm';
$uri = $APPLICATION->GetCurPage() . '?mid=' . htmlspecialchars($mid) . '&lang=' . LANGUAGE_ID;

$CRM_API_HOST_OPTION = 'api_host';
$CRM_API_KEY_OPTION = 'api_key';
$CRM_ORDER_TYPES_ARR = 'order_types_arr';
$CRM_DELIVERY_TYPES_ARR = 'deliv_types_arr';
$CRM_DELIVERY_SERVICES_ARR = 'deliv_services_arr';
$CRM_PAYMENT_TYPES = 'pay_types_arr';
$CRM_PAYMENT_STATUSES = 'pay_statuses_arr';
$CRM_PAYMENT = 'payment_arr'; //order payment Y/N
$CRM_ORDER_LAST_ID = 'order_last_id';
$CRM_ORDER_SITES = 'sites_ids';
$CRM_ORDER_DISCHARGE = 'order_discharge';
$CRM_ORDER_PROPS = 'order_props';
$CRM_LEGAL_DETAILS = 'legal_details';
$CRM_CUSTOM_FIELDS = 'custom_fields';
$CRM_CONTRAGENT_TYPE = 'contragent_type';
$CRM_SITES_LIST= 'sites_list';
$CRM_ORDER_NUMBERS = 'order_numbers';
$CRM_CANSEL_ORDER = 'cansel_order';

if(!CModule::IncludeModule('intaro.retailcrm') || !CModule::IncludeModule('sale'))
    return;

$_GET['errc'] = htmlspecialchars(trim($_GET['errc']));
$_GET['ok'] = htmlspecialchars(trim($_GET['ok']));

if($_GET['errc']) echo CAdminMessage::ShowMessage(GetMessage($_GET['errc']));
if($_GET['ok'] && $_GET['ok'] == 'Y') echo CAdminMessage::ShowNote(GetMessage('ICRM_OPTIONS_OK'));

$arResult = array();

if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/intaro.retailcrm/classes/general/config/options.xml')) {
    $options = simplexml_load_file($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/intaro.retailcrm/classes/general/config/options.xml'); 
    
    foreach($options->contragents->contragent as $contragent) {
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

$arResult['arSites'] = RCrmActions::SitesList();
//ajax update deliveryServices
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') && isset($_POST['ajax']) && ($_POST['ajax'] == 1)) {
    $api_host = COption::GetOptionString($mid, $CRM_API_HOST_OPTION, 0);
    $api_key = COption::GetOptionString($mid, $CRM_API_KEY_OPTION, 0);
    $api = new RetailCrm\ApiClient($api_host, $api_key);

    try {
        $api->paymentStatusesList();
    } catch (\RetailCrm\Exception\CurlException $e) {
        RCrmActions::eventLog(
            'intaro.retailcrm/options.php', 'RetailCrm\ApiClient::paymentStatusesList::CurlException',
            $e->getCode() . ': ' . $e->getMessage()
        );

        $APPLICATION->RestartBuffer();
        header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
        die(json_encode(array('success' => false, 'errMsg' => $e->getCode())));
    }

    $optionsDelivTypes = unserialize(COption::GetOptionString($mid, $CRM_DELIVERY_TYPES_ARR, 0));
    $arDeliveryServiceAll = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();

    foreach ($optionsDelivTypes as $key => $deliveryType) {
        foreach ($arDeliveryServiceAll as $deliveryService) {
            if ($deliveryService['PARENT_ID'] != 0 && $deliveryService['PARENT_ID'] == $key) {
                $srv = explode(':', $deliveryService['CODE']);
                if (count($srv) == 2) {
                    try {
                        $api->deliveryServicesEdit(RCrmActions::clearArr(array(
                            'code' => $srv[1],
                            'name' => RCrmActions::toJSON($deliveryService['NAME']),
                            'deliveryType' => $deliveryType
                        )));
                    } catch (\RetailCrm\Exception\CurlException $e) {
                        RCrmActions::eventLog(
                            'intaro.retailcrm/options.php', 'RetailCrm\ApiClient::deliveryServiceEdit::CurlException',
                            $e->getCode() . ': ' . $e->getMessage()
                        );
                    }
                }
            }
        }
    }

    $APPLICATION->RestartBuffer();
    header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
    die(json_encode(array('success' => true)));
}

//upload orders after install module
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') && isset($_POST['ajax']) && $_POST['ajax'] == 2) {
    $step = $_POST['step'];
    $orders = $_POST['orders'];
    $countStep = 50; // 50 orders on step
    
    if ($orders) {
        $ordersArr = explode(',', $orders);
        $orders = array();
        foreach ($ordersArr as $_ordersArr) {
            $ordersList = explode('-', trim($_ordersArr));
            if (count($ordersList) > 1) {
                for ($i = (int)trim($ordersList[0]); $i <= (int)trim($ordersList[count($ordersList) - 1]); $i++) {
                    $orders[] = $i;
                }
            } else{
                $orders[] = (int)$ordersList[0];
            }
        }
        
        $splitedOrders = array_chunk($orders, $countStep);
        $stepOrders = $splitedOrders[$step];

        RetailCrmOrder::uploadOrders($countStep, false, $stepOrders);
        
        $percent = round((($step * $countStep + count($stepOrders)) * 100 / count($orders)), 1);
        $step++;

        if (!$splitedOrders[$step]) {
            $step = 'end';
        }
        
        $res = array("step" => $step, "percent" => $percent, 'stepOrders' => $stepOrders);
    } else {
        $orders = array();    
        for($i = 1; $i <= $countStep; $i++){
            $orders[] = $i + $step * $countStep;
        }
        
        RetailCrmOrder::uploadOrders($countStep, false, $orders);
        
        $step++;
        $countLeft = (int) CSaleOrder::GetList(array("ID" => "ASC"), array('>ID' => $step * $countStep), array());
        $countAll = (int) CSaleOrder::GetList(array("ID" => "ASC"), array(), array());
        $percent = round(100 - ($countLeft * 100 / $countAll), 1);
        
        if ($countLeft == 0) {
            $step = 'end';
        }
        
        $res = array("step" => $step, "percent" => $percent, 'stepOrders' => $orders);
    }

    $APPLICATION->RestartBuffer();
    header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
    die(json_encode($res));
}

//update connection settings
if (isset($_POST['Update']) && ($_POST['Update'] == 'Y')) {
    $api_host = htmlspecialchars(trim($_POST['api_host']));
    $api_key = htmlspecialchars(trim($_POST['api_key']));

    //bitrix site list
    $siteListArr = array(); 
    foreach ($arResult['arSites'] as $arSites) {
        $siteListArr[$arSites['LID']] = htmlspecialchars(trim($_POST['sites-id-' . $arSites['LID']]));
    }
            
    if ($api_host && $api_key) {
        $api = new RetailCrm\ApiClient($api_host, $api_key);
        try {
            $api->paymentStatusesList();
        } catch (\RetailCrm\Exception\CurlException $e) {
            RCrmActions::eventLog(
                'intaro.retailcrm/options.php', 'RetailCrm\ApiClient::paymentStatusesList::CurlException',
                $e->getCode() . ': ' . $e->getMessage()
            );

            $uri .= '&errc=ERR_' . $e->getCode();
            LocalRedirect($uri);
        }

        COption::SetOptionString($mid, 'api_host', $api_host);
        COption::SetOptionString($mid, 'api_key', $api_key);
    }
       
    //form order types ids arr
    $orderTypesList = RCrmActions::OrderTypesList($arResult['arSites']);
    
    $orderTypesArr = array();
    foreach ($orderTypesList as $orderType) {
        $orderTypesArr[$orderType['ID']] = htmlspecialchars(trim($_POST['order-type-' . $orderType['ID']]));
    }
      
    //form delivery types ids arr
    $arResult['bitrixDeliveryTypesList'] = RCrmActions::DeliveryList();

    $deliveryTypesArr = array();
    foreach ($arResult['bitrixDeliveryTypesList'] as $delivery) {
        $deliveryTypesArr[$delivery['ID']] = htmlspecialchars(trim($_POST['delivery-type-' . $delivery['ID']]));
    }

    //form payment types ids arr
    $arResult['bitrixPaymentTypesList'] = RCrmActions::PaymentList();
            
    $paymentTypesArr = array();
    foreach ($arResult['bitrixPaymentTypesList'] as $payment) {
        $paymentTypesArr[$payment['ID']] = htmlspecialchars(trim($_POST['payment-type-' . $payment['ID']]));
    }  
           
    //form payment statuses ids arr
    $arResult['bitrixStatusesList'] = RCrmActions::StatusesList();
            
    $paymentStatusesArr = array();
    $canselOrderArr = array();
    //$paymentStatusesArr['YY'] = htmlspecialchars(trim($_POST['payment-status-YY']));
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
    

    $previousDischarge = COption::GetOptionString($mid, $CRM_ORDER_DISCHARGE, 0);
    //order discharge mode
    // 0 - agent
    // 1 - event
    $orderDischarge = 0;
    $orderDischarge = (int) htmlspecialchars(trim($_POST['order-discharge']));   
    if (($orderDischarge != $previousDischarge) && ($orderDischarge == 0)) {
        // remove depenedencies
        UnRegisterModuleDependences("sale", "OnSaleOrderEntitySaved", $mid, "RetailCrmEvent", "orderSave");
        UnRegisterModuleDependences("sale", "OnOrderUpdate", $mid, "RetailCrmEvent", "onUpdateOrder");
        UnRegisterModuleDependences("sale", "OnSaleOrderEntityDelete", $mid, "RetailCrmEvent", "orderDelete");
        
    } elseif (($orderDischarge != $previousDischarge) && ($orderDischarge == 1)) {
        // event dependencies
        RegisterModuleDependences("sale", "OnSaleOrderEntitySaved", $mid, "RetailCrmEvent", "orderSave");
        RegisterModuleDependences("sale", "OnOrderUpdate", $mid, "RetailCrmEvent", "onUpdateOrder");
        RegisterModuleDependences("sale", "OnSaleOrderEntityDelete", $mid, "RetailCrmEvent", "orderDelete");
    }

    $orderPropsArr = array();
    foreach ($orderTypesList as $orderType) {
        $propsCount = 0;
        $_orderPropsArr = array();
        foreach ($arResult['orderProps'] as $orderProp) {
            if ((!(int) htmlspecialchars(trim($_POST['address-detail-' . $orderType['ID']]))) && $propsCount > 4) {
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
    $contragentTypeArr = array();
    foreach ($orderTypesList as $orderType) {
        $contragentTypeArr[$orderType['ID']] = htmlspecialchars(trim($_POST['contragent-type-' . $orderType['ID']]));
    }
    //order numbers
    $orderNumbers = htmlspecialchars(trim($_POST['order-numbers'])) ? htmlspecialchars(trim($_POST['order-numbers'])) : 'N';
        
    COption::SetOptionString($mid, $CRM_SITES_LIST, serialize(RCrmActions::clearArr($siteListArr)));
    COption::SetOptionString($mid, $CRM_ORDER_TYPES_ARR, serialize(RCrmActions::clearArr($orderTypesArr)));
    COption::SetOptionString($mid, $CRM_DELIVERY_TYPES_ARR, serialize(RCrmActions::clearArr($deliveryTypesArr)));
    COption::SetOptionString($mid, $CRM_PAYMENT_TYPES, serialize(RCrmActions::clearArr($paymentTypesArr)));
    COption::SetOptionString($mid, $CRM_PAYMENT_STATUSES, serialize(RCrmActions::clearArr($paymentStatusesArr)));
    COption::SetOptionString($mid, $CRM_PAYMENT, serialize(RCrmActions::clearArr($paymentArr)));
    COption::SetOptionString($mid, $CRM_ORDER_DISCHARGE, $orderDischarge);
    COption::SetOptionString($mid, $CRM_ORDER_PROPS, serialize(RCrmActions::clearArr($orderPropsArr)));    
    COption::SetOptionString($mid, $CRM_CONTRAGENT_TYPE, serialize(RCrmActions::clearArr($contragentTypeArr)));    
    COption::SetOptionString($mid, $CRM_LEGAL_DETAILS, serialize(RCrmActions::clearArr($legalDetailsArr)));
    COption::SetOptionString($mid, $CRM_CUSTOM_FIELDS, serialize(RCrmActions::clearArr($customFieldsArr)));
    COption::SetOptionString($mid, $CRM_ORDER_NUMBERS, $orderNumbers);
    COption::SetOptionString($mid, $CRM_CANSEL_ORDER, serialize(RCrmActions::clearArr($canselOrderArr)));

    $uri .= '&ok=Y';
    LocalRedirect($uri);
} else {
    $api_host = COption::GetOptionString($mid, $CRM_API_HOST_OPTION, 0);
    $api_key = COption::GetOptionString($mid, $CRM_API_KEY_OPTION, 0);
    $api = new RetailCrm\ApiClient($api_host, $api_key);

    //prepare crm lists
    try {
        $arResult['orderTypesList'] = $api->orderTypesList()->orderTypes;
        $arResult['deliveryTypesList'] = $api->deliveryTypesList()->deliveryTypes;
        $arResult['deliveryServicesList'] = $api->deliveryServicesList()->deliveryServices;
        $arResult['paymentTypesList'] = $api->paymentTypesList()->paymentTypes;
        $arResult['paymentStatusesList'] = $api->paymentStatusesList()->paymentStatuses; // --statuses
        $arResult['paymentList'] = $api->statusesList()->statuses;
        $arResult['paymentGroupList'] = $api->statusGroupsList()->statusGroups; // -- statuses groups
        $arResult['sitesList'] = $APPLICATION->ConvertCharsetArray($api->sitesList()->sites, 'utf-8', SITE_CHARSET);
    } catch (\RetailCrm\Exception\CurlException $e) {
        RCrmActions::eventLog(
            'intaro.retailcrm/options.php', 'RetailCrm\ApiClient::*List::CurlException',
            $e->getCode() . ': ' . $e->getMessage()
        );

        echo CAdminMessage::ShowMessage(GetMessage('ERR_' . $e->getCode()));
    } catch (InvalidArgumentException $e) {
        $badKey = true;
        echo CAdminMessage::ShowMessage(GetMessage('ERR_403'));
    }
    $delivTypes = array();
    foreach ($arResult['deliveryTypesList'] as $delivType) {
        if ($delivType['active'] === true) {
            $delivTypes[$delivType['code']] = $delivType;
        }
    }
    $arResult['deliveryTypesList'] = $delivTypes;

    //bitrix orderTypesList -- personTypes
    $arResult['bitrixOrderTypesList'] = RCrmActions::OrderTypesList($arResult['arSites']);
    
    //bitrix deliveryTypesList
    $arResult['bitrixDeliveryTypesList'] = RCrmActions::DeliveryList();

    //bitrix paymentTypesList
    $arResult['bitrixPaymentTypesList'] = RCrmActions::PaymentList();
    
    //bitrix statusesList
    $arResult['bitrixPaymentStatusesList'] = RCrmActions::StatusesList();
    
    //bitrix pyament Y/N
    $arResult['bitrixPaymentList'][0]['NAME'] = GetMessage('PAYMENT_Y');
    $arResult['bitrixPaymentList'][0]['ID'] = 'Y';
    $arResult['bitrixPaymentList'][1]['NAME'] = GetMessage('PAYMENT_N');
    $arResult['bitrixPaymentList'][1]['ID'] = 'N';
    
    //bitrix orderPropsList
    $arResult['arProp'] = RCrmActions::OrderPropsList();
    
    //saved cat params
    $optionsOrderTypes = unserialize(COption::GetOptionString($mid, $CRM_ORDER_TYPES_ARR, 0));
    $optionsDelivTypes = unserialize(COption::GetOptionString($mid, $CRM_DELIVERY_TYPES_ARR, 0));
    $optionsPayTypes = unserialize(COption::GetOptionString($mid, $CRM_PAYMENT_TYPES, 0));
    $optionsPayStatuses = unserialize(COption::GetOptionString($mid, $CRM_PAYMENT_STATUSES, 0)); // --statuses
    $optionsPayment = unserialize(COption::GetOptionString($mid, $CRM_PAYMENT, 0));
    $optionsSitesList = unserialize(COption::GetOptionString($mid, $CRM_SITES_LIST, 0));
    $optionsDischarge = COption::GetOptionString($mid, $CRM_ORDER_DISCHARGE, 0);
    $optionsOrderProps = unserialize(COption::GetOptionString($mid, $CRM_ORDER_PROPS, 0));    
    $optionsContragentType = unserialize(COption::GetOptionString($mid, $CRM_CONTRAGENT_TYPE, 0));    
    $optionsLegalDetails = unserialize(COption::GetOptionString($mid, $CRM_LEGAL_DETAILS, 0));
    $optionsCustomFields = unserialize(COption::GetOptionString($mid, $CRM_CUSTOM_FIELDS, 0));
    $optionsOrderNumbers = COption::GetOptionString($mid, $CRM_ORDER_NUMBERS, 0);
    $canselOrderArr = unserialize(COption::GetOptionString($mid, $CRM_CANSEL_ORDER, 0));

    //$isCustomOrderType = function_exists('intarocrm_set_order_type') || function_exists('intarocrm_get_order_type');

    $aTabs = array(
        array(
            "DIV" => "edit1",
            "TAB" => GetMessage('ICRM_OPTIONS_GENERAL_TAB'),
            "ICON" => "",
            "TITLE" => GetMessage('ICRM_OPTIONS_GENERAL_CAPTION')
        ),
        array(
            "DIV" => "edit2",
            "TAB" => GetMessage('ICRM_OPTIONS_CATALOG_TAB'),
            "ICON" => '',
            "TITLE" => GetMessage('ICRM_OPTIONS_CATALOG_CAPTION')
        ),
        array(
            "DIV" => "edit3",
            "TAB" => GetMessage('ICRM_OPTIONS_ORDER_PROPS_TAB'),
            "ICON" => '',
            "TITLE" => GetMessage('ICRM_OPTIONS_ORDER_PROPS_CAPTION')
        ),
        array(
            "DIV" => "edit4",
            "TAB" => GetMessage('OTHER_OPTIONS'),
            "ICON" => '',
            "TITLE" => GetMessage('ICRM_OPTIONS_ORDER_DISCHARGE_CAPTION')
        )
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    $tabControl->Begin();
?>
<?php $APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/js/main/jquery/jquery-1.7.min.js"></script>'); ?>
<script type="text/javascript">
    $(document).ready(function() { 
        $('input.addr').change(function(){
            splitName = $(this).attr('name').split('-');
            orderType = splitName[2];
            
            if(parseInt($(this).val()) === 1)
                $('tr.address-detail-' + orderType).show('slow');
            else if(parseInt($(this).val()) === 0)
                $('tr.address-detail-' + orderType).hide('slow');
        });

        $('tr.contragent-type select').change(function(){
            splitName = $(this).attr('name').split('-');
            contragentType = $(this).val();
            orderType = splitName[2];
            
            $('tr.legal-detail-' + orderType).hide();
            $('.legal-detail-title-' + orderType).hide();

            $('tr.legal-detail-' + orderType).each(function(){
                if($(this).hasClass(contragentType)){
                    $(this).show();
                    $('.legal-detail-title-' + orderType).show();
                }
            });
        });
    });    

    $('input[name="update-delivery-services"]').live('click', function() {
        BX.showWait();
        var updButton = this;
        // hide next step button
        $(updButton).css('opacity', '0.5').attr('disabled', 'disabled');

        var handlerUrl = $(this).parents('form').attr('action');
        var data = 'ajax=1';

        $.ajax({
            type: 'POST',
            url: handlerUrl,
            data: data,
            dataType: 'json',
            success: function(response) {
                BX.closeWait();
                $(updButton).css('opacity', '1').removeAttr('disabled');

                if(!response.success)
                    alert('<?php echo GetMessage('MESS_1'); ?>');
            },
            error: function () {
                BX.closeWait();
                $(updButton).css('opacity', '1').removeAttr('disabled');

                alert('<?php echo GetMessage('MESS_2'); ?>');
            }
        });

        return false;
    });
</script>

<form method="POST" action="<?php echo $uri; ?>" id="FORMACTION">
<?php
    echo bitrix_sessid_post();
    $tabControl->BeginNextTab();
?>
    <input type="hidden" name="tab" value="catalog">
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('ICRM_CONN_SETTINGS'); ?></b></td>
    </tr>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><?php echo GetMessage('ICRM_API_HOST'); ?></td>
        <td width="50%" class="adm-detail-content-cell-r"><input type="text" id="api_host" name="api_host" value="<?php echo $api_host; ?>"></td>
    </tr>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><?php echo GetMessage('ICRM_API_KEY'); ?></td>
        <td width="50%" class="adm-detail-content-cell-r"><input type="text" id="api_key" name="api_key" value="<?php echo $api_key; ?>"></td>
    </tr>
    <?php if(count($arResult['arSites'])>1):?>
    <tr class="heading">
        <td colspan="2" style="background-color: transparent;">
            <b>
                <?php echo GetMessage('ICRM_SITES'); ?>
            </b>
        </td>
    </tr>   
    <?php foreach ($arResult['arSites'] as $site): ?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><?php echo $site['NAME'] . ' (' . $site['LID'] . ')'; ?></td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select class="typeselect" name="sites-id-<?php echo $site['LID']?>">
                <option value=""></option>
                <?php foreach ($arResult['sitesList'] as $sitesList): ?>
                    <option value="<?php echo $sitesList['code'] ?>" <?php if($sitesList['code'] == $optionsSitesList[$site['LID']]) echo 'selected="selected"'; ?>><?php echo $sitesList['name']?></option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif;?>
<?php if(!$badKey):?>
<?php $tabControl->BeginNextTab(); ?>
    <input type="hidden" name="tab" value="catalog">
    <tr align="center">
        <td colspan="2"><b><?php echo GetMessage('INFO_1'); ?></b></td>
    </tr>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('DELIVERY_TYPES_LIST'); ?></b></td>
    </tr>
    <?php foreach($arResult['bitrixDeliveryTypesList'] as $bitrixDeliveryType): ?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $bitrixDeliveryType['ID']; ?>">
            <?php echo $bitrixDeliveryType['NAME']; ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="delivery-type-<?php echo $bitrixDeliveryType['ID']; ?>" class="typeselect">
                <option value=""></option>
                <?php foreach($arResult['deliveryTypesList'] as $deliveryType): ?>
                <option value="<?php echo $deliveryType['code']; ?>" <?php if ($optionsDelivTypes[$bitrixDeliveryType['ID']] == $deliveryType['code']) echo 'selected'; ?>>
                    <?php echo $APPLICATION->ConvertCharset($deliveryType['name'], 'utf-8', SITE_CHARSET); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr class="heading">
        <td colspan="2">
            <input type="submit" name="update-delivery-services" value="<?php echo GetMessage('UPDATE_DELIVERY_SERVICES'); ?>" class="adm-btn-save">
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('PAYMENT_TYPES_LIST'); ?></b></td>
    </tr>
    <?php foreach($arResult['bitrixPaymentTypesList'] as $bitrixPaymentType): ?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $bitrixPaymentType['ID']; ?>">
            <?php echo $bitrixPaymentType['NAME']; ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="payment-type-<?php echo $bitrixPaymentType['ID']; ?>" class="typeselect">
                <option value="" selected=""></option>
                <?php foreach($arResult['paymentTypesList'] as $paymentType): ?>
                <option value="<?php echo $paymentType['code']; ?>" <?php if ($optionsPayTypes[$bitrixPaymentType['ID']] == $paymentType['code']) echo 'selected'; ?>>
                    <?php echo $APPLICATION->ConvertCharset($paymentType['name'], 'utf-8', SITE_CHARSET); ?>
                </option>
                <?php endforeach; ?>
             </select>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('PAYMENT_STATUS_LIST'); ?></b></td>
    </tr>
    <tr>
        <td width="50%"></td>
        <td width="50%">
            <table width="100%">
                <tr>
                    <td width="50%"></td>
                    <td width="50%"><?php echo GetMessage('CANCELED'); ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <?php foreach($arResult['bitrixPaymentStatusesList'] as $bitrixPaymentStatus): ?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $bitrixPaymentStatus['ID']; ?>">
            <?php echo $bitrixPaymentStatus['NAME']; ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <table width="100%">
                <tr>
                    <td width="70%">
                        <select name="payment-status-<?php echo $bitrixPaymentStatus['ID']; ?>" class="typeselect">
                            <option value=""></option>
                            <?php foreach($arResult['paymentGroupList'] as $orderStatusGroup): if(!empty($orderStatusGroup['statuses'])) : ?>
                            <optgroup label="<?php echo $APPLICATION->ConvertCharset($orderStatusGroup['name'], 'utf-8', SITE_CHARSET); ?>">
                                <?php foreach($orderStatusGroup['statuses'] as $payment): ?>
                                    <?php if(isset($arResult['paymentList'][$payment])): ?>
                                        <option value="<?php echo $arResult['paymentList'][$payment]['code']; ?>" <?php if ($optionsPayStatuses[$bitrixPaymentStatus['ID']] == $arResult['paymentList'][$payment]['code']) echo 'selected'; ?>>
                                            <?php echo $APPLICATION->ConvertCharset($arResult['paymentList'][$payment]['name'], 'utf-8', SITE_CHARSET); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; endforeach; ?>
                        </select>
                    </td>
                    <td width="30%">
                        <input name="order-cansel-<?php echo $bitrixPaymentStatus['ID']; ?>" <?php if(in_array($bitrixPaymentStatus['ID'], $canselOrderArr)) echo "checked";?> value="Y" type="checkbox" />
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('PAYMENT_LIST'); ?></b></td>
    </tr>
    <?php foreach($arResult['bitrixPaymentList'] as $bitrixPayment): ?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $bitrixPayment['ID']; ?>">
            <?php echo $bitrixPayment['NAME']; ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="payment-<?php echo $bitrixPayment['ID']; ?>" class="typeselect">
                <option value=""></option>
                <?php foreach($arResult['paymentStatusesList'] as $paymentStatus): ?>
                <option value="<?php echo $paymentStatus['code']; ?>" <?php if ($optionsPayment[$bitrixPayment['ID']] == $paymentStatus['code']) echo 'selected'; ?>>
                    <?php echo $APPLICATION->ConvertCharset($paymentStatus['name'], 'utf-8', SITE_CHARSET); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('ORDER_TYPES_LIST'); ?></b></td>
    </tr>
    <?php if($isCustomOrderType): ?>
    <tr>
        <td colspan="2" style="text-align: center!important; padding-bottom:10px;"><b style="color:#c24141;"><?php echo GetMessage('ORDER_TYPES_LIST_CUSTOM'); ?></b></td>
    </tr>
    <?php endif; ?>
    <?php foreach($arResult['bitrixOrderTypesList'] as $bitrixOrderType): ?>
    <tr>
       <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $bitrixOrderType['ID']; ?>">
           <?php echo $bitrixOrderType['NAME']; ?>
       </td>
       <td width="50%" class="adm-detail-content-cell-r">
           <select name="order-type-<?php echo $bitrixOrderType['ID']; ?>" class="typeselect">
               <option value=""></option>
               <?php foreach($arResult['orderTypesList'] as $orderType): ?>
               <option value="<?php echo $orderType['code']; ?>" <?php if ($optionsOrderTypes[$bitrixOrderType['ID']] == $orderType['code']) echo 'selected'; ?>>
                   <?php echo $APPLICATION->ConvertCharset($orderType['name'], 'utf-8', SITE_CHARSET); ?>
               </option>
               <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php endforeach; ?>
<?php $tabControl->BeginNextTab(); ?>
    <input type="hidden" name="tab" value="catalog">
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('ORDER_PROPS'); ?></b></td>
    </tr>
    <tr align="center">
        <td colspan="2"><b><?php echo GetMessage('INFO_2'); ?></b></td>
    </tr>
    <?php foreach($arResult['bitrixOrderTypesList'] as $bitrixOrderType): ?>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('ORDER_TYPE_INFO') . ' ' . $bitrixOrderType['NAME']; ?></b></td>
    </tr>
    <tr class="contragent-type">
        <td width="50%" class="adm-detail-content-cell-l">
            <?php echo GetMessage('CONTRAGENTS_TYPES_LIST'); ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="contragent-type-<?php echo $bitrixOrderType['ID']; ?>" class="typeselect">         
                <?php foreach ($arResult['contragentType'] as $contragentType): ?>
                <option value="<?php echo $contragentType["ID"]; ?>" <?php if ($optionsContragentType[$bitrixOrderType['ID']] == $contragentType['ID']) echo 'selected'; ?>>
                    <?php echo $contragentType["NAME"]; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php $countProps = 1; foreach($arResult['orderProps'] as $orderProp): ?>    
    <?php if($orderProp['ID'] == 'text'): ?>
    <tr class="heading">
        <td colspan="2" style="background-color: transparent;">
            <b>
                <label><input class="addr" type="radio" name="address-detail-<?php echo $bitrixOrderType['ID']; ?>" value="0" <?php if(count($optionsOrderProps[$bitrixOrderType['ID']]) < 6) echo "checked"; ?>><?php echo GetMessage('ADDRESS_SHORT'); ?></label>
                <label><input class="addr" type="radio" name="address-detail-<?php echo $bitrixOrderType['ID']; ?>" value="1" <?php if(count($optionsOrderProps[$bitrixOrderType['ID']]) > 5) echo "checked"; ?>><?php echo GetMessage('ADDRESS_FULL'); ?></label>
            </b>
        </td>
    </tr>
    <?php endif; ?>
    <tr <?php if ($countProps > 4) echo 'class="address-detail-' . $bitrixOrderType['ID'] . '"'; if(($countProps > 4) && (count($optionsOrderProps[$bitrixOrderType['ID']]) < 6)) echo 'style="display:none;"';?>>
        <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $orderProp['ID']; ?>">
            <?php echo $orderProp['NAME']; ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="order-prop-<?php echo $orderProp['ID'] . '-' . $bitrixOrderType['ID']; ?>" class="typeselect">
                <option value=""></option>              
                <?php foreach ($arResult['arProp'][$bitrixOrderType['ID']] as $arProp): ?>
                <option value="<?php echo $arProp['CODE']; ?>" <?php if ($optionsOrderProps[$bitrixOrderType['ID']][$orderProp['ID']] == $arProp['CODE']) echo 'selected'; ?>>
                    <?php echo $arProp['NAME']; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php $countProps++; endforeach; ?>
    <?if (isset($arResult['customFields']) && count($arResult['customFields']) > 0):?>
        <tr class="heading custom-detail-title">
            <td colspan="2" style="background-color: transparent;">
                <b>
                    <?=GetMessage("ORDER_CUSTOM"); ?>
                </b>
            </td>
        </tr>
        <?foreach($arResult['customFields'] as $customFields):?>
            <tr class="custom-detail-<?=$customFields['ID'];?>">
                <td width="50%" class="" name="">
                    <?=$customFields['NAME']; ?>
                </td>
                <td width="50%" class="">
                    <select name="custom-fields-<?=$customFields['ID'] . '-' . $bitrixOrderType['ID']; ?>" class="typeselect">
                        <option value=""></option>              
                        <?foreach ($arResult['arProp'][$bitrixOrderType['ID']] as $arProp):?>
                            <option value="<?=$arProp['CODE']?>" <?php if ($optionsCustomFields[$bitrixOrderType['ID']][$customFields['ID']] == $arProp['CODE']) echo 'selected'; ?>>
                            <?=$arProp['NAME']; ?>
                            </option>
                        <?endforeach;?>
                    </select>
                </td>
            </tr>
        <?endforeach;?>
    <?endif;?>
    <tr class="heading legal-detail-title-<?php echo $bitrixOrderType['ID'];?>" <?php if(count($optionsLegalDetails[$bitrixOrderType['ID']])<1) echo 'style="display:none"'; ?>>
        <td colspan="2" style="background-color: transparent;">
            <b>
                <?php echo GetMessage('LEGAL_DETAIL'); ?>
            </b>
        </td>
    </tr>
    <?php foreach($arResult['legalDetails'] as $legalDetails): ?>
    <tr class="legal-detail-<?php echo $bitrixOrderType['ID'];?> <?php foreach($legalDetails['GROUP'] as $gr) echo $gr . ' ';?>" <?php if(!in_array($optionsContragentType[$bitrixOrderType['ID']], $legalDetails['GROUP'])) echo 'style="display:none"'; ?>>
        <td width="50%" class="" name="<?php ?>">
            <?php echo $legalDetails['NAME']; ?>
        </td>
        <td width="50%" class="">
            <select name="legal-detail-<?php echo $legalDetails['ID'] . '-' . $bitrixOrderType['ID']; ?>" class="typeselect">
                <option value=""></option>              
                <?php foreach ($arResult['arProp'][$bitrixOrderType['ID']] as $arProp): ?>
                <option value="<?php echo $arProp['CODE']; ?>" <?php if ($optionsLegalDetails[$bitrixOrderType['ID']][$legalDetails['ID']] == $arProp['CODE']) echo 'selected'; ?>>
                    <?php echo $arProp['NAME']; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>   
    <?php endforeach; ?>
    <?php endforeach; ?>
    
<?php $tabControl->BeginNextTab(); ?>
    <input type="hidden" name="tab" value="catalog">
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('ORDER_DISCH'); ?></b></td>
    </tr>    
    <tr class="heading">
        <td colspan="2">
            <b>
                <label><input class="addr" type="radio" name="order-discharge" value="0" <?php if($optionsDischarge == 0) echo "checked"; ?>><?php echo GetMessage('DISCHARGE_AGENT'); ?></label>
                <label><input class="addr" type="radio" name="order-discharge" value="1" <?php if($optionsDischarge == 1) echo "checked"; ?>><?php echo GetMessage('DISCHARGE_EVENTS'); ?></label>
            </b>
        </td>
    </tr>  
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('ORDERS_OPTIONS'); ?></b></td>
    </tr>    
    <tr class="heading">
        <td colspan="2">
            <b>
                <label><input class="addr" type="checkbox" name="order-numbers" value="Y" <?php if($optionsOrderNumbers == 'Y') echo "checked"; ?>> <?php echo GetMessage('ORDER_NUMBERS'); ?></label>
            </b>
        </td>
    </tr>  
<?php endif;?>
<?php $tabControl->Buttons(); ?>
    <input type="hidden" name="Update" value="Y" />
    <input type="submit" title="<?php echo GetMessage('ICRM_OPTIONS_SUBMIT_TITLE'); ?>" value="<?php echo GetMessage('ICRM_OPTIONS_SUBMIT_VALUE'); ?>" name="btn-update" class="adm-btn-save" />
<?php $tabControl->End(); ?>
</form>    
<?php } ?>

<?php //order upload?>
<?php if($_GET['upl'] == 1){?>
<style type="text/css">
    .instal-load-label {
        color: #000;
        margin-bottom: 15px;
    }

    .instal-progress-bar-outer {
        height: 32px;
        border:1px solid;
        border-color:#9ba6a8 #b1bbbe #bbc5c9 #b1bbbe;
        -webkit-box-shadow: 1px 1px 0 #fff, inset 0 2px 2px #c0cbce;
        box-shadow: 1px 1px 0 #fff, inset 0 2px 2px #c0cbce;
        background-color:#cdd8da;
        background-image:-webkit-linear-gradient(top, #cdd8da, #c3ced1);
        background-image:-moz-linear-gradient(top, #cdd8da, #c3ced1);
        background-image:-ms-linear-gradient(top, #cdd8da, #c3ced1);
        background-image:-o-linear-gradient(top, #cdd8da, #c3ced1);
        background-image:linear-gradient(top, #ced9db, #c3ced1);
        border-radius: 2px;
        text-align: center;
        color: #6a808e;
        text-shadow: 0 1px rgba(255,255,255,0.85);
        font-size: 18px;
        line-height: 35px;
        font-weight: bold;
    }

    .instal-progress-bar-alignment {
        height: 28px;
        margin: 0;
        position: relative;
    }

    .instal-progress-bar-inner {
        height: 28px;
        border-radius: 2px;
        border-top: solid 1px #52b9df;
        background-color:#2396ce;
        background-image:-webkit-linear-gradient(top, #27a8d7, #2396ce, #1c79c0);
        background-image:-moz-linear-gradient(top, #27a8d7, #2396ce, #1c79c0);
        background-image:-ms-linear-gradient(top, #27a8d7, #2396ce, #1c79c0);
        background-image:-o-linear-gradient(top, #27a8d7, #2396ce, #1c79c0);
        background-image:linear-gradient(top, #27a8d7, #2396ce, #1c79c0);
        position: absolute;
        overflow: hidden;
        top: 1px;
        left:0;
    }

    .instal-progress-bar-inner-text {
        color: #fff;
        text-shadow: 0 1px rgba(0,0,0,0.2);
        font-size: 18px;
        line-height: 32px;
        font-weight: bold;
        text-align: center;
        position: absolute;
        left: -2px;
        top: -2px;
    }
    
    .order-upload-button{
        padding: 1px 13px 2px;
        height:28px;
    }
    
    .order-upload-button div{
        float:right; 
        position:relative; 
        visible: none;
    }
</style>

<script type="text/javascript">
    $(document).ready(function() { 
        $('#percent').width($('.instal-progress-bar-outer').width());

        $(window).resize(function(){ // strechin progress bar
            $('#percent').width($('.instal-progress-bar-outer').width());
        });

        // orderUpload function
        function orderUpload() {

            var handlerUrl = $('#upload-orders').attr('action');
            var step       = $('input[name="step"]').val();
            var orders     = $('input[name="orders"]').val();
            var data = 'orders=' + orders + '&step=' + step + '&ajax=2';

            // ajax request
            $.ajax({
                type: 'POST',
                url: handlerUrl,
                data: data,
                dataType: 'json',
                success: function(response) {
                    $('input[name="step"]').val(response.step);
                    if(response.step == 'end'){
                        $('input[name="step"]').val(0);
                        BX.closeWait();
                    }
                    else{
                        orderUpload();   
                    }
                    $('#indicator').css('width', response.percent + '%');
                    $('#percent').html(response.percent + '%');
                    $('#percent2').html(response.percent + '%');

                },
                error: function () {
                    BX.closeWait();
                    $('#status').text('<?php echo GetMessage('MESS_4'); ?>');

                    alert('<?php echo GetMessage('MESS_5'); ?>');
                }
            });
        }

        $('input[name="start"]').live('click', function() {  
            BX.showWait();

            orderUpload();

            return false;
        });
    });
</script>
<br>
<form id="upload-orders" action="<?php echo $uri; ?>" method="POST">
    <input type="hidden" name="step" value="0">
    <div class="adm-detail-content-item-block">
        <table class="adm-detail-content-table edit-table" id="edit1_edit_table">
            <tbody>
                <tr class="heading">
                    <td colspan="2"><b><?php echo GetMessage('ORDER_UPLOAD'); ?></b></td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-r"><?php echo GetMessage('ORDER_NUMBER'); ?> <input id="order-nombers" style="width:86%" type="text" value="" name="orders"></td>
                </tr>
            </tbody>
        </table>
        <div class="instal-load-block" id="result">
            <div class="instal-load-label" id="status"><?php echo GetMessage('ORDER_UPLOAD_INFO'); ?></div>

            <div class="instal-progress-bar-outer">
                <div class="instal-progress-bar-alignment" style="width: 100%;">
                    <div class="instal-progress-bar-inner" id="indicator" style="width: 0%;">
                        <div class="instal-progress-bar-inner-text" style="width: 100%;" id="percent">0%</div>
                    </div>
                    <span id="percent2">0%</span>
                </div>
            </div>
        </div>
        <br />
        <div class="order-upload-button">
            <div align="left">
                <input type="submit" name="start" value="<?php echo GetMessage('ORDER_UPL_START'); ?>" class="adm-btn-save">
            </div>
        </div>
    </div>
</form>
<?php }?>

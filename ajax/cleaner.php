<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 15.04.2015
 * Time: 11:51
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$result = false;
if ( isset($_REQUEST['action']) && isset($_REQUEST['order']) ){
    $action = trim($_REQUEST['action']);
    $orderID = intVal($_REQUEST['order']);

    if ( $orderID > 0 ){
        $active = bhTools::getActiveOrders();
        $avail = bhTools::getAvailOrders();
        $cleanerID = intVal($_REQUEST['cleaner']);
        $props = bhOrder::getProps($orderID);


        switch ($action){
            case 'add':
                if ($props['Cleaner']['VALUE'] != '' && $props['Cleaner']['VALUE'] != 0){
                    break;
                }
                $result = bhCleaner::addToOrder($cleanerID , $orderID);
                if ( $result ){
                    bhTools::setActiveOrders($active + 1);
                    bhTools::setAvailOrders($avail - 1);
                    bhCleaner::sendNoticeTake($orderID, $cleanerID);
                } else{

                }
                break;
            case 'remove':
                $propId = intVal($_REQUEST['propId']);
                if ( $propId && $propId > 0 ){
                    $result = bhCleaner::removeFromOrder($propId, $orderID);
                }
                if( $result ){
                    bhTools::setActiveOrders($active - 1);
                    bhTools::setAvailOrders($avail + 1);
                    CSaleOrderChange::AddRecord($orderID, 'ORDER_CLEANER_CANCEL', array('DATE'=>$cleanerID));

                    $arCleaner = bhTools::formatUser($cleanerID);
                    $arCleaner = $arCleaner[$cleanerID];

                    $rsUser = CUser::GetByID($cleanerID);
                    $arUser = $rsUser->Fetch();

                    $stringSms = 'Клинер '.$arCleaner['NAME'].' отказался от заказа '.$orderID.'. Просьба назначить нового клинера!';

                    if ($arUser["PERSONAL_CITY"]==617){
                        sendsms(MANAGER_PHONE, $stringSms);
                        sendsms(MANAGER_PHONE_SPB_TEST, $stringSms);
                    }
                    else{
                        sendsms(MANAGER_PHONE_MSK, $stringSms);
                        sendsms(MANAGER_PHONE_MSK_TEST, $stringSms);
                    }

                    //$result = true;
                } else {}
                break;
            case 'finish':
                $arOrder = CSaleOrder::getByID($orderID);

                bhOrder::setStatusF($arOrder, $props);
                bhTools::setActiveOrders($active - 1);
                $result = true;
                break;
        }
    }
}
$arResult['COUNT'] = $_SESSION['bh_active_orders'];

if ( !$result ){
    echo 'Error';
} else {
    $arResult['HTML'] = '<script>
    location.reload();
    </script>';
}
echo json_encode($arResult);

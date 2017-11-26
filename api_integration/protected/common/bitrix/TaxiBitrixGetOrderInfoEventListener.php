<?php

require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

class TaxiBitrixGetOrderInfoEventListener extends TaxiClientEventListener
{

    public $lastIBlock;

    public function onEvent($event)
    {
        if (!$this->isInBitrix()) {
            return true;
        } else {
            if ($this->needWriteToBitrix() === true) {
                $order_info = $event->getOrderInfoResult();
                $this->updateOrderInBitrix($order_info);
            }
        }
    }

    private function updateOrderInBitrix($order_info)
    {
        $arParams = array(
            'ID'           => $order_info->id,
            'STATUS_ORDER' => $order_info->status,
            'STATUS_LABEL' => $order_info->statusLabel,
        );

        if (isset($order_info->driverFio))
            $arParams['DRIVER'] = $order_info->driverFio;

        if (isset($order_info->detailOrderInfo))
            $arParams['ORDER_INFO'] = $order_info->detailOrderInfo;

        if (isset($order_info->carDescription))
            $arParams['CAR'] = $order_info->carDescription;

        if (isset($order_info->carTime))
            $arParams['PORCH_TIME'] = $order_info->carTime . ' мин.';

        if (isset($order_info->cost))
            $arParams['TOTAL_PRICE'] = $order_info->cost;


        $tmp = new TaxiHttpRequest();
        $url = $tmp->createGetUrl('http://' . $_SERVER['SERVER_NAME'] . '/api_integration/include/bitrix/update_order_info.php', $arParams);
        $connection = new TaxiHttpConnection();
        $connection->timeout = 20;
        $connection->executeGetQuery($url);
        $countRows= $connection->getLastResultString();
        $this->log->error("OTVET" . "\n"
                    . CVarDumper::dumpAsString($countRows));
        if (!$countRows) {
            $this->log->error("поля инфоблока не обновлены" . "\n"
                    . CVarDumper::dumpAsString($this->lastIBlock));
            return null;
        } else {
            $this->log->info("Количество обновленных полей: " . $countRows
                    . "\n");
            return $countRows;
        }
    }

}

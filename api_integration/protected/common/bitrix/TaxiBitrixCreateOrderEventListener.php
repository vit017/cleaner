<?php

require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");




class TaxiBitrixCreateOrderEventListener extends TaxiClientEventListener
{

    public $lastIBlock;

    

    
    private function sendOrderToBitrix()
    {
        $tmp = new TaxiHttpRequest();

        $getParams = $this->currentEvent->commandParams;
        $getParams['XDEBUG_SESSION_START'] = 'netbeans-xdebug';
        $url = $tmp->createGetUrl('http://' . $_SERVER['SERVER_NAME'] . '/api_integration/include/bitrix/send_order.php', $getParams);

        $this->log->info("Посылаем заказ в Битрикс: " . $url
                . "\n" . CVarDumper::dumpAsString($this->lastIBlock));

        $connection = new TaxiHttpConnection();
        $connection->timeout = 20;
        $connection->executeGetQuery($url);
        $bitrixOrderId = $connection->getLastResultString();
        if (!$bitrixOrderId || !preg_match('/^\d+$/', $bitrixOrderId)) {
            $this->log->error("Не удалось отправить заказ в Битрикс" . "\n"
                    . CVarDumper::dumpAsString($this->lastIBlock));
            return null;
        } else {
            $this->log->info("Создан заказ в Битрикс: " . $bitrixOrderId
                    . "\n" . CVarDumper::dumpAsString($this->lastIBlock));
            return $bitrixOrderId;
        }
    }

    
    private function storeOrderId()
    {
        $event = $this->currentEvent;
        
        $orderId = $event->getOrderId();
        if ($orderId) {
            $_SESSION['orderId'] = $orderId;
        } else {
            $this->log->error("Неверный номер заказа при обработке через БИТРИКС - не удалось создать заказ ??");
        }
    }

    
    public function onEvent($event)
    {
        $this->currentEvent = $event;
        $getParams = $this->currentEvent->commandParams;
        $this->storeOrderId();
        if (isset($getParams['isMobile'])) {
            $isMobile = $getParams['isMobile'];
        } else {
            $isMobile = "0";
        }
        if ($this->needWriteToBitrix() === true) {
            if ($this->isInBitrix()) {
                if ($isMobile == "1") {
                    $this->sendOrderToBitrix();
                    return true;
                }
            }
        }
        return true;
    }

}

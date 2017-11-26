<?php

require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");




class TaxiBitrixAddReviewEventListener extends TaxiClientEventListener
{

    public $lastIBlock;

    
    private function sendReviewToBitrix()
    {
        $tmp = new TaxiHttpRequest();

        $getParams = $this->currentEvent->commandParams;
        $getParams['XDEBUG_SESSION_START'] = 'netbeans-xdebug';
        $url = $tmp->createGetUrl('http://' . $_SERVER['SERVER_NAME'] . '/api_integration/include/bitrix/send_review.php', $getParams);

        $this->log->info("Посылаем отзыв в Битрикс: " . $url
                . "\n" . CVarDumper::dumpAsString($this->lastIBlock));

        $connection = new TaxiHttpConnection();
        $connection->timeout = 20;
        $connection->executeGetQuery($url);
        $bitrixOrderId = $connection->getLastResultString();
        if (!$bitrixOrderId || !preg_match('/^\d+$/', $bitrixOrderId)) {
            $this->log->error("Не удалось отправить отзыв в Битрикс" . "\n"
                    . CVarDumper::dumpAsString($this->lastIBlock));
            return null;
        } else {
            $this->log->info("Создан отзыв в Битрикс: " . $bitrixOrderId
                    . "\n" . CVarDumper::dumpAsString($this->lastIBlock));
            return $bitrixOrderId;
        }
    }

    
    public function onEvent($event)
    {
        $this->currentEvent = $event;
        $getParams = $this->currentEvent->commandParams;
        if (isset($getParams['isMobile'])) {
            $isMobile = $getParams['isMobile'];
        } else {
            $isMobile = "0";
        }
        if ($this->needWriteToBitrix() === true) {
            if ($this->isInBitrix()) {
                if ($isMobile == "1") {
                    $this->sendReviewToBitrix();
                    return true;
                }
            }
        }
        return true;
    }

}

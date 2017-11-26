<?php



class TaxiClientEventListener extends TaxiEventListener
{

    private $clientConfig;

    protected function isInBitrix()
    {
        $prologPath = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
        return is_file($prologPath);
    }

    protected function needWriteToBitrix()
    {
        $this->clientConfig = TaxiEnv::$config->getClientConfig();
        if ((isset($this->clientConfig['options']['needWriteToBitrix'])) && ($this->clientConfig['options']['needWriteToBitrix'] === true)) {
            return true;
        }
        return false;
    }

    public function onEvent($event)
    {
        
    }

}

<?php




class TaxiAccessRulleSmsAuthorization extends TaxiAccessRulle
{

    
    public function checkAccess($commandName, $params)
    {
        if (isset($params['phone']) && $params['phone']) {
            if ($this->adapter->clientAuthorization->checkTokenByPhone($params['phone'])) {
                return true;
            } else {
                $this->addError("Нарушение проверки авторизации через СМС для телефона {$params['phone']}");
            }
        }
        return false;
    }

}

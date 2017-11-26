<?php




abstract class TaxiSmsAdapter extends TaxiAdapter
{

    
    public $iphoneHackRegistrationTel = null;
    
    private $_useSmsAuthorization = false;
    
    private $_smsValidator;

    public function __construct()
    {
        parent::__construct();
        $this->accessRulles = new TaxiAccessRulles($this);
    }

    

    
    public function getUseSmsAuthorization()
    {
        return $this->_useSmsAuthorization;
    }

    

    
    public function setUseSmsAuthorization($useSmsAuthorization)
    {
        $this->_useSmsAuthorization = $useSmsAuthorization;

        if (!$useSmsAuthorization) {
            $this->accessRulles->clear();
        } else {
            $this->accessRulles->add('createOrder', array(
                new TaxiAccessRulleSmsAuthorization(),
            ));
        }
    }

    
    public function getSmsValidator()
    {
        if (!$this->_smsValidator) {
            $this->_smsValidator = new TaxiSmsValidator();
        }
        return $this->_smsValidator;
    }

    
    public function needSendSms($phone = null)
    {
        $need = false;
        if ($this->useSmsAuthorization) {
            if (!$this->clientAuthorization->checkTokenByPhone($phone)) {
                $need = true;
            }
        }
        return $need;
    }

    
    public function sendSms($phone, $typeId)
    {
        if ($smsCode = $this->smsValidator->generateSms($phone)) {
            $message = "Уважаемый клиент! Ваш код подтверждения {$smsCode}. Спасибо!";
            if (!$this->sendRealSms($phone, $message)) {
                throw new TaxiException("Не удалось выполнить реальную отправку СМС на номер {$phone}");
            } else {
                $info = new TaxiAutorizationInfo();
                $info->isAuthorizedNow = false;
                $info->phone = $phone;
                $info->rawInfo = null;
                $info->resultCode = null;
                $info->success = true;
                $currentClass = get_class($this);
                if (TaxiEnv::$DEBUG || get_class($this) === 'TaxiTestAdapter') {
                    $info->smsMessage = $message;
                    $info->resultCode = $smsCode;
                }
                $info->text = "Успешный запрос СМС кода на телефон {$phone}";
                return $info;
            }
        } else {
            throw new TaxiException("Не удалось выполнить запрос кода СМС на номер {$phone}");
        }
    }

    
    abstract public function sendRealSms($toPhone, $message);

    
    public function login($phone, $typeId, $smsCode)
    {
        $info = new TaxiAutorizationInfo();
        if ($this->smsValidator->validateSms($phone, $smsCode, $remainedAttempts)) {
            $info->isAuthorizedNow = true;
            $info->phone = $phone;
            if (!$info->browserKey || !$this->clientAuthorization->checkBrowserKey($info->browserKey)) {
                $info->browserKey = $this->clientAuthorization->createCookieBrowserUniqueKey();
            }
            $info->token = $this->clientAuthorization->createCookieToken($phone, $info->browserKey);
            $info->text = "Успешная авторизация через СМС код {$smsCode} на телефон {$phone}";
            $info->resultCode = null;
            $info->success = true;
        } else {
            $this->log->info("Неверная попытка авторизации, осталось {$remainedAttempts}");
            $info->isAuthorizedNow = false;
            $info->token = null;
            $info->browserKey = null;
            $info->phone = $phone;
            if ($remainedAttempts === 0) {
                $info->rawInfo = 'Превышено число попыток';
            }
            $info->resultCode = null;
            $info->success = false;
            $info->text = "Неверный код {$smsCode} для авторизации на телефон {$phone}";
        }
        return $info;
    }

    
    public function isLogined($phone, $typeId)
    {
        $info = new TaxiAutorizationInfo();

        $success = $this->clientAuthorization->checkAuthorization($info->token, $phone, $info->browserKey);
        if ($success) {
            $this->log->info("Success checking token for phone {$phone} && browser key {$info->browserKey}");
            return true;
        } else {
            $this->log->warning("Failed checking token for phone {$phone} && browser key {$info->browserKey}");
            return false;
        }
    }

    
    public function createOrder($fromCity, $fromStreet, $fromHouse, $fromHousing, $fromBuilding, $fromPorch, $fromLat, $fromLon, $toCity, $toStreet, $toHouse, $toHousing, $toBuilding, $toPorch, $toLat, $toLon, $clientName, $phone, $priorTime, $customCarId, $customCar, $carType, $carGroupId, $tariffGroupId, $comment, $isMobile, $additional)
    {
        if (!empty($this->iphoneHackRegistrationTel) && ($phone == $this->iphoneHackRegistrationTel)) {
            $adapter = new TaxiAppleFakeAdapter();
            return $adapter->createOrder($fromCity, $fromStreet, $fromHouse, $fromHousing, $fromBuilding, $fromPorch, $fromLat, $fromLon, $toCity, $toStreet, $toHouse, $toHousing, $toBuilding, $toPorch, $toLat, $toLon, $clientName, $phone, $priorTime, $customCarId, $customCar, $carType, $carGroupId, $tariffGroupId, $comment, $isMobile, $additional);
        } else {
            return null;
        }
    }

    
    public function getOrderInfo($orderId)
    {
        if (!empty($this->iphoneHackRegistrationTel)) {
            $cache = new TaxiFileCache('/' . 'TaxiAppleFakeAdapter' . '.dat');
            $orderData = $cache->getValue('orderData' . $orderId);
            if (!empty($orderData)) {
                if (isset($orderData['phone']) && ($orderData['phone'] == $this->iphoneHackRegistrationTel)) {
                    $adapter = new TaxiAppleFakeAdapter();
                    return $adapter->getOrderInfo($orderId);
                }
            }
        } else {
            return null;
        }
    }

    
    public function rejectOrder($orderId)
    {
        if (!empty($this->iphoneHackRegistrationTel)) {
            $cache = new TaxiFileCache('/' . 'TaxiAppleFakeAdapter' . '.dat');
            $orderData = $cache->getValue('orderData' . $orderId);
            if (!empty($orderData)) {
                if (isset($orderData['phone']) && ($orderData['phone'] == $this->iphoneHackRegistrationTel)) {
                    $adapter = new TaxiAppleFakeAdapter();
                    return $adapter->rejectOrder($orderId);
                }
            }
        } else {
            return null;
        }
    }

    
    public function getCarInfo($carId)
    {
        if (!empty($this->iphoneHackRegistrationTel)) {
            $cache = new TaxiFileCache('/' . 'TaxiAppleFakeAdapter' . '.dat');
            $carData = $cache->getValue('carLive' . $carId);
            if (!empty($carData)) {
                $adapter = new TaxiAppleFakeAdapter();
                return $adapter->getCarInfo($carId);
            }
        } else {
            return null;
        }
    }

    
    public function checkIphoneHackRegistrationTel($phone)
    {
        if ($phone == $this->iphoneHackRegistrationTel) {
            return true;
        }
    }

}

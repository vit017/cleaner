<?php


class TaxiRouteDataBaseLayer
{
    
    public $_tariffDataBaseHost;
    
    public $_tariffDataBasename;
    
    public $_tariffDataBaseUser;
    
    public $_tariffDataBasePassword;
    
    public $_log;
    
    public $_dbh;
    public $_tenantId;
    
    public function __construct($tariffDataBaseHost, $tariffDataBasename, $tariffDataBaseUser, $tariffDataBasePassword)
    {
        $this->_tariffDataBaseHost = $tariffDataBaseHost;
        $this->_tariffDataBasename = $tariffDataBasename;
        $this->_tariffDataBaseUser = $tariffDataBaseUser;
        $this->_tariffDataBasePassword = $tariffDataBasePassword;
        $this->_log = new TaxiLog($this);
        $this->connectToDataBase($this->_tariffDataBaseHost, $this->_tariffDataBasename, $this->_tariffDataBaseUser, $this->_tariffDataBasePassword);
    }
    public function setTenantId($tenantId)
    {
        $this->_tenantId = $tenantId;
    }
    public function getTenantId()
    {
        return $this->_tenantId;
    }
    
    public function connectToDataBase($tariffDataBaseHost, $tariffDataBasename, $tariffDataBaseUser, $tariffDataBasePassword)
    {
        try {
            $this->_dbh = new PDO("mysql:host=$tariffDataBaseHost;dbname=$tariffDataBasename;charset=utf-8", $tariffDataBaseUser, $tariffDataBasePassword);
            $this->_log->info('Соединение с бд тарифов установлено');
        } catch (PDOException $exception) {
            $this->_log->info($exception);
        }
    }
    
    public function getBasePolygon()
    {
        $tenantId = $this->getTenantId();
        try {
            $stm = $this->_dbh->prepare("SELECT polygon from tbl_parking where type='basePolygon' and tenant_id=:tenantId");
            $stm->bindValue(':tenantId', $tenantId);
            $stm->execute();
            $polygon = $stm->fetchColumn();
            return $polygon;
        } catch (PDOException $exception) {
            $this->_log->info($exception);
        }
    }
    
    public function getParkings()
    {
        $tenantId = $this->getTenantId();
        try {
            $stm = $this->_dbh->prepare("SELECT parking_id,type,polygon from tbl_parking where type<>'basePolygon' and tenant_id=:tenantId");
            $stm->bindValue(':tenantId', $tenantId);
            $stm->execute();
            $polygons = $stm->fetchAll(PDO::FETCH_ASSOC);
            return $polygons;
        } catch (PDOException $exception) {
            $this->_log->info($exception);
        }
    }
    
    public function getParkingTypeByid($parkingId)
    {
        $tenantId = $this->getTenantId();
        try {
            $stm = $this->_dbh->prepare("SELECT type from tbl_parking where type<>'basePolygon' and parking_id = :parkingId and tenant_id=:tenantId ");
            $stm->bindValue(':parkingId', $parkingId);
            $stm->bindValue(':tenantId', $tenantId);
            $stm->execute();
            $parkingType = $stm->fetchColumn();
            return $parkingType;
        } catch (PDOException $exception) {
            $this->_log->info($exception);
        }
    }
    
    public function getTariffData($tariffGroupId, $zone)
    {
        try {
            $stm = $this->_dbh->prepare("SELECT * from tbl_option_tariff where tariff_id= :tariffGroupId and zone = :zone");
            $stm->bindValue(':tariffGroupId', $tariffGroupId);
            $stm->bindValue(':zone', $zone);
            $stm->execute();
            $tariffData = $stm->fetch(PDO::FETCH_ASSOC);
            return $tariffData;
        } catch (PDOException $exception) {
            $this->_log->info($exception);
        }
    }
    
    public function findFixTariffs($tariffGroupId)
    {
        try {
            $stm = $this->_dbh->prepare("SELECT option_id from tbl_fix_option_tariff where tariff_id =:tariffGroupId ");
            $stm->bindValue(':tariffGroupId', $tariffGroupId);
            $stm->execute();
            $option_ids = $stm->fetchAll(PDO::FETCH_ASSOC);
            $fixTariffs = array();
            if (!empty($option_ids)) {
                foreach ($option_ids as $optionId) {
                    $stm = $this->_dbh->prepare("SELECT fix_tariff_id from tbl_fix_option_has_tariff where option_id =:optionId ");
                    $stm->bindValue(':optionId', $optionId['option_id']);
                    $stm->execute();
                    $option_ids = $stm->fetchAll(PDO::FETCH_ASSOC);
                    $fixTariffs = array_merge($fixTariffs, $option_ids);
                }
                if ($fixTariffs) {
                    $resultTarifffs = array();
                    foreach ($fixTariffs as $fixTariff) {
                        $id = $fixTariff['fix_tariff_id'];
                        $stm = $this->_dbh->prepare("SELECT * from tbl_fix_tariff where fix_id = :id");
                        $stm->bindValue(':id', $id);
                        $stm->execute();
                        $data = $stm->fetchAll(PDO::FETCH_ASSOC);
                        $resultTarifffs = array_merge($resultTarifffs, $data);
                    }
                }
            }
            return isset($resultTarifffs) ? $resultTarifffs : null;
        } catch (PDOException $exception) {
            $this->_log->info($exception);
        }
    }
    
    public function getAdditionalCost($name, $tariffGroupId)
    {
        try {
            $stm = $this->_dbh->prepare("SELECT option_id from tbl_car_option where name= :name");
            $stm->bindValue(':name', $name);
            $stm->execute();
            $optionId = $stm->fetchColumn();
            if (!empty($optionId)) {
                $stm = $this->_dbh->prepare("SELECT price from tbl_additional_option where option_id= :optionId and tariff_id= :tariffId");
                $stm->bindValue(':optionId', $optionId);
                $stm->bindValue(':tariffId', $tariffGroupId);
                $stm->execute();
                $costAdd = $stm->fetchColumn();
                return $costAdd;
            }
            return null;
        } catch (PDOException $exception) {
            $this->_log->info($exception);
            return null;
        }
    }
}

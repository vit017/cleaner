<?php
namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Delivery\Services\EmptyDeliveryService;
use Bitrix\Sale\Exchange;
use Bitrix\Sale\PaySystem\Manager;

class ImportSettings implements Exchange\ISettings
{
    private static $currentSettings = null;
    protected $settings = array();

    /**
     * ImportSettings constructor.
     * @param array|null $settings
     */
    protected function __construct(array $settings = null)
    {
        if($settings !== null)
        {
            $this->settings = $settings;
        }
    }

    /**
     * @return array|null
     * @throws Main\ArgumentNullException
     */
    private static function loadCurrentSettings()
    {
        if(self::$currentSettings === null)
        {

            self::$currentSettings['import']['CURRENCY'] = \CSaleLang::GetLangCurrency(Option::get("sale", "1C_SITE_NEW_ORDERS"));
            self::$currentSettings['import']['SITE_ID'] = Option::get("sale", "1C_SITE_NEW_ORDERS");

            self::$currentSettings['finalStatusId'][Exchange\EntityType::ORDER_NAME] = "F";
            self::$currentSettings['finalStatusOnDelivery'][Exchange\EntityType::ORDER_NAME] = Option::get("sale", "1C_FINAL_STATUS_ON_DELIVERY", "");

            self::$currentSettings['changeStatusFor'][Exchange\EntityType::ORDER_NAME] = Option::get("sale", "1C_CHANGE_STATUS_FROM_1C", "");
            self::$currentSettings['changeStatusFor'][Exchange\EntityType::SHIPMENT_NAME] = '';
            self::$currentSettings['changeStatusFor'][Exchange\EntityType::PAYMENT_CASH_NAME] = '';
            self::$currentSettings['changeStatusFor'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = '';
            self::$currentSettings['changeStatusFor'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

            self::$currentSettings['importableFor'][Exchange\EntityType::USER_PROFILE_NAME] = Option::get("sale", "1C_IMPORT_NEW_ORDERS", "Y");
            self::$currentSettings['importableFor'][Exchange\EntityType::PROFILE_NAME] = Option::get("sale", "1C_IMPORT_NEW_ORDERS", "Y");
            self::$currentSettings['importableFor'][Exchange\EntityType::ORDER_NAME] = Option::get("sale", "1C_IMPORT_NEW_ORDERS", "Y");
            self::$currentSettings['importableFor'][Exchange\EntityType::SHIPMENT_NAME] = Option::get("sale", "1C_IMPORT_NEW_SHIPMENT", "Y");
            self::$currentSettings['importableFor'][Exchange\EntityType::PAYMENT_CASH_NAME] = Option::get("sale", "1C_IMPORT_NEW_PAYMENT", "Y");
            self::$currentSettings['importableFor'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = Option::get("sale", "1C_IMPORT_NEW_PAYMENT", "Y");
            self::$currentSettings['importableFor'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = Option::get("sale", "1C_IMPORT_NEW_PAYMENT", "Y");

            self::$currentSettings['accountNumberPrefix'][Exchange\EntityType::ORDER_NAME] = Option::get("sale", "1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX", "");
            self::$currentSettings['accountNumberPrefix'][Exchange\EntityType::SHIPMENT_NAME] = '';
            self::$currentSettings['accountNumberPrefix'][Exchange\EntityType::PAYMENT_CASH_NAME] = '';
            self::$currentSettings['accountNumberPrefix'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = '';
            self::$currentSettings['accountNumberPrefix'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

            self::$currentSettings['paySystem'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = Option::get("sale", "1C_IMPORT_DEFAULT_PS_B", "");
            self::$currentSettings['paySystem'][Exchange\EntityType::PAYMENT_CASH_NAME] = Option::get("sale", "1C_IMPORT_DEFAULT_PS", "");
            self::$currentSettings['paySystem'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = Option::get("sale", "1C_IMPORT_DEFAULT_PS_A", "");

            self::$currentSettings['paySystemDefault'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = Manager::getInnerPaySystemId();
            self::$currentSettings['paySystemDefault'][Exchange\EntityType::PAYMENT_CASH_NAME] = Manager::getInnerPaySystemId();
            self::$currentSettings['paySystemDefault'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = Manager::getInnerPaySystemId();

            self::$currentSettings['shipmentService'][Exchange\EntityType::SHIPMENT_NAME] = Option::get("sale", "1C_IMPORT_DEFAULT_SHIPMENT_SERVICE", "");
            self::$currentSettings['shipmentServiceDefault'][Exchange\EntityType::SHIPMENT_NAME] = EmptyDeliveryService::getEmptyDeliveryServiceId();

            self::$currentSettings['canCreateOrder'][Exchange\EntityType::ORDER_NAME] = '';
			self::$currentSettings['canCreateOrder'][Exchange\EntityType::SHIPMENT_NAME] = Option::get("sale", "1C_IMPORT_NEW_ORDER_NEW_SHIPMENT", "");
			self::$currentSettings['canCreateOrder'][Exchange\EntityType::PAYMENT_CASH_NAME] = '';
			self::$currentSettings['canCreateOrder'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = '';
			self::$currentSettings['canCreateOrder'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = '';

            //self::$currentSettings['shipmentBasketChangeQuantity'][EntityType::SHIPMENT_NAME] = Option::get("sale", "1C_IMPORT_UPDATE_BASKET_QUANTITY", "");

			self::$currentSettings['collisionResolve'][Exchange\EntityType::ORDER_NAME] = Option::get("sale", "1C_IMPORT_COLLISION_RESOLVE", array(Exchange\EntityCollisionType::OrderFinalStatusName));
			self::$currentSettings['collisionResolve'][Exchange\EntityType::SHIPMENT_NAME] = Option::get("sale", "1C_IMPORT_COLLISION_RESOLVE", array(Exchange\EntityCollisionType::ShipmentIsShippedName));
			self::$currentSettings['collisionResolve'][Exchange\EntityType::PAYMENT_CASH_NAME] = Option::get("sale", "1C_IMPORT_COLLISION_RESOLVE", array(Exchange\EntityCollisionType::PaymentIsPayedName));
			self::$currentSettings['collisionResolve'][Exchange\EntityType::PAYMENT_CASH_LESS_NAME] = Option::get("sale", "1C_IMPORT_COLLISION_RESOLVE", array(Exchange\EntityCollisionType::PaymentIsPayedName));
			self::$currentSettings['collisionResolve'][Exchange\EntityType::PAYMENT_CARD_TRANSACTION_NAME] = Option::get("sale", "1C_IMPORT_COLLISION_RESOLVE", array(Exchange\EntityCollisionType::PaymentIsPayedName));

            if(!is_array(self::$currentSettings))
            {
                self::$currentSettings = array();
            }
        }
        return self::$currentSettings;
    }

    /**
     * @param $entityTypeId
     * @return bool
     * @throws Main\ArgumentTypeException
     * @throws Main\NotSupportedException
     */
    public function isImportableFor($entityTypeId)
    {
        if(!is_int($entityTypeId))
        {
            throw new Main\ArgumentTypeException('entityTypeID', 'integer');
        }

        if(!Exchange\EntityType::IsDefined($entityTypeId))
        {
            throw new Main\NotSupportedException("Entity ID: '{$entityTypeId}' is not supported in current context");
        }

        $entityTypeName = Exchange\EntityType::ResolveName($entityTypeId);
        //By default control is enabled
        return isset($this->settings['importableFor'][$entityTypeName]) && $this->settings['importableFor'][$entityTypeName] === 'Y';
    }

    /**
     * @param $entityTypeId
     * @return mixed
     * @throws Main\ArgumentTypeException
     * @throws Main\NotSupportedException
     */
    public function prefixFor($entityTypeId)
    {
        if(!is_int($entityTypeId))
        {
            throw new Main\ArgumentTypeException('entityTypeID', 'integer');
        }

        if(!Exchange\EntityType::IsDefined($entityTypeId))
        {
            throw new Main\NotSupportedException("Entity ID: '{$entityTypeId}' is not supported in current context");
        }

        $entityTypeName = Exchange\EntityType::ResolveName($entityTypeId);
        //By default control is enabled
        return $this->settings['accountNumberPrefix'][$entityTypeName];
    }

    /**
     * @param $entityTypeId
     * @return mixed
     * @throws Main\ArgumentTypeException
     * @throws Main\NotSupportedException
     */
    public function paySystemIdFor($entityTypeId)
    {
        if(!is_int($entityTypeId))
        {
            throw new Main\ArgumentTypeException('entityTypeID', 'integer');
        }

        if(!Exchange\EntityType::IsDefined($entityTypeId))
        {
            throw new Main\NotSupportedException("Entity ID: '{$entityTypeId}' is not supported in current context");
        }

        $entityTypeName = Exchange\EntityType::ResolveName($entityTypeId);
        //By default control is enabled

        return ($this->settings['paySystem'][$entityTypeName] == '' ? $this->settings['paySystemDefault'][$entityTypeName]: $this->settings['paySystem'][$entityTypeName]);


    }

    /**
     * @param $entityTypeId
     * @return mixed
     * @throws Main\ArgumentTypeException
     * @throws Main\NotSupportedException
     */
    public function shipmentServiceFor($entityTypeId)
    {
        if(!is_int($entityTypeId))
        {
            throw new Main\ArgumentTypeException('entityTypeID', 'integer');
        }

        if(!Exchange\EntityType::IsDefined($entityTypeId))
        {
            throw new Main\NotSupportedException("Entity ID: '{$entityTypeId}' is not supported in current context");
        }

        $entityTypeName = Exchange\EntityType::ResolveName($entityTypeId);
        //By default control is enabled

        return ($this->settings['shipmentService'][$entityTypeName] == '' ? $this->settings['shipmentServiceDefault'][$entityTypeName]: $this->settings['shipmentService'][$entityTypeName]);

    }

    /**
     * @param $entityTypeId
     * @return string
     * @throws Main\ArgumentTypeException
     * @throws Main\NotSupportedException
     */
    public function finalStatusIdFor($entityTypeId)
    {
        if(!is_int($entityTypeId))
        {
            throw new Main\ArgumentTypeException('entityTypeID', 'integer');
        }

        if(!Exchange\EntityType::IsDefined($entityTypeId))
        {
            throw new Main\NotSupportedException("Entity ID: '{$entityTypeId}' is not supported in current context");
        }

        $entityTypeName = Exchange\EntityType::ResolveName($entityTypeId);
        //By default control is enabled

        return (isset($this->settings['finalStatusId'][$entityTypeName]) ? $this->settings['finalStatusId'][$entityTypeName]: '');

    }

    /**
     * @param $entityTypeId
     * @return string
     * @throws Main\ArgumentTypeException
     * @throws Main\NotSupportedException
     */
    public function finalStatusOnDeliveryFor($entityTypeId)
    {
        if(!is_int($entityTypeId))
        {
            throw new Main\ArgumentTypeException('entityTypeID', 'integer');
        }

        if(!Exchange\EntityType::IsDefined($entityTypeId))
        {
            throw new Main\NotSupportedException("Entity ID: '{$entityTypeId}' is not supported in current context");
        }

        $entityTypeName = Exchange\EntityType::ResolveName($entityTypeId);
        //By default control is enabled

        return (isset($this->settings['finalStatusOnDelivery'][$entityTypeName]) ? $this->settings['finalStatusOnDelivery'][$entityTypeName]: '');

    }

    /**
     * @param $entityTypeId
     * @return string
     * @throws Main\ArgumentTypeException
     * @throws Main\NotSupportedException
     */
    public function changeStatusFor($entityTypeId)
    {
        if(!is_int($entityTypeId))
        {
            throw new Main\ArgumentTypeException('entityTypeID', 'integer');
        }

        if(!Exchange\EntityType::IsDefined($entityTypeId))
        {
            throw new Main\NotSupportedException("Entity ID: '{$entityTypeId}' is not supported in current context");
        }

        $entityTypeName = Exchange\EntityType::ResolveName($entityTypeId);
        //By default control is enabled

        return ($this->settings['changeStatusFor'][$entityTypeName] == 'Y' ? $this->settings['changeStatusFor'][$entityTypeName]: '');
    }

	/**
	 * @param $entityTypeId
	 * @return string
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function canCreateOrder($entityTypeId)
	{
		if(!is_int($entityTypeId))
		{
			throw new Main\ArgumentTypeException('entityTypeID', 'integer');
		}

		if(!Exchange\EntityType::IsDefined($entityTypeId))
		{
			throw new Main\NotSupportedException("Entity ID: '{$entityTypeId}' is not supported in current context");
		}

		$entityTypeName = Exchange\EntityType::ResolveName($entityTypeId);

		return ($this->settings['canCreateOrder'][$entityTypeName] == 'Y' ? $this->settings['canCreateOrder'][$entityTypeName]: '');
	}

    /**
     * @return string
     */
    public function getSiteId()
    {
        return $this->settings['import']['SITE_ID'] !== ""  ?  $this->settings['import']['SITE_ID']: Main\Application::getInstance()->getContext()->getSite();
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->settings['import']['CURRENCY'];
    }

    /**
     * @return ImportSettings
     */
    public static function getCurrent()
    {
        return new static(self::loadCurrentSettings());
    }

	/**
	 * @param $entityTypeId
	 * @return array
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 */
	public function getCollisionResolve($entityTypeId)
	{
		if(!is_int($entityTypeId))
		{
			throw new Main\ArgumentTypeException('entityTypeID', 'integer');
		}

		if(!Exchange\EntityType::IsDefined($entityTypeId))
		{
			throw new Main\NotSupportedException("Entity ID: '{$entityTypeId}' is not supported in current context");
		}

		$entityTypeName = Exchange\EntityType::ResolveName($entityTypeId);

		return is_array($this->settings['collisionResolve'][$entityTypeName]) ? $this->settings['collisionResolve'][$entityTypeName]:array();
	}
}
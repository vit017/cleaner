<?php
namespace Bitrix\Sale\Exchange;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Cashbox\Cashbox;
use Bitrix\Sale\Cashbox\Cashbox1C;
use Bitrix\Sale\Cashbox\CheckManager;
use Bitrix\Sale\Cashbox\Internals\CashboxCheckTable;
use Bitrix\Sale\EntityMarker;
use Bitrix\Sale\Internals\Fields;
use Bitrix\Sale\Order;
use Bitrix\Sale\Result;
use Bitrix\Sale\Exchange;
use Bitrix\Sale\Exchange\OneC;
use Bitrix\Sale\ResultError;
use Bitrix\Sale\ResultWarning;

IncludeModuleLangFile(__FILE__);

class ImportOneCPackage extends ImportPattern
{
	const EVENT_ON_EXCHANGE_CONFIGURE_IMPORTER = 'OnExchangeConfigureImporter';

    private static $instance = null;
    private static $settings = null;

    protected $order = null;

    /** @var  Fields */
    protected $fields;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if(self::$instance === null)
        {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * @param Entity\EntityImport|ProfileImport $item
     * @return Result
     * @throws ArgumentException
	 * @internal
	 */
    protected function modifyEntity($item)
    {
        $result = new Result();

        if(!($item instanceof Exchange\Entity\EntityImport) && !($item instanceof Exchange\Entity\UserProfileImport))
            throw new ArgumentException("Item must be instanceof EntityImport or UserProfileImport");

        $params = $item->getFieldValues();

        $fields = &$params['TRAITS'];

        $converter = OneC\Converter::getInstance($item->getOwnerTypeId());
        $converter->loadSettings($item->getSettings());

        /** @var OneC\Converter $converter*/
        $converter->sanitizeFields($item->getEntity(), $fields);
        $item->refreshData($fields);

        $criterion = $item->getCurrentCriterion($item->getEntity());
        $collision = $item->getCurrentCollision($item->getOwnerTypeId());

		if($criterion->equals($fields))
		{
			$collision->resolve($item);
		}

        if(!$criterion->equals($fields) ||
            ($criterion->equals($fields) && !$item->hasCollisionErrors()))
        {
			$result = $item->import($params);
        }

        return $result;
    }

    private function __clone() {}
    private function __construct() {}

    /**
     * @return Result|null
     * @throws \Bitrix\Main\ArgumentNullException
     */
    static public function checkSettings()
    {
        if(self::$settings === null)
        {
            $result = new Result();
            $message = self::getMessage();

            if(Option::get('catalog', 'default_use_store_control', 'N')=='Y' ||
                Option::get('catalog', 'enable_reservation', 'N')=='Y')
            {
                $result->addError(new Error($message["CC_BSC1_USE_STORE_SALE"]));
            }

            if(Option::get("main", "~sale_converted_15", 'N') <> 'Y')
            {
                $result->addError(new Error($message["CC_BSC1_CONVERT_SALE"]));
            }

            if(Option::get("sale", "allow_deduction_on_delivery", "N") == 'Y')
            {
                $result->addError(new Error($message["CC_BSC1_SALE_ALLOW_DEDUCTION_ON_DELIVERY_ERROR"]));
            }

            self::$settings = $result;
        }

        return self::$settings;
    }

    /**
     * @param array $rawFields
     * @return Result
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    protected function parse(array $rawFields)
    {
        $result = new Result();
        $list = array();

        foreach($rawFields as $raw)
        {
            $documentTypeId = $this->resolveDocumentTypeId($raw);

            $document = OneC\DocumentImportFactory::create($documentTypeId);

            $fields = $document::prepareFieldsData($raw);

            $document->setFields($fields);

            $list[] = $document;
        }

        $result->setData($list);

        return $result;
    }

    /**
     * @param array $fields
     * @return int
     */
    protected function resolveDocumentTypeId(array $fields)
    {
        return OneC\DocumentImport::resolveDocumentTypeId($fields);
    }

    /**
     * @param array $documents
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     */
    protected function convert(array $documents)
    {
		$result = new Result();
        $list = array();
		$settingsShipment = Manager::getSettingsByType(EntityType::SHIPMENT);

        if($settingsShipment->canCreateOrder(EntityType::SHIPMENT)=='Y' && !static::hasDocumentOrder($documents))
		{
			foreach($documents as $document)
			{
				if($document->getOwnerEntityTypeId() == EntityType::SHIPMENT)
				{
					$order['ID_1C'] = $document->getField('ID_1C');
					$order['VERSION_1C'] = $document->getField('VERSION_1C');
					$order['AMOUNT'] = $document->getField('AMOUNT');
					$order['ITEMS'] = $document->getField('ITEMS');
					$order['TAXES'] = $document->getField('TAXES');
					$order['AGENT'] = $document->getField('AGENT');

					$documentOrder = new OneC\OrderDocument(EntityType::ORDER);
					$documentOrder->setFields($order);
					$documents[] = $documentOrder;

					break;
				}
			}
		}

		foreach($documents as $document)
		{
			if($document->getOwnerEntityTypeId() == EntityType::ORDER)
			{
				$agent = $document->getField('AGENT');
				$documentProfile = new OneC\UserProfileDocument();
				$documentProfile->setFields($agent);
				$documents[] = $documentProfile;
				break;
			}
		}

        /** @var OneC\DocumentImport $document */
        foreach($documents as $document)
        {
			$settings = Manager::getSettingsByType($document->getOwnerEntityTypeId());

        	$convertor = OneC\Converter::getInstance($document->getOwnerEntityTypeId());
			$convertor->loadSettings($settings);
            $fields = $convertor->resolveParams($document);

            $loader = Entity\EntityImportLoaderFactory::create($document->getOwnerEntityTypeId());
            $loader->loadSettings($settings);

            if(strlen($document->getId())>0)
                $fieldsEntity = $loader->getByNumber($document->getId());
            else
                $fieldsEntity = $loader->getByExternalId($document->getExternalId());

            if(!empty($fieldsEntity['ID']))
                $fields['TRAITS']['ID'] = $fieldsEntity['ID'];

            $entityImport = Manager::createImport($document->getOwnerEntityTypeId());
            $entityImport->setFields($fields);
            $list[] = $entityImport;
        }

        if($result->isSuccess())
        {
            $result = $this->checkFields($list);
            if($result->isSuccess())
            {
                $result->setData($list);
            }
        }

        return $result;
    }

	/**
	 * @param array $documents
	 * @return bool
	 */
	public static function hasDocumentOrder(array $documents)
	{
		foreach($documents as $document)
		{
			if($document->getOwnerEntityTypeId() == EntityType::ORDER)
				return true;
		}

		return false;
	}

    /**
     * @param array $items
     * @return Result
     */
    protected function checkFields(array $items)
    {
        $result = new Result();

        $parentEntityId = null;

        /** @var Entity\EntityImport $item */
        foreach($items as $item)
        {
            if($item->getOwnerTypeId() == EntityType::ORDER)
            {
                $params = $item->getFieldValues();
                $fields = $params['TRAITS'];

                if($fields['ID']<>'')
                {
                    $parentEntityId = $fields['ID'];
                }
                elseif($fields[$item::getFieldExternalId()]<>'')
                {
                    $parentEntityId = $fields[$item::getFieldExternalId()];
                }
                break 1;
            }
        }

        if(empty($parentEntityId))
            $result->addErrors(array(new Error('Order not found')));

        foreach($items as $item)
        {
            if($item->getOwnerTypeId() <> EntityType::ORDER)
            {
                $params = $item->getFieldValues();
                $fields = $params['TRAITS'];

                if(!empty($parentEntityId) && $fields['ORDER_ID']<>'')
                {
                    if($parentEntityId <> $fields['ORDER_ID'])
                    {
                        $result->addErrors(array(new Error('Order not found')));
                        break 1;
                    }
                }
            }
        }

        foreach($items as $item)
        {
            $params = $item->getFieldValues();
            $fields = $params['TRAITS'];

            if(strlen($fields[$item::getFieldExternalId()])<= 0)
                $result->addErrors(array(new Error(" ".EntityType::getDescription($item->getOwnerTypeId()).": ".GetMessage("SALE_EXCHANGE_EXTERNAL_ID_NOT_FOUND"), 'SALE_EXCHANGE_EXTERNAL_ID_NOT_FOUND')));
        }

        return $result;
    }

    public static function configuration()
    {
		$event = new Event('sale', static::EVENT_ON_EXCHANGE_CONFIGURE_IMPORTER);
		$event->send();

    	Manager::registerInstance(EntityType::ORDER, OneC\ImportSettings::getCurrent(), new OneC\CollisionOrder(), new OneC\CriterionOrder());
        Manager::registerInstance(EntityType::SHIPMENT, OneC\ImportSettings::getCurrent(), new OneC\CollisionShipment(), new OneC\CriterionShipment());
        Manager::registerInstance(EntityType::PAYMENT_CASH, OneC\ImportSettings::getCurrent(), new OneC\CollisionPayment(), new OneC\CriterionPayment());
        Manager::registerInstance(EntityType::PAYMENT_CASH_LESS, OneC\ImportSettings::getCurrent(), new OneC\CollisionPayment(), new OneC\CriterionPayment());
        Manager::registerInstance(EntityType::PAYMENT_CARD_TRANSACTION, OneC\ImportSettings::getCurrent(), new OneC\CollisionPayment(), new OneC\CriterionPayment());
        Manager::registerInstance(EntityType::USER_PROFILE, OneC\ImportSettings::getCurrent());
    }

    /**
     * @param ProfileImport[]|Exchange\Entity\EntityImport[] $items
     * @return ProfileImport[]|Exchange\Entity\EntityImport[]
     */
    protected function sortItems(array $items)
    {
        $list = array();
        $i = 0;

        foreach ($items as $item)
        {
            if($item->getOwnerTypeId() == EntityType::USER_PROFILE)
            {
                $list[$i++] = $item;
            }
        }

        foreach ($items as $item)
        {
            if($item->getOwnerTypeId() == EntityType::ORDER)
            {
                $list[$i++] = $item;
            }
        }

        foreach ($items as $item)
        {
            if($item->getOwnerTypeId() <> EntityType::ORDER && $item->getOwnerTypeId() <> EntityType::USER_PROFILE)
            {
                $list[$i++] = $item;
            }
        }

        return $list;
    }

    /**
     * @param ProfileImport[]|Exchange\Entity\EntityImport[] $items
     * @return Result
     * @inernal
     */
    protected function import(array $items)
    {
        $result = new Result();

		$items = $this->sortItems($items);
        $itemOrder = $this->loadOrder($items);

        if($itemOrder->getEntityId()>0)
        {
        	$r = $this->UpdateCashBoxChecks($itemOrder, $items);
			if($r->isSuccess())
			{
				$this->save($itemOrder, $items);
				return $result;
			}

            $r = $this->onBeforeEntityModify($itemOrder, $items);
            if($r->hasWarnings())
                $this->marker($itemOrder, $r);
        }

		if(!$this->hasCollisionErrors($items))
		{
			/** Only sorted items */
			foreach($items as $item)
			{
				if($item->getOwnerTypeId() == EntityType::USER_PROFILE)
				{
					$r = new Result();
					if($itemOrder->getEntityId() == null)
					{
						$params = $item->getFieldValues();
						$fields = $params['TRAITS'];

						$personalTypeId = $params['TRAITS']['PERSON_TYPE_ID'] = $item->resolvePersonTypeId($fields);

						$property = $params['ORDER_PROPS'];
						if(!empty($property))
						{
							$params['ORDER_PROP'] = $item->getPropertyOrdersByConfig($personalTypeId, array(), $property);
						}

						unset($params['ORDER_PROPS']);
						$item->setFields($params);

						$r = $item->load($fields);
						if($r->isSuccess())
						{
							$r = $this->modifyEntity($item);

							if(!$this->importableItems($r, $item))
							{
								return new Result();
							}
							else
							{
								if(intval($personalTypeId)<=0)
									$r->addError(new Error(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_PERSONAL_TYPE_IS_EMPTY", array("#DOCUMENT_ID#"=>$fields['XML_ID'])), "PACKAGE_ERROR_PERSONAL_TYPE_IS_EPMTY"));

								if(intval($item->getId())<=0)
									$r->addError(new Error(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_USER_IS_EMPTY", array("#DOCUMENT_ID#"=>$fields['XML_ID'])), "PACKAGE_ERROR_USER_IS_EPMTY"));

								if($r->isSuccess())
								{
									/** prepare for import Order */
									$paramsOrder = $itemOrder->getFieldValues();
									$fieldsOrder = &$paramsOrder['TRAITS'];

									if(!empty($property))
									{
										$fieldsOrder['ORDER_PROP'] = $params['ORDER_PROP'];
									}

									$fieldsOrder['USER_ID'] = $item->getId();
									$fieldsOrder['PERSON_TYPE_ID'] = $personalTypeId;
									$itemOrder->setFields($paramsOrder);
								}
							}
						}
					}
				}
				elseif($item->getOwnerTypeId() == EntityType::ORDER)
				{
					$r = $this->modifyEntity($itemOrder);

					if(!$this->importableItems($r, $itemOrder))
					{
						return new Result();
					}
				}
				else
				{
					/** @var Order $order */
					$order = $itemOrder->getEntity();
					$params = $item->getFieldValues();
					$fields = $params['TRAITS'];

					$r = $this->orderIsLoad($order, $itemOrder);
					if(!$r->hasWarnings())
					{
						static::load($item, $fields, $order);

						$r = $this->checkParentById($fields['ID'], $item);
						if(!$r->hasWarnings())
						{
							$isShipped = $order->isShipped();

							$r = $this->modifyEntity($item);

							if($r->isSuccess())
							{
								if($item->getOwnerTypeId() == EntityType::SHIPMENT)
								{
									if(!$isShipped && $order->isShipped())
										$this->onAfterShipmentModifyChangeStatusOnDelivery($itemOrder);
								}
							}
						}
					}
				}

				if(!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					break;
				}
				elseif($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
					break;
				}
			}

			if($result->isSuccess() && !$result->hasWarnings() && !$this->hasCollisionErrors($items))
			{
				$r = $this->onAfterEntitiesModify($itemOrder, $items);
				if(!$r->isSuccess())
					$result->addErrors($r->getErrors());
				if($r->hasWarnings())
					$result->addWarnings($r->getWarnings());
			}
		}

        if($result->isSuccess())
        {
            $r = $this->save($itemOrder, $items);
			if(!$r->isSuccess())
				$result->addErrors($r->getErrors());
			if($r->hasWarnings())
				$result->addWarnings($r->getWarnings());
        }

        return $result;
    }

	/**
	 * @param Result $r
	 * @param Exchange\Entity\EntityImport|Exchange\Entity\UserProfileImport $item
	 * @return bool
	 * @throws ArgumentException
	 */
	private function importableItems(Result $r, $item)
	{
		$result = true;

		$entity = $item->getEntity();

		if($item->getOwnerTypeId() == EntityType::ORDER)
		{
			/** @var Order $entity */
			if($r->isSuccess() &&
				!$r->hasWarnings() &&
				!($entity instanceof Order) &&
				!$item->isImportable())
			{
				$result = false;
			}
		}
		elseif($item->getOwnerTypeId() == EntityType::USER_PROFILE)
		{
			/** @var Exchange\Entity\UserProfileImport $entity */
			if($r->isSuccess() &&
				!$r->hasWarnings() &&
				!($entity instanceof Exchange\Entity\UserProfileImport) &&
				!$item->isImportable())
			{
				$result = false;
			}
		}

		return $result;
	}

    /**
     * @param array $values
     * @internal param array $fields
     */
    public function setFields(array $values)
    {
        foreach ($values as $key=>$value)
        {
            $this->setField($key, $value);
        }
    }

    /**
     * @param $name
     * @param $value
     */
    public function setField($name, $value)
    {
        $this->fields->set($name, $value);
    }

    /**
     * @param $name
     * @return null|string
     */
    public function getField($name)
    {
        return $this->fields->get($name);
    }

    /**
     * @param Entity\EntityImport[] $items
     * @return Entity\OrderImport|null
     */
    protected function loadOrder(array $items)
    {
        foreach ($items as $item)
        {
            if($item->getOwnerTypeId() == EntityType::ORDER)
            {
                $params = $item->getFieldValues();
                $fields = $params['TRAITS'];

                static::load($item, $fields);

                return $item;
            }
        }

        return null;
    }

    /**
     * @param Entity\EntityImport $item
     * @param array $fields
     * @param null $order
     */
    protected static function load(Entity\EntityImport $item, array $fields, $order=null)
    {
        if($item->getOwnerTypeId() <> EntityType::ORDER)
        {
            $item->setParentEntity($order);
        }

        $item->load($fields);
    }

    /**
     * Modify the shipment collection.
     * Remove shipments that were not submitted for processing
     * Add a note to the order saying the shipment was removed
     * @internal
     * @param Entity\OrderImport $orderImport
     * @param Entity\EntityImport[] $items
     * @return Result
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     */
    protected function onBeforeShipmentCollectionModify(Exchange\Entity\OrderImport $orderImport, $items)
    {
        $result = new Result();

        $shipmentItems = array();

        /** @var Order $order */
        $order = $orderImport->getEntity();
        if(empty($order))
            throw new ArgumentNullException('Order');

        foreach($items as $item)
        {
            switch($item->getOwnerTypeId())
            {
                case EntityType::SHIPMENT:
                    $params = $item->getFieldValues();
                    $fields = $params['TRAITS'];

                    if(!empty($fields['ID']))
                        $shipmentItems[] = $fields['ID'];
                    break;
            }
        }

        /** @var \Bitrix\Sale\Shipment $shipment */
        $shipmentList = array();
        $shipmentCollection = $order->getShipmentCollection();
        foreach($shipmentCollection as $shipment)
        {
            if($shipment->isSystem())
                continue;

            if(!in_array($shipment->getId(), $shipmentItems))
                $shipmentList[$shipment->getId()] = $shipment;
        }

        if(!empty($shipmentList))
        {
            foreach($shipmentList as $id=>$shipment)
            {
                $typeId = Entity\ShipmentImport::resolveEntityTypeId($shipment);

                $item = Manager::createImport($typeId);
                static::load($item, array('ID'=>$id), $order);
				$collision = $item->getLoadedCollision();

				$collision->resolve($item);
                if(!$item->hasCollisionErrors())
                {
                    $result = $item->delete();
                }
                else
                {
					$item->setCollisions(EntityCollisionType::BeforeUpdateShipmentDeletedError, $item->getParentEntity());
                }

                $collisions = $item->getCollisions();
                $item->markedEntityCollisions($collisions);
            }
        }

        return $result;
    }

    /**
     * Modify the payment collection.
     * Remove payments that were not submitted for processing
     * Add a note to the order saying the payment was removed
     * @param Entity\OrderImport $orderImport
     * @param Entity\EntityImport[] $items
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     * @inernal
     */
    protected function onBeforePaymentCollectionModify(Exchange\Entity\OrderImport $orderImport, $items)
    {
        $result = new Result();

        $paymentItems = array();

        /** @var Order $order */
        $order = $orderImport->getEntity();
        if(empty($order))
            throw new ArgumentNullException('Order');

        foreach($items as $item)
        {
            switch($item->getOwnerTypeId())
            {
                case EntityType::PAYMENT_CARD_TRANSACTION:
                case EntityType::PAYMENT_CASH:
                case EntityType::PAYMENT_CASH_LESS:
                    $params = $item->getFieldValues();
                    $fields = $params['TRAITS'];

                    if(!empty($fields['ID']))
                        $paymentItems[] = $fields['ID'];
                    break;
            }
        }

        /** @var \Bitrix\Sale\Payment $payment */
        $paymentList = array();
        $paymentCollection = $order->getPaymentCollection();
        foreach($paymentCollection as $payment)
        {
            if(!in_array($payment->getId(), $paymentItems))
                $paymentList[$payment->getId()] = $payment;
        }

        if(!empty($paymentList))
        {
            foreach($paymentList as $id=>$payment)
            {
                $typeId = Entity\PaymentImport::resolveEntityTypeId($payment);

                $item = Manager::createImport($typeId);
                static::load($item, array('ID'=>$id), $order);
				$collision = $item->getLoadedCollision();

				$collision->resolve($item);
				if(!$item->hasCollisionErrors())
                {
                    $result = $item->delete();
                }
                else
                {
					$item->setCollisions(Exchange\EntityCollisionType::BeforeUpdatePaymentDeletedError, $item->getParentEntity());
                }

                $collisions = $item->getCollisions();
                $item->markedEntityCollisions($collisions);
            }
        }

        return $result;
    }

    /**
     * Modify the order and all dependent entities before import
     * @param Entity\OrderImport $orderImport
     * @param Entity\EntityImport[] $items
     * @return Result
     * @throws ArgumentNullException
     * @internal param Entity\OrderImport $order
     * @inernal
     */
    protected function onBeforeBasketModify(Exchange\Entity\OrderImport $orderImport, $items)
    {
        $basketItems = array();

        /** @var Order $order */
        $order = $orderImport->getEntity();
        if(empty($order))
            throw new ArgumentNullException('Order');

        $basket = $order->getBasket();

        foreach($items as $item)
        {
            switch($item->getOwnerTypeId())
            {
                case EntityType::ORDER:
                    $params = $item->getFieldValues();
                    $basketItems = $params['ITEMS'];
                    break;
            }
        }

        $basketItemsIndex = $orderImport::calculateDeltaQuantity($basket, $basketItems);

        $result = $orderImport->synchronizeQuantityBasketItems($basketItemsIndex);

        return $result;
    }

    /**
     * Modify all dependent entities before the order is changed
     * @param Entity\OrderImport $orderImport
     * @param Entity\EntityImport[] $items
     * @return Result
     * @inernal
     */
    protected function onBeforeEntityModify(Exchange\Entity\OrderImport $orderImport, array $items)
    {
        $result = new Result();

        /**
		 * @var Result $basketResult
		 * @var Result $paymentResult
		 * @var Result $shipmentResult
		 * */
		$basketItemsResult = $this->onBeforeBasketModify($orderImport, $items);
		$paymentResult = $this->onBeforePaymentCollectionModify($orderImport, $items);
		$shipmentResult = $this->onBeforeShipmentCollectionModify($orderImport, $items);

		if(!$paymentResult->isSuccess())
			$result->addWarnings($paymentResult->getErrors());

		if(!$shipmentResult->isSuccess())
			$result->addWarnings($shipmentResult->getErrors());

		if(!$basketItemsResult->isSuccess() || !$shipmentResult->isSuccess() || !$paymentResult->isSuccess())
			$result->addWarning(new ResultWarning(GetMessage('SALE_EXCHANGE_PACKAGE_ERROR_ORDER_CANNOT_UPDATE'), "PACKAGE_ERROR_ORDER_CANNOT_UPDATE"));

        return $result;
    }

    /**
     * Modify shipment after changed
     * @param Entity\OrderImport $orderImport
     * @return Result
     * @internal param ImportBase $entity
     */
    protected function onAfterShipmentModifyChangeStatusOnDelivery(Exchange\Entity\OrderImport $orderImport)
    {
        $result = new Result();

        /** @var Order $order */
        $order = $orderImport->getEntity();
        if($order->isShipped())
        {
            $settings = $orderImport->getSettings();
            $status = $settings->finalStatusOnDeliveryFor($orderImport->getOwnerTypeId());
            if($status !== '')
                $order->setField("STATUS_ID", $status);
        }
        return $result;
    }


    /**
     * Modify all dependent entities after the order is changed
     * @param Entity\OrderImport $orderImport
     * @param ImportBase[] $items
     * @return Result
     */
    protected function onAfterEntitiesModify(Exchange\Entity\OrderImport $orderImport, $items)
    {
        $result = new Result();

        foreach ($items as $item)
        {
            if($item->getOwnerTypeId() == EntityType::ORDER)
            {
                /** @var Order $order */
                $order = $orderImport->getEntity();
                $params = $item->getFieldValues();
                $fields = $params['TRAITS'];

                if($fields['1C_PAYED_DATE'] instanceof DateTime)
                {
                    if(!$order->isPaid())
                    {
                        /** @var Exchange\Entity\OrderImport $item */
                        $item->setCollisions(EntityCollisionType::OrderPayedByStatusError, $order);
                    }
                }

                if($fields['1C_DELIVERY_DATE'] instanceof DateTime)
                {
                    if(!$order->isShipped())
                    {
                        /** @var Exchange\Entity\OrderImport $item */
                        $item->setCollisions(EntityCollisionType::OrderShippedByStatusError, $order);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $id
     * @param Entity\EntityImport $item
     * @return Result
     */
    protected function checkParentById($id, Entity\EntityImport $item)
    {
        $result = new Result();

        /** @var \Bitrix\Sale\Internals\Entity $entity */
        $entity = $item->getEntity();

        if(!empty($id) && empty($entity))
        {
            switch($item->getOwnerTypeId())
            {
                case EntityType::PAYMENT_CASH:
                case EntityType::PAYMENT_CARD_TRANSACTION:
                case EntityType::PAYMENT_CASH_LESS:
                    $result->addWarning(new ResultWarning(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_PAYMENT_IS_NOT_RELATED_TO_ORDER_OR_DELETED", array("#DOCUMENT_ID#"=>$id)), "PACKAGE_ERROR_PAYMENT_IS_NOT_RELATED_TO_ORDER_OR_DELETED"));
                    break;
                case EntityType::SHIPMENT:
                    $result->addWarning(new ResultWarning(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_SHIPMENT_IS_NOT_RELATED_TO_ORDER_OR_DELETED", array("#DOCUMENT_ID#"=>$id)), "PACKAGE_ERROR_SHIPMENT_IS_NOT_RELATED_TO_ORDER_OR_DELETED"));
                    break;
            }
        }
        return $result;
    }

	/**
	 * @param $order
	 * @param Entity\OrderImport $itemOrder
	 * @return Result
	 */
	protected function orderIsLoad($order, $itemOrder)
	{
		$result = new Result();

		if(!($order instanceof Order))
		{
			$params = $itemOrder->getFieldValues();
			$fields = $params['TRAITS'];

			$result->addWarning(new ResultWarning(GetMessage("SALE_EXCHANGE_PACKAGE_ERROR_ORDER_IS_NOT_LOADED", array("#DOCUMENT_ID#"=>$fields['ID_1C'])), "PACKAGE_ERROR_ORDER_IS_NOT_LOADED"));
		}

		return $result;
	}

    /**
     * @param Entity\OrderImport $orderImport
     * @param Result $result
     */
    protected function marker(Exchange\Entity\OrderImport $orderImport, Result $result)
    {
        /** @var Order $order */
        $order = $orderImport->getEntity();

        $orderImport->setField('MARKED', 'Y');
        EntityMarker::addMarker($order, $order, $result);
    }

    /**
     * @param Exchange\Entity\OrderImport $orderImport
     * @param Entity\EntityImport[] $items
     * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\UpdateResult|Result|mixed
     */
    protected function save(Exchange\Entity\OrderImport $orderImport, $items)
    {
        foreach ($items as $item)
        {
            if($item instanceof Exchange\Entity\EntityImport)
            {
                $collisions = $item->getCollisions();
                if (!empty($collisions))
                {
                    /** @var ICollision $collision */
                    $item->markedEntityCollisions($collisions);
                }
            }
        }
        return $orderImport->save();
    }

	/**
	 * @param Entity\EntityImport[] $items
	 * @return bool
	 */
	protected function hasCollisionErrors($items)
	{
		foreach($items as $item)
		{
			if($item->hasCollisionErrors())
				return true;
		}
		return false;
	}

    /**
     * @return array
     */
    protected static function getMessage()
    {
        return Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/bitrix/sale.export.1c/component.php');
    }

	/**
	 * @param Entity\OrderImport $orderImport
	 * @param ProfileImport[]|Exchange\Entity\EntityImport[] $items
	 * @return Result
	 * @deprecated
	 */
	private function UpdateCashBoxChecks(Exchange\Entity\OrderImport $orderImport, array $items)
	{
		$result = new Result();
		$bCheckUpdated = false;

		$order = $orderImport->getEntity();

		foreach ($items as $item)
		{
			/** @var Exchange\Entity\PaymentImport $item */

			if($item->getOwnerTypeId() == EntityType::PAYMENT_CASH ||
				$item->getOwnerTypeId() == EntityType::PAYMENT_CASH_LESS ||
				$item->getOwnerTypeId() == EntityType::PAYMENT_CARD_TRANSACTION
			)
			{
				/** @var  $params */
				$params = $item->getFieldValues();
				static::load($item, $params['TRAITS'], $order);

				if($item->getEntityId()>0)
				{
					$entity = $item->getEntity();

					if(isset($params['CASH_BOX_CHECKS']))
					{
						$fields = $params['CASH_BOX_CHECKS'];

						if($fields['ID']>0)
						{
							$res = CashboxCheckTable::getById($fields['ID']);
							if ($data = $res->fetch())
							{
								if($data['STATUS']<>'Y')
								{
									$applyResult = Cashbox1C::applyCheckResult($params['CASH_BOX_CHECKS']);
									$bCheckUpdated = $applyResult->isSuccess();
								}
							}
							else
							{
								$item->setCollisions(Exchange\EntityCollisionType::PaymentCashBoxCheckNotFound, $entity);
							}
						}
					}
				}
			}
		}

		/** @var OneC\CollisionOrder $collision */
		$collision = $orderImport->getCurrentCollision(EntityType::ORDER);
		$collisionTypes = $collision->getCollision($orderImport);

		if(count($collisionTypes)>0 && $bCheckUpdated)
		{
			return $result;
		}
		else
		{
			$result->addError(new Error('', 'CASH_BOX_CHECK_IGNORE'));
		}

		return $result;
	}
}

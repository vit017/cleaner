<?php
namespace Bitrix\Sale\Archive;

use Bitrix\Main,
	Bitrix\Sale,
	Bitrix\Main\Config\Option,
	Bitrix\Sale\Internals,
	Bitrix\Main\Type,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class manages of orders's archiving, restoring entries from database
 *
 * @package Bitrix\Sale\Archive
 */
class Manager
{
	const SALE_ARCHIVE_VERSION = 1;

	private static $archiveFieldNamesOrder = array(
		"ACCOUNT_NUMBER", "USER_ID", "PRICE", "CURRENCY", "STATUS_ID", "PAYED", "DEDUCTED", "CANCELED",
		"LID", "PERSON_TYPE_ID", "XML_ID", "ID_1C", "DATE_INSERT", "RESPONSIBLE_ID", "COMPANY_ID"
	);

	private static $archiveFieldNamesBasket = array(
		"PRODUCT_ID", "PRODUCT_PRICE_ID", "NAME", "PRICE", "MODULE", "QUANTITY", "WEIGHT", "DATE_INSERT",
		"CURRENCY", "PRODUCT_XML_ID", "MEASURE_NAME", "TYPE", "SET_PARENT_ID", "MEASURE_CODE", "BASKET_DATA"
	);

	/**
	 * Archive orders by filter
	 *
	 * @param array $filter			Filter the selection.
	 * @param int $limit		Limit the selection orders.
	 * @param int $timeExecution		Limits the maximum execution time.
	 *
	 * @return Sale\Result $result
	 *
	 * @throws Main\ArgumentNullException
	 * @throws \Exception
	 */
	public static function archiveOrders($filter = array(), $limit = null, $timeExecution = null)
	{
		$idList = array();
		$orderListArchive = array();
		$result = new Sale\Result();
		$countArchived = null;

		if ((int)$timeExecution)
		{
			@set_time_limit(0);
		}

		$params["filter"] = $filter;
		$params["order"] = array('ID' => "ASC");
		if ((int)$limit)
		{
			$params["limit"] = (int)$limit;
		}
		
		$ordersList = Sale\Order::getList($params);

		while ($order = $ordersList->fetch())
		{
			$orderListArchive[$order['ID']]['ORDER'] = $order;
			$idList[] = $order['ID'];
		}

		if (empty($idList))
		{
			$result->setData(array("count" => null));
			$result->addWarning(new Main\Error(Loc::getMessage("ARCHIVE_ORDER_NOT_FOUND")));
			return $result;
		}

		$idListChunks = array_chunk($idList, 999);

		foreach ($idListChunks as $idOrdersList)
		{
			$sortedOrderData = static::collectOrderData($idOrdersList);

			foreach ($idOrdersList as $id)
			{
				$orderList = array_merge($orderListArchive[$id], $sortedOrderData[$id]);
				$preparedOrderData = array_intersect_key($orderList['ORDER'], array_flip(static::$archiveFieldNamesOrder));
				$preparedOrderData['ORDER_ID'] = $id;
				$preparedOrderData['DATE_ARCHIVED'] = new Type\DateTime();
				$preparedOrderData['VERSION'] = static::SALE_ARCHIVE_VERSION;
				$basketItems = $orderList['BASKET_ITEMS'];
				unset($orderList['BASKET_ITEMS']);
				$preparedOrderData['ORDER_DATA'] = serialize($orderList);
				$additionResult = Internals\OrderArchiveTable::add($preparedOrderData);

				if ($additionResult->isSuccess())
				{
					$archivedOrderId = $additionResult->getId();
					$basketItemIdList = array();
					if (is_array($basketItems))
					{
						foreach ($basketItems as $item)
						{
							$preparedBasketItems = array_intersect_key($item, array_flip(static::$archiveFieldNamesBasket));
							$preparedBasketItems['ARCHIVE_ID'] = $archivedOrderId;
							$preparedBasketItems['BASKET_DATA'] = serialize($item);
							$additionBasketResult = Internals\BasketArchiveTable::add($preparedBasketItems);
							if ($additionBasketResult->isSuccess())
							{
								$basketItemIdList[] = $additionBasketResult->getId();
							}
							else
							{
								$additionResult->addErrors($additionBasketResult->getErrors());
								break;
							}
						}
					}

					if ($additionResult->isSuccess())
					{
						foreach ($orderList['SHIPMENT'] as $shipmentData)
						{
							if ($shipmentData["RESERVED"] == "Y" &&	$shipmentData["DEDUCTED"] == "N")
							{
								$order = Order::load($id);
								if ($shipmentCollection = $order->getShipmentCollection())
								{
									foreach ($shipmentCollection as $shipment)
									{
										$shipment->tryUnreserve();
									}

									$order->save();
								}
								
								break;
							}
						}
						Sale\Order::deleteNoDemand($id);
						$countArchived++;
					}
					else
					{
						Internals\OrderArchiveTable::delete($archivedOrderId);
						foreach ($basketItemIdList as $archivedBasketItemId)
						{
							Internals\BasketArchiveTable::delete($archivedBasketItemId);
						}
					}
				}

				if (!$additionResult->isSuccess())
				{
					$errorMessages = $additionResult->getErrorMessages();
					foreach ($errorMessages as $error)
					{
						$result->addError(new Main\Error(Loc::getMessage("ARCHIVE_ERROR_ORDER_MESSAGE", array("#ID#" => $id)).": ".$error));
					}
				}

				if ((int)$timeExecution && (getmicrotime() - START_EXEC_TIME > $timeExecution))
				{
					$result->setData(array("count" => $countArchived));
					return $result;
				}
			}
		}

		$result->setData(array("count" => $countArchived));

		return $result;
	}

	/**
	 * Archive orders that are selected by module's settings.
	 * 
	 * Used in agents.
	 * 
	 * @param int $limit		Limit the selection orders.
	 * @param int $timeExecution		Limits the maximum execution time.
	 *
	 * @return Sale\Result
	 *
	 * @throws Main\SystemException
	 */
	public static function archiveByOptions($limit = null, $timeExecution = null)
	{
		$filter = Option::get('sale', 'archive_params');

		if (strlen($filter) <= 0)
		{
			throw new Main\SystemException("Settings of order's archiving are null or empty");
		}

		$filter = unserialize($filter);

		if (isset($filter['PERIOD']))
		{
			if ((int)$filter['PERIOD'] > 0)
			{
				$date = new Type\DateTime();
				$latestDate = $date->add('-'.(int)$filter['PERIOD'].' day');
				$filter['<=DATE_INSERT'] = $latestDate;
			}

			unset($filter['PERIOD']);
		}

		return static::archiveOrders($filter, $limit, $timeExecution);
	}

	/**
	 * Used in agents. Manage execution of agent.
	 *
	 * @param int $limit		Limit the selection orders.
	 * @param int $maxTime		Maximum execution time of agent.
	 *
	 * @return string
	 *
	 * @throws Main\ArgumentNullException
	 */
	public static function archiveOnAgent($limit, $maxTime = null)
	{
		$agentId = null;

		$limit = (int)$limit ? (int)$limit : 10;
		$maxTime = (int)$maxTime ? (int)$maxTime : null;

		$agentsList = \CAgent::GetList(array("ID"=>"DESC"), array(
			"MODULE_ID" => "sale",
			"NAME" => "\\Bitrix\\Sale\\Archive\\Manager::archiveOnAgent(%",
		));
		while($agent = $agentsList->Fetch())
		{
			$agentId = $agent["ID"];
		}

		if ($agentId)
		{
			$result = static::archiveByOptions($limit, $maxTime);

			$resultData = $result->getData();
			if ($resultData['count'])
			{
				\CAgent::Update($agentId, array("AGENT_INTERVAL" => 60*5));

			}
			else
			{
				\CAgent::Update($agentId, array("AGENT_INTERVAL" => 24*60*60));
			}
		}
		else
		{
			\CAgent::AddAgent("\\Bitrix\\Sale\\Archive\\Manager::archiveOnAgent(".$limit.",".$maxTime.");", "sale", "N", 24*60*60, "", "Y");
		}

		return "\\Bitrix\\Sale\\Archive\\Manager::archiveOnAgent(".$limit.",".$maxTime.");";
	}


	/**
	 * Select and collect order's data to array
	 *
	 * @param array $idList			Ids list of orders.
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	protected static function collectOrderData($idList = array())
	{
		$sortedOrderReferences = array();

		$sortedOrderProperties = static::collectOrderProperties($idList);
		$sortedPayments = static::collectPayments($idList);
		$sortedShipments = static::collectShipments($idList);
		$sortedBasketItems = static::collectBaskets($idList);
		$couponList = static::collectCoupons($idList);
		$sortedDataDiscount = static::collectDiscountData($idList);
		$sortedDiscountRules = static::collectRules($idList);

		foreach ($idList as $id)
		{
			if (isset($couponList[$id]))
				$discountData['COUPON_LIST'] = $couponList[$id];

			$discountData['ORDER_DATA'] = isset($sortedDataDiscount[$id]) ? $sortedDataDiscount[$id] : array();
			$discountData['RULES_DATA'] = isset($sortedDiscountRules[$id]) ? $sortedDiscountRules[$id] : array();
			$sortedOrderReferences[$id] = array(
				"PROPERTIES" => $sortedOrderProperties[$id] ? $sortedOrderProperties[$id] : array(),
				"PAYMENT" => $sortedPayments[$id] ? $sortedPayments[$id] : array(),
				"SHIPMENT" => $sortedShipments[$id] ? $sortedShipments[$id] : array(),
				"BASKET_ITEMS" => $sortedBasketItems[$id] ? $sortedBasketItems[$id] : array(),
				"DISCOUNT" => $discountData
			);
		}

		return $sortedOrderReferences;
	}

	/**
	 * @param array $filter
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $filter = array())
	{
		return Internals\OrderArchiveTable::getList($filter);
	}

	/**
	 * Get entry of order from archive by entry's id.
	 *
	 * @param int $id
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getById($id)
	{
		return Internals\OrderArchiveTable::getById($id);
	}

	/**
	 * Get entries of basket items from archive.
	 * 
	 * @param array $filter
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getBasketList(array $filter = array())
	{
		return Internals\BasketArchiveTable::getList($filter);
	}

	/**
	 * Get entry of basket item from archive by id.
	 * 
	 * @param int $id
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getBasketItemById($id)
	{
		return Internals\BasketArchiveTable::getById($id);
	}

	/**
	 * Delete archived order with archived basket items.
	 * 
	 * @param int $id
	 *
	 * @return Main\Entity\DeleteResult
	 * @throws \Exception
	 */
	public static function delete($id)
	{
		$basketItems = static::getBasketList(
			array(
				"filter" => array("ARCHIVE_ID" => $id),
				"select" => array("ID")
			)
		);
		while ($item = $basketItems->fetch())
		{
			Internals\BasketArchiveTable::delete($item['ID']);
		}

		return Internals\OrderArchiveTable::delete($id);
	}

	/**
	 * Return Archive\Order object restored from archive
	 *
	 * @param int $id		Entity's id.
	 *
	 * @return Sale\Order
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ArgumentNullException
	 */
	public static function returnArchivedOrder($id)
	{
		$id = (int)$id;
		if ($id <= 0)
			throw new Main\ArgumentNullException("id");

		$archivedOrder = Internals\OrderArchiveTable::getById($id);
		$orderFields = $archivedOrder->fetch();

		if (!$orderFields)
			return null;

		$recoveryName = "\\Bitrix\\Sale\\Archive\\Recovery\\Version" . $orderFields['VERSION'];
		if (class_exists($recoveryName))
		{
			$orderFields['ORDER_DATA'] = unserialize($orderFields['ORDER_DATA']);
			$basketArchivedItems = Internals\BasketArchiveTable::getList(
				array(
					"filter" => array("ARCHIVE_ID" => $orderFields['ID'])
				)
			);

			while ($item = $basketArchivedItems->fetch())
			{
				$item['BASKET_DATA'] = unserialize($item['BASKET_DATA']);
				$orderFields['ORDER_DATA']['BASKET_ITEMS'][$item['BASKET_DATA']['ID']] = $item['BASKET_DATA'];
			}

			/** @var Sale\Archive\Recovery\Base $orderRecovery */
			$orderRecovery = new $recoveryName;
			$order = $orderRecovery->restoreOrder($orderFields['ORDER_DATA']);
			$order->setDateArchived($orderFields['DATE_ARCHIVED']);
			$order->setVersion($orderFields['VERSION']);
		}
		else
		{
			throw new Main\ObjectNotFoundException('Class of restoring archive didn\'t find');
		}

		return $order;
	}

	/**
	 * Collect order properties and sort by orders's ids
	 * 
	 * @param $orderIds
	 *
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	protected static function collectOrderProperties($orderIds)
	{
		$sortedOrderProperties = array();
		$orderProperties = Internals\OrderPropsValueTable::getList(
			array(
				"order" => array("ORDER_ID"),
				"filter" => array("=ORDER_ID" => $orderIds)
			)
		);

		while ($property = $orderProperties->fetch())
		{
			$sortedOrderProperties[$property['ORDER_ID']][$property['ID']] = $property;
		}
		return $sortedOrderProperties;
	}

	/**
	 * Collect payments and sort by orders's ids
	 * 
	 * @param $orderIds
	 *
	 * @return array
	 */
	protected static function collectPayments($orderIds)
	{
		$sortedPayments = array();
		$payments = Sale\Payment::getList(
			array(
				"order" => array("ORDER_ID"),
				"filter" => array("=ORDER_ID" => $orderIds)
			)
		);

		while ($payment = $payments->fetch())
		{
			$sortedPayments[$payment['ORDER_ID']][$payment['ID']] = $payment;
		}

		return $sortedPayments;
	}

	/**
	 * Collect shipments with shipment items and sort by orders's ids
	 * 
	 * @param $orderIds
	 *
	 * @return mixed
	 */
	protected static function collectShipments($orderIds)
	{
		$shipmentItemsList = array();
		$sortedShipments = array();

		$shipments = Sale\Shipment::getList(
			array(
				"order" => array("ORDER_ID"),
				"filter" => array("=ORDER_ID" => $orderIds, "SYSTEM" => 'N')
			)
		);

		while ($shipment = $shipments->fetch())
		{
			$shipmentItemsList[$shipment['ID']] = $shipment;
		}

		if (!empty($shipmentItemsList))
		{
			$shipmentsItems = Sale\ShipmentItem::getList(
				array(
					"order" => array("ORDER_DELIVERY_ID"),
					"filter" => array("ORDER_DELIVERY_ID" => array_keys($shipmentItemsList))
				)
			);

			while ($shipmentsItem = $shipmentsItems->fetch())
			{
				$shipmentItemsList[$shipmentsItem['ORDER_DELIVERY_ID']]["SHIPMENT_ITEM"][] = $shipmentsItem;
			}
		}

		foreach ($shipmentItemsList as $item)
		{
			$sortedShipments[$item['ORDER_ID']][$item['ID']] = $item;
		}

		return $sortedShipments;
	}

	/**
	 * Collect basket items with barcodes and sort by orders's ids
	 * 
	 * @param $orderIds
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	protected static function collectBaskets($orderIds)
	{
		$sortedBasketItems = array();
		$basketItemsList = array();

		$basketItems = Sale\Basket::getList(
			array(
				"order" => array("ORDER_ID"),
				"filter" => array("=ORDER_ID" => $orderIds)
			)
		);

		while ($element = $basketItems->fetch())
		{
			$basketItemsList[$element['ID']] = $element;
		}

		if (!empty($basketItemsList))
		{
			$basketProperties = Internals\BasketPropertyTable::getList(
				array(
					"filter" => array("BASKET_ID" => array_keys($basketItemsList))
				)
			);

			while ($property = $basketProperties->fetch())
			{
				$basketItemsList[$property["BASKET_ID"]]['PROPERTY_ITEMS'][] = $property;
			}

			$basketProperties = Sale\ShipmentItemStore::getList(
				array(
					"filter" => array("=BASKET_ID" => array_keys($basketItemsList))
				)
			);

			while ($property = $basketProperties->fetch())
			{
				$basketItemsList[$property["BASKET_ID"]]['SHIPMENT_BARCODE_ITEMS'][$property['ORDER_DELIVERY_BASKET_ID']] = $property;
			}
		}

		foreach ($basketItemsList as $basketItem)
		{
			$sortedBasketItems[$basketItem['ORDER_ID']][$basketItem['ID']] = $basketItem;
		}

		return $sortedBasketItems;
	}

	/**
	 * Collect coupons and sort by orders's ids
	 * 
	 * @param $orderIds
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	protected static function collectCoupons($orderIds)
	{
		$couponList = array();

		$couponsIterator = Internals\OrderCouponsTable::getList(array(
			'select' => array(
				'*',
				'MODULE_ID' => 'ORDER_DISCOUNT.MODULE_ID',
				'DISCOUNT_ID' => 'ORDER_DISCOUNT.DISCOUNT_ID',
				'DISCOUNT_NAME' => 'ORDER_DISCOUNT.NAME',
				'DISCOUNT_DESCR' => 'ORDER_DISCOUNT.ACTIONS_DESCR',
			),
			'filter' => array('=ORDER_ID' => $orderIds),
			'order' => array('ID' => 'ASC')
		));

		while ($coupon = $couponsIterator->fetch())
		{
			foreach ($coupon['DISCOUNT_DESCR'] as $discountDescriptionArray)
			{
				foreach ($discountDescriptionArray as $descriptionList)
				{
					$coupon['DISCOUNT_SIZE'] = Sale\OrderDiscountManager::formatDescription($descriptionList);
				}
			}

			$couponList[$coupon['ORDER_ID']][$coupon['COUPON']] = $coupon;
		}

		return $couponList;
	}

	/**
	 * Collect discount data and sort by orders's ids
	 * 
	 * @param $orderIds
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	protected static function collectDiscountData($orderIds)
	{
		$sortedDataDiscount = array();

		$dataIterator = Internals\OrderDiscountDataTable::getList(
			array(
				'select' => array('*'),
				'filter' => array('=ORDER_ID' => $orderIds)
			)
		);

		while ($dataDiscount = $dataIterator->fetch())
		{
			$sortedDataDiscount[$dataDiscount['ORDER_ID']][$dataDiscount['ID']] = $dataDiscount;
		}

		return $sortedDataDiscount;
	}

	/**
	 * Collect discount data and sort by orders's ids
	 * 
	 * @param $orderIds
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	protected static function collectRules($orderIds)
	{
		$sortedRules = array();
		$discountList = array();
		$rulesList = array();
		
		$ruleIterator = Internals\OrderRulesTable::getList(array(
			'select' => array('*', 'RULE_DESCR' => 'DESCR.DESCR', 'RULE_DESCR_ID' => 'DESCR.ID'),
			'filter' => array('=ORDER_ID' => $orderIds),
			'order' => array('ID' => 'ASC')
		));

		while ($rule = $ruleIterator->fetch())
		{
			$discountList[] = $rule['ORDER_DISCOUNT_ID'];
			$rulesList[$rule['ID']] = $rule;
		}

		if (!empty($discountList))
		{
			$discountIterator = Internals\OrderDiscountTable::getList(array(
				'filter' => array('@ID' => $discountList),
			));

			while ($discount = $discountIterator->fetch())
			{
				$discountList[$discount['ID']] = $discount;
			}
		}

		foreach ($rulesList as $id => $rule)
		{
			$rule["DISCOUNT_DATA"] = $discountList[$rule['ORDER_DISCOUNT_ID']] ? $discountList[$rule['ORDER_DISCOUNT_ID']] : array();
			$sortedRules[$rule['ORDER_ID']][$id] = $rule;
		}

		return $sortedRules;
	}
}

<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Error;
use Bitrix\Sale\Cashbox\Internals\CashboxCheckTable;
use Bitrix\Sale\Cashbox\Internals\CashboxTable;
use Bitrix\Sale\EntityMarker;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Sale;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Result;

Loc::loadLanguageFile(__FILE__);

final class CheckManager
{
	const EVENT_ON_GET_CUSTOM_CHECK = 'OnGetCustomCheckList';
	const EVENT_ON_CHECK_PRINT_SEND = 'OnPrintableCheckSend';
	const MIN_TIME_FOR_SWITCH_CASHBOX = 240;

	/** This is time re-sending a check print in minutes */
	const CHECK_RESENDING_TIME = 1;
	const CHECK_LIMIT_RECORDS = 5;

	/**
	 * @param CollectableEntity[] $entities
	 * @param $type
	 * @return Result
	 */
	public static function addByType(array $entities, $type)
	{
		$result = new Result();

		if ($type === '')
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_EMPTY_CHECK_TYPE')));
			return $result;
		}

		$check = static::createByType($type);
		if ($check === null)
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK')));
			return $result;
		}

		$cashboxList = array();
		foreach ($entities as $entity)
		{
			$items = Manager::getListWithRestrictions($entity);
			if (!$cashboxList)
				$cashboxList = $items;
			else
				$cashboxList = array_intersect($items, $cashboxList);
		}

		$entity = reset($entities);
		$order = static::getOrder($entity);

		if (!$cashboxList)
		{
			$dbRes = CashboxTable::getList(array('filter' => array('ACTIVE' => 'Y')));
			if ($dbRes->fetch())
				$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_NOT_FOUND')));

			return $result;
		}

		$check->setEntities($entities);
		$check->setAvailableCashbox($cashboxList);

		$saveResult = $check->save();

		if ($saveResult->isSuccess())
		{
			$checkId = $saveResult->getId();
			$order->addPrintedCheck($check);

			foreach ($cashboxList as $item)
			{
				$cashbox = Cashbox::create($item);
				if ($cashbox instanceof IPrintImmediately)
				{
					$printResult = $cashbox->printImmediately($check);
					if ($printResult->isSuccess())
					{
						$data = $printResult->getData();
						CashboxCheckTable::update(
							$checkId,
							array(
								'STATUS' => 'P',
								'DATE_PRINT_START' => new Type\DateTime(),
								'CASHBOX_ID' => $item['ID'],
								'EXTERNAL_UUID' => $data['UUID']
							)
						);

						return $result;
					}
					else
					{
						$result->addErrors($printResult->getErrors());
					}
				}
			}

			global $CACHE_MANAGER;
			foreach ($cashboxList as $cashbox)
			{
				$CACHE_MANAGER->Read(CACHED_b_sale_order, 'sale_checks_'.$cashbox['ID']);
				$CACHE_MANAGER->SetImmediate('sale_checks_'.$cashbox['ID'], true);
			}
		}
		else
		{
			$result->addErrors($saveResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param $checkId
	 * @param array $data
	 * @return Result
	 */
	public static function savePrintResult($checkId, array $data)
	{
		$result = new Result();

		if ($checkId <= 0)
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_ID')));
			return $result;
		}

		$order = null;
		$payment = null;

		$dbRes = CashboxCheckTable::getList(array('select' => array('*'), 'filter' => array('ID' => $checkId)));
		$check = $dbRes->fetch();
		if (!$check)
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_NOT_FOUND', array('#CHECK_ID#' => $checkId))));
			return $result;
		}

		if ($check['STATUS'] === 'Y')
			return $result;

		if ($check['ORDER_ID'] > 0)
		{
			$order = Sale\Order::load($check['ORDER_ID']);
			if ($order === null)
			{
				$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_ORDER_LOAD')));
				return $result;
			}

			$paymentCollection = $order->getPaymentCollection();
			if ($check['PAYMENT_ID'] > 0)
			{
				$payment = $paymentCollection->getItemById($check['PAYMENT_ID']);
				if ($payment === null)
				{
					$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_PAYMENT_LOAD')));
					return $result;
				}
			}
		}

		if (isset($data['ERROR']))
		{
			$errorMessage = Loc::getMessage('SALE_CASHBOX_ERROR_CHECK_PRINT', array('#CHECK_ID#' => $checkId));
			if ($data['ERROR']['MESSAGE'])
				$errorMessage .= ': '.$data['ERROR']['MESSAGE'];

			if ($data['ERROR']['TYPE'] === Errors\Warning::TYPE)
			{
				if ($check['CNT_FAIL_PRINT'] >= 3)
				{
					$data['ERROR']['TYPE'] = Errors\Error::TYPE;
				}
				else
				{
					CashboxCheckTable::update($checkId, array('CNT_FAIL_PRINT' => $check['CNT_FAIL_PRINT'] + 1));
					$result->addError(new Errors\Warning($errorMessage));
					return $result;
				}
			}

			if ($data['ERROR']['TYPE'] === Errors\Error::TYPE)
			{
				$updatedFields = array('STATUS' => 'E', 'DATE_PRINT_END' => new Main\Type\DateTime());
				if ((int)$check['CNT_FAIL_PRINT'] === 0)
					$updatedFields['CNT_FAIL_PRINT'] = 1;

				CashboxCheckTable::update($checkId, $updatedFields);

				if ($order !== null && $payment !== null)
				{
					$r = new Result();
					$errorCode = isset($data['ERROR']['CODE']) ? $data['ERROR']['CODE'] : 0;
					$r->addWarning(new Main\Error($errorMessage, $errorCode));
					EntityMarker::addMarker($order, $payment, $r);

					$payment->setField('MARKED', 'Y');
					$order->save();
				}

				$error = new Errors\Error($errorMessage);
			}
			else
			{
				$error = new Errors\Warning($errorMessage);
			}

			Manager::writeToLog($check['CASHBOX_ID'], $error);
			$result->addError($error);
		}
		else
		{
			$updateResult = CashboxCheckTable::update(
				$checkId,
				array(
					'STATUS' => 'Y',
					'LINK_PARAMS' => $data['LINK_PARAMS'],
					'DATE_PRINT_END' => new Main\Type\DateTime()
				)
			);

			if ($updateResult->isSuccess())
			{
				if ($payment !== null)
				{
					$isSend = false;
					$event = new Main\Event('sale', static::EVENT_ON_CHECK_PRINT_SEND, array('PAYMENT' => $payment, 'CHECK' => $check));
					$event->send();

					$eventResults = $event->getResults();
					/** @var Main\EventResult $eventResult */
					foreach($eventResults as $eventResult)
					{
						if($eventResult->getType() == Main\EventResult::SUCCESS)
							$isSend = true;
					}

					if (!$isSend)
						Sale\Notify::callNotify($payment, Sale\EventActions::EVENT_ON_CHECK_PRINT);
				}
			}
			else
			{
				$result->addErrors($updateResult->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param CollectableEntity[] $entities
	 * @return Result
	 */
	public static function addChecks(array $entities)
	{
		$result = new Result();

		$map = static::collateDocuments($entities);
		foreach ($map as $check)
		{
			$addResult = static::addByType($check["ENTITIES"], $check["TYPE"]);
			if (!$addResult->isSuccess())
				$result->addErrors($addResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param Sale\Internals\CollectableEntity $entity
	 * @throws Main\ArgumentTypeException
	 * @return Sale\Order
	 */
	public static function getOrder($entity)
	{
		$order = null;

		if ($entity instanceof Sale\Payment)
		{
			/** @var Sale\PaymentCollection $col */
			$col = $entity->getCollection();
			$order = $col->getOrder();
		}
		elseif ($entity instanceof Sale\Shipment)
		{
			/** @var Sale\ShipmentCollection $col */
			$col = $entity->getCollection();
			$order = $col->getOrder();
		}
		else
		{
			throw new Main\ArgumentTypeException("entities");
		}

		return $order;
	}

	/**
	 * @return array
	 */
	private static function getBuildInCheckList()
	{
		return array(
			'\Bitrix\Sale\Cashbox\SellCheck',
			'\Bitrix\Sale\Cashbox\SellReturnCashCheck',
			'\Bitrix\Sale\Cashbox\SellReturnCheck',
//			'\Bitrix\Sale\Cashbox\PrePaymentReturnCheck',
//			'\Bitrix\Sale\Cashbox\PrePaymentCheck',
		);
	}

	/**
	 * @return array
	 */
	private static function getUserCheckList()
	{
		$checkList = array();

		$event = new Main\Event('sale', static::EVENT_ON_GET_CUSTOM_CHECK);
		$event->send();
		$resultList = $event->getResults();

		if (is_array($resultList) && !empty($resultList))
		{
			foreach ($resultList as $eventResult)
			{
				/** @var  Main\EventResult $eventResult */
				if ($eventResult->getType() === Main\EventResult::SUCCESS)
				{
					$params = $eventResult->getParameters();
					if (!empty($params) && is_array($params))
						$checkList = array_merge($checkList, $params);
				}
			}
		}

		return $checkList;
	}

	/**
	 * @return void
	 */
	public static function init()
	{
		static $isInit = false;

		if ($isInit === false)
		{
			$handlers = static::getUserCheckList();
			Main\Loader::registerAutoLoadClasses(null, $handlers);
			$isInit = true;
		}
	}

	/**
	 * @return array
	 */
	public static function getCheckList()
	{
		static $checkList = array();
		if (empty($checkList))
			$checkList = array_merge(static::getBuildInCheckList(), array_keys(static::getUserCheckList()));

		return $checkList;
	}

	/**
	 * @return array
	 */
	public static function getCheckTypeMap()
	{
		static::init();

		$result = array();
		$checkMap = static::getCheckList();

		/** @var Check $className */
		foreach ($checkMap as $className)
		{
			if (class_exists($className))
				$result[$className::getType()] = $className;
		}

		return $result;
	}

	/**
	 * @param string $type
	 * @return null|Check
	 */
	public static function createByType($type)
	{
		static::init();

		$typeMap = static::getCheckTypeMap();
		$handler = $typeMap[$type];

		return Check::create($handler);
	}

	/**
	 * @param array $entities
	 * @return Entity[]
	 * @throws Main\NotSupportedException
	 */
	public static function collateDocuments(array $entities)
	{
		$map = array();

		$event = new Main\Event('sale', 'OnCheckCollateDocuments', array(
			'ENTITIES' => $entities
		));
		$event->send();
		$eventResults = $event->getResults();
		if ($eventResults != null)
		{
			foreach ($eventResults as $eventResult)
			{
				if ($eventResult->getType() === Main\EventResult::SUCCESS)
				{
					$d = $eventResult->getParameters();
					if (!is_array($d))
						throw new Main\NotSupportedException("OnCheckCollateDocuments event result");

					$map = array_merge($map, $d);
				}
			}

			if (count($map) > 0)
				return $map;
		}

		$existingChecks = null;
		$order = null;
		foreach ($entities as $entity)
		{
			// load existing checks
			if ($existingChecks === null)
			{
				$existingChecks = array();
				$order = static::getOrder($entity);
				$db = CashboxCheckTable::getList(
					array(
						"filter" => array("ORDER_ID" => $order->getId()),
						"select" => array("ID", "PAYMENT_ID", "SHIPMENT_ID", "TYPE", "STATUS")
					)
				);
				while ($ar = $db->fetch())
				{
					if (intval($ar["PAYMENT_ID"]) > 0)
						$existingChecks["P"][ $ar["PAYMENT_ID"] ][] = $ar;
					if (intval($ar["SHIPMENT_ID"]) > 0)
						$existingChecks["S"][ $ar["SHIPMENT_ID"] ][] = $ar;
				}
			}

			// analysing
			// we should allow users to implement their own algorithms
			if (count($existingChecks) <= 0)
			{
				if ($entity instanceof Sale\Payment)
				{
					/** @var Sale\PaySystem\Service $ps */
					$ps = $entity->getPaySystem();
					if ($entity->isPaid()
						&& ($ps->getField("CAN_PRINT_CHECK") == "Y")
						&& ($entity->getSum() == $order->getPrice()))
					{
						$checkEntities[] = $entity;

						$shipmentCollection = $order->getShipmentCollection();
						/** @var Sale\Shipment $shipment */
						foreach ($shipmentCollection as $shipment)
						{
							if (!$shipment->isSystem())
								$checkEntities[] = $shipment;
						}

						$map[] = array("TYPE" => "sell", "ENTITIES" => $checkEntities);
					}
				}
			}
		}

		return $map;
	}

	/**
	 * @param array $cashboxIds
	 * @param array $orderIds
	 * @return array
	 */
	public static function getPrintableChecks(array $cashboxIds, array $orderIds = array())
	{
		$result = array();

		$filter = array(
			'LINK_PARAMS' => '',
			'CHECK2CASHBOX.CASHBOX_ID' => $cashboxIds,
			array(
				'LOGIC' => 'OR',
				array(
					'=STATUS' => 'N',
					'DATE_PRINT_START' => ''
				),
				array(
					'=STATUS' => 'P',
					'<MAX_DT_REPEAT_CHECK' => new Type\DateTime()
				)
			)
		);
		if ($orderIds)
			$filter['ORDER_ID'] = $orderIds;

		$limit = count($cashboxIds)*static::CHECK_LIMIT_RECORDS;
		$dbRes = CashboxCheckTable::getList(
			array(
				'select' => array('*', 'AVAILABLE_CASHBOX_ID' => 'CHECK2CASHBOX.CASHBOX_ID'),
				'filter' => $filter,
				'limit' => $limit,
				'runtime' => array(
					new Main\Entity\ExpressionField(
						'MAX_DT_REPEAT_CHECK',
						'DATE_ADD(DATE_PRINT_START, INTERVAL '.static::CHECK_RESENDING_TIME.' MINUTE)',
						null,
						array(
							'data_type' => 'datetime'
						)
					)
				)
			)
		);

		if ($data = $dbRes->fetch())
		{
			$con = Main\Application::getConnection();
			$dbLocRes = $con->query("SELECT GET_LOCK('get_check_list', 0) as L");
			$locResult = $dbLocRes->fetch();
			if ($locResult["L"] == "0")
				return $result;

			$i = 0;
			do
			{
				if (!isset($result[$data['ID']]))
				{
					$i++;
					if ($i > static::CHECK_LIMIT_RECORDS)
						break;

					$result[$data['ID']] = $data;
					$result[$data['ID']]['CASHBOX_LIST'] = array();
				}

				$result[$data['ID']]['CASHBOX_LIST'][] = $data['AVAILABLE_CASHBOX_ID'];
			}
			while ($data = $dbRes->fetch());

			foreach ($result as $checkId => $item)
			{
				if ($item['STATUS'] === 'P')
				{
					$now = new Type\DateTime();
					$nowTs = $now->getTimestamp();

					/** @var Type\DateTime $dateStartPrint */
					$dateStartPrint = $item['DATE_PRINT_START'];
					$dateStartPrintTs = $dateStartPrint->getTimestamp();

					if ($nowTs - $dateStartPrintTs > static::MIN_TIME_FOR_SWITCH_CASHBOX)
					{
						$availableCashboxIds = array_diff($cashboxIds, array($item['CASHBOX_ID']));
						if ($availableCashboxIds)
						{
							$result[$checkId]['CASHBOX_ID'] = Manager::chooseCashbox($availableCashboxIds);
							CashboxCheckTable::update($checkId, array('CASHBOX_ID' => $result[$checkId]['CASHBOX_ID']));
						}
					}
					else
					{
						if ($item['CASHBOX_ID'] > 0 && !in_array($item['CASHBOX_ID'], $cashboxIds))
							unset($result[$checkId]);
					}

					continue;
				}

				$result[$checkId]['CASHBOX_ID'] = Manager::chooseCashbox($item['CASHBOX_LIST']);
				CashboxCheckTable::update($checkId, array('STATUS' => 'P', 'DATE_PRINT_START' => new Type\DateTime(), 'CASHBOX_ID' => $result[$checkId]['CASHBOX_ID']));
			}

			$con->query("SELECT RELEASE_LOCK('get_check_list')");
		}

		return $result;
	}

	/**
	 * @param array $settings
	 * @return Check|null
	 */
	public static function create(array $settings)
	{
		$check = CheckManager::createByType($settings['TYPE']);
		if ($check)
			$check->init($settings);

		return $check;
	}

	/**
	 * @param Sale\Internals\CollectableEntity $entity
	 * @return array
	 */
	public static function getCheckInfo(Sale\Internals\CollectableEntity $entity)
	{
		$filter = array();
		if ($entity instanceof Sale\Payment)
			$filter['PAYMENT_ID'] = $entity->getId();

		return static::collectInfo($filter);
	}

	/**
	 * @param Sale\Internals\CollectableEntity $entity
	 * @return array
	 */
	public static function getLastPrintableCheckInfo(Sale\Internals\CollectableEntity $entity)
	{
		if (!($entity instanceof Sale\Payment))
			return array();

		$dbRes = CashboxCheckTable::getList(
			array(
				'select' => array('*'),
				'filter' => array('PAYMENT_ID' => $entity->getId(), 'STATUS' => 'Y'),
				'order' => array('DATE_PRINT_END' => 'DESC'),
				'limit' => 1
			)
		);

		if ($data = $dbRes->fetch())
		{
			$data['LINK'] = '';
			if (!empty($data['LINK_PARAMS']))
			{
				$cashbox = Manager::getObjectById($data['CASHBOX_ID']);
				if ($cashbox)
					$data['LINK'] = $cashbox->getCheckLink($data['LINK_PARAMS']);
			}

			return $data;
		}

		return array();
	}

	/**
	 * @param array $filter
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	public static function collectInfo(array $filter = array())
	{
		$result = array();
		
		$typeMap = CheckManager::getCheckTypeMap();

		$dbRes = CashboxCheckTable::getList(
			array(
				'select' => array('*'),
				'filter' => $filter
			)
		);

		while ($data = $dbRes->fetch())
		{
			$data['LINK'] = '';
			if (!empty($data['LINK_PARAMS']))
			{
				$cashbox = Manager::getObjectById($data['CASHBOX_ID']);
				if ($cashbox)
					$data['LINK'] = $cashbox->getCheckLink($data['LINK_PARAMS']);
			}

			/** @var Check $type */
			$type = $typeMap[$data['TYPE']];
			if (class_exists($type))
				$data['TYPE_NAME'] = $type::getName();

			$result[$data['ID']] = $data;
		}

		return $result;
	}

	/**
	 * @param $uuid
	 * @return array|false
	 */
	public static function getCheckInfoByExternalUuid($uuid)
	{
		$dbRes = CashboxCheckTable::getList(array('filter' => array('EXTERNAL_UUID' => $uuid)));
		return $dbRes->fetch();
	}
}
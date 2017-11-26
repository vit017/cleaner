<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Sale\Cashbox\Internals\CashboxCheckTable;
use Bitrix\Sale\Cashbox\Internals\Check2CashboxTable;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentCollection;

/**
 * Class Check
 * @package Bitrix\Sale\Cashbox
 */
abstract class Check
{
	const PARAM_FISCAL_DOC_NUMBER = 'fiscal_doc_number';
	const PARAM_FISCAL_DOC_ATTR = 'fiscal_doc_attribute';
	const PARAM_FISCAL_RECEIPT_NUMBER = 'fiscal_receipt_number';
	const PARAM_FN_NUMBER = 'fn_number';
	const PARAM_SHIFT_NUMBER = 'shift_number';
	const PARAM_REG_NUMBER_KKT = 'reg_number_kkt';
	const PARAM_DOC_TIME = 'doc_time';
	const PARAM_DOC_SUM = 'doc_sum';
	const PARAM_CALCULATION_ATTR = 'calculation_attribute';

	/** @var array $fields */
	private $fields = array();

	/** @var array $cashboxList */
	private $cashboxList = array();

	/** @var CollectableEntity[] $entities */
	private $entities = array();

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getType()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getName()
	{
		throw new Main\NotImplementedException();
	}
	
	/**
	 * @param string $handler
	 * @return null|Check
	 */
	public static function create($handler)
	{
		if (class_exists($handler))
			return new $handler();

		return null;
	}

	/**
	 * Check constructor.
	 */
	private function __construct() {}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function getField($name)
	{
		return $this->fields[$name];
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function setField($name, $value)
	{
		$this->fields[$name] = $value;
	}

	/**
	 * @param array $cashboxList
	 */
	public function setAvailableCashbox(array $cashboxList)
	{
		$this->cashboxList = $cashboxList;
	}

	/**
	 * @param CollectableEntity[] $entities
	 * @throws Main\ArgumentTypeException
	 */
	public function setEntities(array $entities)
	{
		$this->entities = $entities;

		$orderId = null;

		foreach ($this->entities as $entity)
		{
			if ($entity instanceof Payment)
			{
				$this->fields['PAYMENT_ID'] = $entity->getId();
				$this->fields['SUM'] = $entity->getSum();
				$this->fields['CURRENCY'] = $entity->getField('CURRENCY');

				/** @var PaymentCollection $col */
				$col = $entity->getCollection();
				$colOrderId = $col->getOrder()->getId();

				if ($orderId === null)
					$orderId = $colOrderId;
				elseif ($orderId != $colOrderId)
					throw new Main\ArgumentTypeException('entities');
			}
			elseif ($entity instanceof Shipment)
			{
				$this->fields['SHIPMENT_ID'] = $entity->getId();

				/** @var ShipmentCollection $col */
				$col = $entity->getCollection();
				$colOrderId = $col->getOrder()->getId();

				if ($orderId === null)
					$orderId = $colOrderId;
				elseif ($orderId != $colOrderId)
					throw new Main\ArgumentTypeException('entities');
			}
			else
			{
				throw new Main\ArgumentTypeException('entities');
			}
		}

		$this->fields['ORDER_ID'] = $orderId;
	}

	/**
	 * @return CollectableEntity[]
	 */
	public function getEntities()
	{
		if ($this->entities)
			return $this->entities;

		if ($this->fields['PAYMENT_ID'] > 0)
		{
			if ($this->fields['ORDER_ID'] > 0)
			{
				$orderId = $this->fields['ORDER_ID'];
			}
			else
			{
				$dbRes = Payment::getList(array('filter' => array('ID' => $this->fields['PAYMENT_ID'])));
				$data = $dbRes->fetch();
				$orderId = $data['ORDER_ID'];
			}

			if ($orderId > 0)
			{
				$order = Order::load($orderId);
				if ($order)
				{
					$paymentCollection = $order->getPaymentCollection();
					if ($paymentCollection)
					{
						$payment = $paymentCollection->getItemById($this->fields['PAYMENT_ID']);
						if ($payment)
							$this->entities[] = $payment;
					}

					if ($this->fields['SHIPMENT_ID'] > 0)
					{
						$shipmentCollection = $order->getShipmentCollection();
						if ($shipmentCollection)
						{
							$shipment = $shipmentCollection->getItemById($this->fields['SHIPMENT_ID']);
							if ($shipment)
								$this->entities[] = $shipment;
						}
					}
				}
			}
		}

		return $this->entities;
	}

	/**
	 * @return Main\Entity\AddResult|Main\Entity\UpdateResult
	 */
	public function save()
	{
		if ((int)$this->fields['ID'] > 0)
			return CashboxCheckTable::update($this->fields['ID'], $this->fields);

		$this->fields['TYPE'] = static::getType();
		$this->fields['DATE_CREATE'] = new Main\Type\DateTime();

		$result = CashboxCheckTable::add($this->fields);
		$checkId = $result->getId();
		$this->fields['ID'] = $checkId;
		foreach ($this->cashboxList as $cashbox)
			Check2CashboxTable::add(array('CHECK_ID' => $checkId, 'CASHBOX_ID' => $cashbox['ID']));

		return $result;
	}

	/**
	 * @param $cashboxId
	 */
	public function linkCashbox($cashboxId)
	{
		$this->fields['CASHBOX_ID'] = $cashboxId;
	}

	/**
	 * @param $settings
	 */
	public function init($settings)
	{
		$this->fields = $settings;
	}

	/**
	 * @return array
	 */
	abstract public function getDataForCheck();
}
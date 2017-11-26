<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\ShipmentCollection;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class PrepaymentCheck
 * @package Bitrix\Sale\Cashbox
 */

class PrepaymentCheck extends Check
{
	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getType()
	{
		return 'prepayment';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_PREPAYMENT_NAME');
	}
	
	/**
	 * @return array
	 */
	public function getDataForCheck()
	{
		$result = array(
			'type' => static::getType(),
			'number_kkm' => $this->getField('NUMBER_KKM'),
			'unique_id' => $this->getField('ID'),
			'items' => array()
		);

		$order = null;
		$payment = null;
		$shipment = null;

		$entities = $this->getEntities();

		if ($entities)
		{
			foreach ($entities as $entity)
			{
				if ($order === null)
				{
					/** @var PaymentCollection|ShipmentCollection $collection */
					$collection = $entity->getCollection();
					if ($collection)
						$order = $collection->getOrder();
				}

				if ($entity instanceof Payment)
				{
					$payment = $entity;
					$paySystem = PaySystem\Manager::getById($entity->getPaymentSystemId());

					$result['payments'][] = array(
						'is_cash' => $paySystem['IS_CASH'],
						'sum' => $entity->getSum()
					);
				}
			}

			if ($shipment === null)
			{
				$result['items'][] = array(
					'name' => Main\Localization\Loc::getMessage('SALE_CASHBOX_PREPAYMENT_NO_ITEM_NAME', array('#ORDER_ID#' => $payment->getField('ACCOUNT_NUMBER'))),
					'price' => $payment->getSum(),
					'quantity' => 1,
					'VAT' => 0
				);
			}

			if ($order !== null)
			{
				$properties = $order->getPropertyCollection();
				$email = $properties->getUserEmail();
				$result['client_email'] = $email->getValue();
				$result['date_create'] = new Main\Type\DateTime();
			}
		}

		return $result;
	}

}
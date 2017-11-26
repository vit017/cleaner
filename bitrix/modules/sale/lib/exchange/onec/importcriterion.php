<?php
namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Exchange;

class ImportCriterionBase
    implements Exchange\ICriterion
{
    protected $entity = null;

    /**
     * @return null|Sale\Internals\CollectableEntity $entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

	/**
	 * @param $entity
	 */
	public function setEntity($entity = null)
	{
		$this->entity = $entity;
	}

    /**
     * @param $entityTypeId
     * @param $entity
     * @return static
     * @throws Main\ArgumentException
     * @throws Main\NotImplementedException
     */
    public static function getCurrent($entityTypeId, $entity)
    {
        if(!Exchange\EntityType::IsDefined($entityTypeId))
        {
            throw new Main\ArgumentOutOfRangeException('Is not defined', Exchange\EntityType::FIRST, Exchange\EntityType::LAST);
        }

        /** @var ImportCriterionBase $criterion */
        $criterion =  new static();
        $criterion->setEntity($entity);

        return $criterion;
    }

	/**
	 * @param array $fields
	 * @return bool
	 */
	public function equals(array $fields)
	{
		return true;
	}
}

class ImportCriterionOneCCml2 extends ImportCriterionBase
{
	/**
	 * @param array $fields
	 * @return bool
	 */
	public function equals(array $fields)
	{
		/** @var Sale\Internals\CollectableEntity $entity */
		$entity = $this->getEntity();
		if(empty($entity))
		{
			return true;
		}

		if(($entity->getField('VERSION_1C') != $fields['VERSION_1C']) ||
			(strlen($entity->getField('VERSION_1C'))<=0 || strlen($fields['VERSION_1C'])<=0)
		)
		{
			return true;
		}

		return false;
	}
}

class CriterionOrder extends ImportCriterionOneCCml2
    implements Exchange\ICriterionOrder
{
    /**
     * @param Sale\BasketItem $basketItem
     * @param array $fields
     * @return bool
     */
    public function equalsBasketItemTax(Sale\BasketItem $basketItem, array $fields)
    {
        if($fields['TAX']['VAT_RATE'] != $basketItem->getVatRate() &&
            ($fields['TAX']['VAT_INCLUDED']<>'Y' && $fields['PRICE']<>$basketItem->getPrice())
        )
        {
            return true;
        }

        return false;
    }

    /**
     * @param Sale\BasketItem $basketItem
     * @param array $fields
     * @return bool
     */
    public function equalsBasketItem(Sale\BasketItem $basketItem, array $fields)
    {
        if($fields['QUANTITY'] != $basketItem->getQuantity() ||
            $fields['PRICE'] != $basketItem->getPrice() ||
            $fields['VAT_RATE'] != $basketItem->getVatRate() ||
            $fields['DISCOUNT_PRICE'] != $basketItem->getDiscountPrice())
        {
            return true;
        }

        return false;
    }

    /**
     * @param Sale\BasketItem $basketItem
     * @param array $fields
     * @return bool
     */
    public function equalsBasketItemDiscount(Sale\BasketItem $basketItem, array $fields)
    {
        if($fields['DISCOUNT']['PRICE'] != $basketItem->getDiscountPrice() && intval($fields['DISCOUNT']['PRICE'])>0)
        {
            return true;
        }

        return false;
    }

	/**
	 * @param Sale\Order|null $entity
	 */
	public function setEntity($entity = null)
	{
		if(!empty($entity) && !($entity instanceof Sale\Order))
			throw new Main\ArgumentException("Entity must be instanceof Order");

		parent::setEntity($entity);
	}
}

class CriterionShipment extends ImportCriterionOneCCml2
    implements Exchange\ICriterionShipment
{
    /**
     * @param array $fields
     * @param bool|true $withoutSystem
     * @return bool
     */
    public function equalsForList(array $fields, $withoutSystem = true)
    {
        /** @var Sale\Shipment $entity */
        $entity = $this->getEntity();

        if(!$entity->isShipped() && ($withoutSystem || !$entity->isSystem()))
        {
            if($this->equals($fields))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Sale\Shipment $entity
     */
	public function setEntity($entity = null)
	{
		if(!empty($entity) && !($entity instanceof Sale\Shipment))
			throw new Main\ArgumentException("Entity must be instanceof Shipment");

		parent::setEntity($entity);
	}
}

class CriterionPayment extends ImportCriterionOneCCml2
{
    /**
     * @param Sale\Payment|null $entity
     */
	public function setEntity($entity = null)
	{
		if(!empty($entity) && !($entity instanceof Sale\Payment))
			throw new Main\ArgumentException("Entity must be instanceof Payment");

		parent::setEntity($entity);
	}
}

/**
 * Class CriterionProfile
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class CriterionProfile extends ImportCriterionOneCCml2
{
    /**
     * @param array $fields
     * @return bool
     */
    public function equals(array $fields)
    {
        $entity = $this->getEntity();
        if(empty($entity))
        {
            return true;
        }

        if(($entity->getField('USER_PROFILE_VERSION') != $fields['VERSION_1C']) ||
            (strlen($entity->getField('USER_PROFILE_VERSION'))<=0 || strlen($fields['VERSION_1C'])<=0)
        )
        {
            return true;
        }

        return false;
    }

    /**
     * @param Exchange\ProfileImport|null $entity
     */
	public function setEntity($entity = null)
	{
		if(!empty($entity) && !($entity instanceof Exchange\ProfileImport))
			throw new Main\ArgumentException("Entity must be instanceof ProfileImport");

		parent::setEntity($entity);
	}
}
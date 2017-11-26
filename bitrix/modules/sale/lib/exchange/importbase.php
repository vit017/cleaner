<?php
namespace Bitrix\Sale\Exchange;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Exchange;

abstract class ImportBase
{
    const ITEM_ITEM = 'ITEM';
    const ITEM_SERVICE = 'SERVICE';

	protected $collisionErrors = false;
	protected $collisionWarnings = false;

    /** @var Sale\Internals\Fields */
    protected $fields;
    /** @var ISettings */
    protected $settings = null;
    /** @var Exchange\ICriterion */
    protected $loadCriterion = null;

    /** @var ICollision  */
    protected $loadCollision = null;

    /**
     * @return int
     */
    abstract public function getOwnerTypeId();

    /**
     * Adds row to entity table
     * @param array $params
     * @return Sale\Result
     */
    abstract public function add(array $params);

    /**
     * Updates row in entity table
     * @param array $params
     * @return Sale\Result
     */
    abstract public function update(array $params);

    /**
     * Deletes row in entity table by primary key
     * @param array|null $params
     * @return Sale\Result
     */
    abstract public function delete(array $params = null);

    /**
     * @param array $fields
     * @return Sale\Result
     */
    abstract protected function checkFields(array $fields);

    /**
     * @param array $fields
	 * @return Sale\Result
     */
    abstract public function load(array $fields);

    /**
     * @param array $params
     * @return Sale\Result
     */
    public function import(array $params)
    {
        $result = new Sale\Result();
        if($this->getId()>0)
        {
            $result = $this->update($params);
        }
        elseif($this->isImportable())
        {
            $result = $this->add($params);
        }

        return $result;
    }

    /**
     * @return int|null
     */
    abstract public function getId();

    /**
     * @return bool
     */
    abstract public function isImportable();

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
     * @return array
     */
    public function getFieldValues()
    {
        return $this->fields->getValues();
    }

    /**
     * @param array $fields
     */
    abstract public function refreshData(array $fields);

    /**
     * @param ISettings $settings
     */
    public function loadSettings(ISettings $settings)
    {
        $this->settings = $settings;
    }

	/**
	 * @param ICriterion $criterion
	 */
	public function loadCriterion(ICriterion $criterion)
	{
		$this->loadCriterion = $criterion;
	}

    /**
     * @return ICriterion
     * @internal param $typeId
     * @internal param $entity
     * @internal
     */
    public function getLoadedCriterion()
    {
        return $this->loadCriterion;
    }

    /**
     * @param $entity
     * @return ICriterion
     */
    public function getCurrentCriterion($entity)
    {
        /** @var ICriterion $criterion */
        $criterions = $this->getLoadedCriterion();
        return $criterions::getCurrent($this->getOwnerTypeId(), $entity);
    }

	/**
	 * @param ICollision $collision
	 */
	public function loadCollision(ICollision $collision)
	{
		$this->loadCollision = $collision;
	}

    /**
     * @internal
     * @return ICollision
     * @throws Main\ArgumentOutOfRangeException
     */
    public function getLoadedCollision()
    {
        return $this->loadCollision;
    }

    /**
     * @return ISettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param $typeId
     * @return ICollision
     */
    public function getCurrentCollision($typeId)
    {
        /** @var Exchange\OneC\ImportCollision $collision */
        $collision = $this->getLoadedCollision();
        return $collision::getCurrent($typeId);
    }

	/**
	 * @return bool
	 */
	public function hasCollisionErrors()
	{
		return $this->collisionErrors;
	}

	/**
	 * @return bool
	 */
	public function hasCollisionWarnings()
	{
		return $this->collisionWarnings;
	}
}